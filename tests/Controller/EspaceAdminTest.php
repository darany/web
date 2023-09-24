<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EspaceAdminTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testAccessIllegal(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        //Si l'admin n'est pas connecté, il ne doit pas voir le lien vers son esapce
        $crawler = $this->client->request('GET', '/');
        $this->assertSelectorNotExists('a[id=cmdAdmin]');
        $testUser = $userRepository->findOneByEmail('user0@example.org');
        $this->client->loginUser($testUser);
        //Si un simple utilisateur est connecté, il ne doit pas voir le lien vers l'easpace Admin
        $crawler = $this->client->request('GET', '/');
        $this->assertSelectorNotExists('a[id=cmdAdmin]');
        //Accès à la page d'amin doit planter
        $crawler = $this->client->request('GET', '/admin');
        $this->assertResponseStatusCodeSame(403);   // 403 = Forbidden
    }

    public function testAccessNormal(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.org');
        $this->client->loginUser($testUser);
        $crawler = $this->client->request('GET', '/admin');
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorExists('a[id=cmdGestionJoueurs]');
        $this->assertSelectorExists('a[id=cmdGestionEquipes]');
        $this->assertSelectorExists('a[id=cmdGestionMatchs]');
    }

}
