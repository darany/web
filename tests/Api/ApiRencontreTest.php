<?php
namespace App\Tests;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Repository\UserRepository;
use App\Repository\RencontreRepository;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

/**
 * Test fonctionnel des endpoints de l'API Rencontre
 */
class ApiRencontre extends ApiTestCase
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

    public function testGetRencontres(): void
    {
        $this->client->request('GET', '/api/rencontres');
        $this->assertResponseStatusCodeSame(200);
        $content = $this->client->getResponse()->getContent();
        $data = json_decode($content, true);
        $this->assertGreaterThan(1, $data['hydra:totalItems']);
    }

    public function testGetRencontresPublicAccess(): void
    {
        $rencontreRepository = static::getContainer()->get(RencontreRepository::class);
        $rencontres = $rencontreRepository->findAll();
        $this->client->request('GET', '/api/rencontres/' . $rencontres[0]->getId());
        $this->assertResponseStatusCodeSame(200);
        $content = $this->client->getResponse()->getContent();
        $data = json_decode($content, true);
        $this->assertArrayHasKey('@id', $data);
        $this->assertArrayNotHasKey('totalDesMises', $data);
        $this->assertArrayNotHasKey('nombreDeParisSurEquipeA', $data);
        $this->assertArrayNotHasKey('nombreDeParisSurEquipeB', $data);
        $this->assertEquals($rencontres[0]->getMeteo(), $data['meteo']);
        $this->assertEquals($rencontres[0]->getScoreEquipeA(), $data['scoreEquipeA']);
        $this->assertEquals($rencontres[0]->getScoreEquipeB(), $data['scoreEquipeB']);
        $this->assertEquals($rencontres[0]->getEquipeA()->getNom(), $data['equipeA']);
        $this->assertEquals($rencontres[0]->getEquipeB()->getNom(), $data['equipeB']);
        $this->assertEquals($rencontres[0]->getCoteEquipeA(), $data['coteEquipeA']);
        $this->assertEquals($rencontres[0]->getCoteEquipeB(), $data['coteEquipeB']);
    }

    public function testGetRencontresRestrictedAccess(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('commentateur@example.org');
        $token = $this->createAuthenticatedToken($user);
        $rencontreRepository = static::getContainer()->get(RencontreRepository::class);
        $rencontres = $rencontreRepository->findAll();
        $this->client->request('GET', '/api/rencontres/' . $rencontres[0]->getId(), [
                'auth_bearer' => $token,
        ]);
        $this->assertResponseStatusCodeSame(200);
        $content = $this->client->getResponse()->getContent();
        $data = json_decode($content, true);
        $this->assertArrayHasKey('totalDesMises', $data);
        $this->assertArrayHasKey('nombreDeParisSurEquipeA', $data);
        $this->assertArrayHasKey('nombreDeParisSurEquipeB', $data);
    }
}
