<?php

namespace App\Tests;

use App\Repository\UserRepository;
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

}
