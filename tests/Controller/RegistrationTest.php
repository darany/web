<?php

namespace App\Tests;

use Faker\Factory;
use Faker\Generator;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationTest extends WebTestCase
{
    private KernelBrowser $client;
    private Generator $faker;

    protected function setUp(): void
    {
        $this->faker = Factory::create();   // Outil de génération de données aléatoires
        $this->client = static::createClient();   // Créer un client pour faire des requêtes HTTP
    }

    public function testRegistration(): void
    {

        $crawler = $this->client->request('GET', '/register');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleContains("Créer son compte");

        $buttonCrawlerNode = $crawler->selectButton("S'enregistrer");
        $form = $buttonCrawlerNode->form();
        $form['registration_form[nom]'] = 'balet';
        $form['registration_form[prenom]'] = 'benjamin';
        $form['registration_form[email]'] = $this->faker->email();
        $form['registration_form[plainPassword]'] = '1Mot2P@ssCompliqué!!';
        $this->client->submit($form);
        $this->assertResponseRedirects('/login');
    }
}
