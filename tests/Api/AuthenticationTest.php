<?php
namespace App\Tests;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use App\Repository\UserRepository;

/**
 * Test fonctionnel de l'authentification JWT
 */
class AuthenticationTest extends ApiTestCase
{
    public function testLogin(): void
    {
        $client = self::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user0@example.org');

        // retrieve a token
        $response = $client->request('POST', '/auth', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => 'user0@example.org',
                'password' => 'user0@example.org',
            ],
        ]);
        $json = $response->toArray();
        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('token', $json);

        // TODO: test not authorized
        // $client->request('GET', '/greetings');
        // $this->assertResponseStatusCodeSame(401);
        // // test authorized
        // $client->request('GET', '/greetings', ['auth_bearer' => $json['token']]);
        // $this->assertResponseIsSuccessful();
    }
}
