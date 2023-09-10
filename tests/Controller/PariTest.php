<?php

namespace App\Tests;

use App\Entity\Rencontre;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use App\Repository\UserRepository;
use App\Repository\RencontreRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PariTest extends WebTestCase
{

    private function buildRencontre(int $statut): Rencontre
    {
        //Prendre une rencontre en fonction du statut
        $rencontreRepository = static::getContainer()->get(RencontreRepository::class);
        $rencontres = $rencontreRepository->toutesLesRencontres();
        foreach($rencontres as $rencontre) {
            if ($rencontre->getStatut() == $statut) {
                return $rencontre;
            }
        }
    }

    private function buildConnectedClient(string $email): KernelBrowser
    {
        //Connexion
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail($email);
        $client->loginUser($testUser);
        return $client;
    }

    public function testPariCreateUpdate(): void
    {
        $client = $this->buildConnectedClient('user0@example.org');

        $rencontre = $this->buildRencontre(Rencontre::STATUT_A_VENIR);
        $crawler = $client->request('GET', '/pari/rencontre/' . $rencontre->getId());
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleContains("Prise de pari");

        $mode = 'actualisation';
        $buttonCrawlerNode = $crawler->selectButton('actualisation');
        if (is_null($buttonCrawlerNode->getNode(0))) {
            $mode = 'validation';
            $buttonCrawlerNode = $crawler->selectButton('validation');
        }
        $form = $buttonCrawlerNode->form();
        $form['pari[mise]'] = '13';
        $form['pari[equipe]'] = $rencontre->getEquipeB()->getId();
        $client->submit($form);
        $this->assertResponseRedirects('/pari/rencontre/' . $rencontre->getId());

        $crawler = $client->request('GET', '/pari/rencontre/' . $rencontre->getId());
        $mise = $crawler->filter('input[name="pari[mise]"]')->extract(array('value'))[0];
        $this->assertEquals('13', $mise);
        $equipe = $crawler->filter('option[selected]')->extract(array('value'))[0];
        $this->assertEquals($rencontre->getEquipeB()->getId(), $equipe);
    }

    public function testPariInvalidRencontre(): void
    {
        $client = $this->buildConnectedClient('user0@example.org');

        $rencontre = $this->buildRencontre(Rencontre::STATUT_EN_COURS);
        $crawler = $client->request('GET', '/pari/rencontre/' . $rencontre->getId());
        $this->assertResponseStatusCodeSame(404);
    }

    public function testPariMiseNegative(): void
    {
        $client = $this->buildConnectedClient('user0@example.org');

        $rencontre = $this->buildRencontre(Rencontre::STATUT_A_VENIR);
        $crawler = $client->request('GET', '/pari/rencontre/' . $rencontre->getId());
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleContains("Prise de pari");

        $mode = 'actualisation';
        $buttonCrawlerNode = $crawler->selectButton('actualisation');
        if (is_null($buttonCrawlerNode->getNode(0))) {
            $mode = 'validation';
            $buttonCrawlerNode = $crawler->selectButton('validation');
        }
        $form = $buttonCrawlerNode->form();
        $form['pari[mise]'] = '-10';
        $form['pari[equipe]'] = $rencontre->getEquipeB()->getId();
        $client->submit($form);
        $this->assertResponseStatusCodeSame(404);
    }

    public function testPariSuppression(): void
    {
        $client = $this->buildConnectedClient('user0@example.org');

        $rencontre = $this->buildRencontre(Rencontre::STATUT_A_VENIR);
        $crawler = $client->request('GET', '/pari/rencontre/' . $rencontre->getId());
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleContains("Prise de pari");

        $mode = 'actualisation';
        $buttonCrawlerNode = $crawler->selectButton('actualisation');
        if (is_null($buttonCrawlerNode->getNode(0))) {
            $mode = 'validation';
            $buttonCrawlerNode = $crawler->selectButton('validation');
        }
        $form = $buttonCrawlerNode->form();
        $form['pari[mise]'] = '13';
        $form['pari[equipe]'] = $rencontre->getEquipeB()->getId();
        $client->submit($form);
        $this->assertResponseRedirects('/pari/rencontre/' . $rencontre->getId());

        //Mise à 0 ==> Suppression du pari
        $buttonCrawlerNode = $crawler->selectButton('actualisation');
        $form = $buttonCrawlerNode->form();
        $form['pari[mise]'] = '0';
        $form['pari[equipe]'] = $rencontre->getEquipeB()->getId();
        $client->submit($form);
        $this->assertResponseRedirects('/pari/rencontre/' . $rencontre->getId());

        $crawler = $client->request('GET', '/pari/rencontre/' . $rencontre->getId());
        $mise = $crawler->filter('input[name="pari[mise]"]')->extract(array('value'))[0];
        $this->assertEquals('0', $mise);
        $equipe = $crawler->filter('option[selected]')->extract(array('value'))[0];
        $this->assertEquals($rencontre->getEquipeA()->getId(), $equipe);
        $this->assertSelectorExists('input[value=validation]');
    }
}
