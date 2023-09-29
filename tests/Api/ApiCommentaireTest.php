<?php
namespace App\Tests;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Repository\UserRepository;
use App\Repository\RencontreRepository;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

/**
 * Test fonctionnel des endpoints de l'API Pari
 */
class ApiCommentaireTest extends ApiTestCase
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

    public function testPostCommentaire(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('commentateur@example.org');
        $token = $this->createAuthenticatedToken($user);
        $rencontreRepository = static::getContainer()->get(RencontreRepository::class);
        $rencontres = $rencontreRepository->findAll();
        $this->client->request('POST', '/api/commentaires', [
            'auth_bearer' => $token,
            'json' => [
                'rencontreId' => $rencontres[0]->getId(),
                'texte' => 'Test commentaire',
                'scoreEquipeA' => 10,
                'scoreEquipeB' => 23,
            ],
        ]);
        $this->assertResponseStatusCodeSame(201);   // 201 = Created
        $content = $this->client->getResponse()->getContent();
        $data = json_decode($content, true);
        $this->assertArrayHasKey('@id', $data);
        $this->assertEquals($rencontres[0]->getId(), $data['rencontreId']);
    }


    public function testPostXSS(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('commentateur@example.org');
        $token = $this->createAuthenticatedToken($user);
        $rencontreRepository = static::getContainer()->get(RencontreRepository::class);
        $rencontres = $rencontreRepository->findAll();
        $this->client->request('POST', '/api/commentaires', [
            'auth_bearer' => $token,
            'json' => [
                'rencontreId' => $rencontres[0]->getId(),
                'texte' => "<script>alert('e');</script>"
            ],
        ]);
        $this->assertResponseStatusCodeSame(201);   // 201 = Created
        $content = $this->client->getResponse()->getContent();
        $data = json_decode($content, true);
        $this->assertEquals("&lt;script&gt;alert(&#039;e&#039;);&lt;/script&gt;", $data['texte']);
    }

    public function testPostQueLeScore(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('commentateur@example.org');
        $token = $this->createAuthenticatedToken($user);
        $rencontreRepository = static::getContainer()->get(RencontreRepository::class);
        $rencontres = $rencontreRepository->findAll();
        $this->client->request('POST', '/api/commentaires', [
            'auth_bearer' => $token,
            'json' => [
                'rencontreId' => $rencontres[0]->getId(),
                'scoreEquipeA' => 101
            ],
        ]);
        $this->assertResponseStatusCodeSame(201);   // 201 = Created
        $content = $this->client->getResponse()->getContent();
        $data = json_decode($content, true);
        $this->assertEquals("Mise à jour du score", $data['texte']);
        $this->assertEquals(101, $data['scoreEquipeA']);
    }

    public function testPostNotAllowed(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('user14@example.org');
        $token = $this->createAuthenticatedToken($user);
        $rencontreRepository = static::getContainer()->get(RencontreRepository::class);
        $rencontres = $rencontreRepository->findAll();
        $this->client->request('POST', '/api/commentaires', [
            'auth_bearer' => $token,
            'json' => [
                'rencontreId' => $rencontres[0]->getId(),
                'texte' => 'Test commentaire',
            ],
        ]);
        $this->assertResponseStatusCodeSame(403);   // 403 = Forbidden
    }

    public function testPostRencontreNotFound(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('commentateur@example.org');
        $token = $this->createAuthenticatedToken($user);
        $this->client->request('POST', '/api/commentaires', [
            'auth_bearer' => $token,
            'json' => [
                'rencontreId' => 99999,
                'texte' => 'Test commentaire',
            ],
        ]);
        $this->assertResponseStatusCodeSame(404);   // 404 = Not Found
    } 

    public function testGetCommentaires(): void
    {
        $rencontreRepository = static::getContainer()->get(RencontreRepository::class);
        $rencontres = $rencontreRepository->toutesLesRencontresTermineesOuEnCours();
        $this->client->request('GET', '/api/rencontres/' . $rencontres[0]->getId() . '/commentaires');
        $this->assertResponseStatusCodeSame(200);
        $content = $this->client->getResponse()->getContent();
        $data = json_decode($content, true);
        $this->assertGreaterThan(1, $data['hydra:totalItems']);
    }

    public function testCommentaireNotFound(): void
    {
        $this->client->request('GET', '/api/rencontres/99999/commentaires');
        $this->assertResponseStatusCodeSame(404);
    }
}
