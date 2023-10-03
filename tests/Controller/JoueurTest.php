<?php

namespace App\Tests;

use App\Repository\UserRepository;
use App\Entity\Joueur;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class JoueurTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testAccessIllegal(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user0@example.org');
        $this->client->loginUser($testUser);
        $crawler = $this->client->request('GET', '/admin/joueurs');
        $this->assertResponseStatusCodeSame(403);   // 403 = Forbidden
    }

    public function testAccessNormal(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.org');
        $this->client->loginUser($testUser);
        $crawler = $this->client->request('GET', '/admin/joueurs');
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorExists('h2[id=titleJoueurs]');
        $this->assertSelectorNotExists('p[id=msgNoPlayerFound]');
    }

    public function testCreateNewPlayer(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.org');
        $this->client->loginUser($testUser);
        $crawler = $this->client->request('GET', '/admin/joueurs/create');
        $buttonCrawlerNode = $crawler->selectButton("Enregistrer");
        $form = $buttonCrawlerNode->form();
        $newNom = $this->generateRandomString();
        $form['joueur[nom]'] = $newNom;
        $form['joueur[prenom]'] = 'Bobby';
        $form['joueur[numero]'] = 96;
        $this->client->submit($form);
        $crawler = $this->client->request('GET', '/admin/joueurs');
        $cells = $crawler->filter('td:contains("' . $newNom . '")');
        $this->assertCount(1, $cells);
    }

    public function testEditPlayer(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.org');
        $this->client->loginUser($testUser);
        //Créer l'équipe en BDD
        $joueur = new Joueur();
        $joueur->setNom($this->generateRandomString());
        $joueur->setPrenom('Bobby');
        $joueur->setNumero(96);
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($joueur);
        $entityManager->flush();

        //Récupération de l'id de l'équipe créée et édition
        $crawler = $this->client->request('GET', '/admin/joueurs/' . $joueur->getId());
        $buttonCrawlerNode = $crawler->selectButton("Enregistrer");
        $form = $buttonCrawlerNode->form();
        $form['joueur[nom]'] = 'Nimporte';
        $newPrenom = $this->generateRandomString();
        $form['joueur[prenom]'] = $newPrenom;
        $this->client->submit($form);
        $crawler = $this->client->request('GET', '/admin/joueurs');
        $cells = $crawler->filter('td:contains("' . $newPrenom . '")');
        $this->assertCount(1, $cells);
    }

    public function testDeletePlayer(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.org');
        $this->client->loginUser($testUser);
        //Créer l'équipe en BDD
        $joueur = new Joueur();
        $newNom = $this->generateRandomString();
        $joueur->setNom($newNom);
        $joueur->setPrenom('Bobby');
        $joueur->setNumero(96);
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($joueur);
        $entityManager->flush();

        //Récupération de l'id de l'équipe créée et suppression
        $crawler = $this->client->request('GET', '/admin/joueurs/' . $joueur->getId() . '/delete');
        $crawler = $this->client->request('GET', '/admin/joueurs');
        $cells = $crawler->filter('td:contains("' . $newNom . '")');
        $this->assertCount(0, $cells);
    }

    /**
     * Genérer une chaîne de caractères aléatoire
     * Cette fonction n'est pas sûre d'un point de vue sécurité, mais elle est suffisante pour nos tests
     *
     * @param integer $length
     * @return void
     */
    private function generateRandomString($length = 10): string {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }
}
