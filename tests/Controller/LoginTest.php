<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginTest extends WebTestCase
{
    public function testLoginLogout(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user0@example.org');

        //Etat initial : déconnecté
        //tester sur une page qui affiche un menu, même en étant déconnecté
        $crawler = $client->request('GET', 'rencontres');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('p[id=cmdLogout]');

        //Se connecter
        $client->loginUser($testUser);
        $crawler = $client->request('GET', 'rencontres');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('p[id=cmdLogin]');
    }
}
