<?php

namespace App\Tests;

use App\Repository\UserRepository;
use App\Entity\Equipe;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EquipeTest extends WebTestCase
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
        $crawler = $this->client->request('GET', '/admin/equipes');
        $this->assertResponseStatusCodeSame(403);   // 403 = Forbidden
    }

    public function testAccessNormal(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.org');
        $this->client->loginUser($testUser);
        $crawler = $this->client->request('GET', '/admin/equipes');
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorExists('h2[id=titleEquipes]');
        $this->assertSelectorNotExists('p[id=msgNoPTeamsFound]');
    }

    public function testCreateNewTeam(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.org');
        $this->client->loginUser($testUser);
        $crawler = $this->client->request('GET', '/admin/equipes/create');
        $buttonCrawlerNode = $crawler->selectButton("Enregistrer");
        $form = $buttonCrawlerNode->form();
        $newNom = $this->generateRandomString();
        $form['equipe[nom]'] = $newNom;
        $form['equipe[pays]'] = 'Barbie Land';
        $this->client->submit($form);
        $crawler = $this->client->request('GET', '/admin/equipes');
        $cells = $crawler->filter('td:contains("' . $newNom . '")');
        $this->assertCount(1, $cells);
    }

    public function testEditTeam(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.org');
        $this->client->loginUser($testUser);
        //Créer l'équipe en BDD
        $equipe = new Equipe();
        $equipe->setNom($this->generateRandomString());
        $equipe->setPays('Tales Land');
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($equipe);
        $entityManager->flush();

        //Récupération de l'id de l'équipe créée et édition
        $crawler = $this->client->request('GET', '/admin/equipes/' . $equipe->getId());
        $buttonCrawlerNode = $crawler->selectButton("Enregistrer");
        $form = $buttonCrawlerNode->form();
        $form['equipe[nom]'] = 'Peu importe';
        $newPays = $this->generateRandomString();
        $form['equipe[pays]'] = $newPays;
        $this->client->submit($form);
        $crawler = $this->client->request('GET', '/admin/equipes');
        $cells = $crawler->filter('td:contains("' . $newPays . '")');
        $this->assertCount(1, $cells);
    }

    public function testDeleteTeam(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.org');
        $this->client->loginUser($testUser);
        //Créer l'équipe en BDD
        $equipe = new Equipe();
        $newNom = $this->generateRandomString();
        $equipe->setNom($newNom);
        $equipe->setPays('No man\'s Land');
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($equipe);
        $entityManager->flush();

        //Récupération de l'id de l'équipe créée et suppression
        $crawler = $this->client->request('GET', '/admin/equipes/' . $equipe->getId() . '/delete');
        $crawler = $this->client->request('GET', '/admin/equipes');
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
