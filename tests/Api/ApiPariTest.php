<?php
namespace App\Tests;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Repository\UserRepository;
use App\Repository\PariRepository;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

/**
 * Test fonctionnel des endpoints de l'API Pari
 */
class ApiPariTest extends ApiTestCase
{
    private Client $client;
    private JWTTokenManagerInterface $jwtManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        // Récupérer le service JWT manager depuis le container de test
        $this->jwtManager = static::getContainer()->get(JWTTokenManagerInterface::class);
    }

    private function createAuthenticatedToken($user): string
    {
        return $this->jwtManager->create($user);
    }

    public function testGetValidPari(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user0@example.org');
        $pariRepository = static::getContainer()->get(PariRepository::class);
        $token = $this->createAuthenticatedToken($testUser);

        $paris = $pariRepository->findParisTerminesByUserId($testUser->getId());
        $this->client->request('GET', '/api/rencontres/' . $paris[0]->getRencontre()->getId() . '/pari', [
                'auth_bearer' => $token
        ]);
        $this->assertResponseStatusCodeSame(200);
        $content = $this->client->getResponse()->getContent();
        $data = json_decode($content, true);
        $this->assertEquals($paris[0]->getId(), $data['id']);
        $this->assertEquals($paris[0]->getMise(), $data['mise']);
        $this->assertEquals($paris[0]->getGain(), $data['gain']);
        $this->assertEquals($paris[0]->getEquipe()->getNom(), $data['nomEquipe']);
    }

    public function testGetPariNotOwnedByUser(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user0@example.org');
        $pariRepository = static::getContainer()->get(PariRepository::class);
        $invalidUser = $userRepository->findOneByEmail('commentateur@example.org');
        $token = $this->createAuthenticatedToken($invalidUser);

        $paris = $pariRepository->findParisTerminesByUserId($testUser->getId());
        $this->client->request('GET', '/api/rencontres/' . $paris[0]->getRencontre()->getId() . '/pari', [
                'auth_bearer' => $token,
        ]);
        $this->assertResponseStatusCodeSame(404);
    }

    public function testNotLogged(): void
    {
        $this->client->request('GET', '/api/rencontres/99999/pari');
        $this->assertResponseStatusCodeSame(401);
    }
}
