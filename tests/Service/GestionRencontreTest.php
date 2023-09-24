<?php
namespace App\Tests\Service;

use App\Repository\UserRepository;
use App\Repository\RencontreRepository;
use App\Entity\Rencontre;
use App\Entity\Pari;
use App\Entity\Equipe;
use App\Service\GestionRencontre;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManager;

/**
 * Test fonctionnel de la classe GestionRencontre
 */
class GestionRencontreTest extends KernelTestCase
{
    private EntityManager $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testCloturer(): void
    {
        $rencontre = new Rencontre();
        $maintenant = new \DateTime();
        $rencontre->setHeureDebut($maintenant);
        $maintenant->add(new \DateInterval('PT1H'));
        $rencontre->setHeureFin($maintenant);
        $rencontre->setStatut(Rencontre::STATUT_A_VENIR);
        $equipeA = new Equipe();
        $equipeA->setNom("Unit Test Team A");
        $equipeA->setPays("USA");
        $rencontre->setEquipeA($equipeA);
        $equipeB = new Equipe();
        $equipeB->setNom("Unit Test Team B");
        $equipeB->setPays("USA");
        $rencontre->setEquipeB($equipeB);
        $rencontre->setCoteEquipeA(2.0);
        $rencontre->setCoteEquipeB(3.5);
        $rencontre->setScoreEquipeA(55);
        $rencontre->setScoreEquipeB(20);
        $rencontre->setScoreEquipeB(70);
        $this->entityManager->persist($rencontre);

        $userRepository = static::getContainer()->get(UserRepository::class);
        $user1 = $userRepository->findOneByEmail('user11@example.org');
        $user2 = $userRepository->findOneByEmail('user12@example.org');

        $pari1 = new Pari();
        $pari1->setDate($maintenant);
        $pari1->setUser($user1);
        $pari1->setMise(100);
        $pari1->setEquipe($equipeB);
        $pari1->setRencontre($rencontre);
        $this->entityManager->persist($pari1);

        $pari2 = new Pari();
        $pari2->setDate($maintenant);
        $pari2->setUser($user2);
        $pari2->setMise(200);
        $pari2->setEquipe($equipeA);
        $pari2->setRencontre($rencontre);
        $this->entityManager->persist($pari2);
        
        $rencontre->addPari($pari1);
        $rencontre->addPari($pari2);
        $this->entityManager->persist($rencontre);
        $this->entityManager->flush();
        
        // Test de la clôture de la rencontre proprement dit (statut et heure de fin)
        $container = static::getContainer();
        // Bien prendre ce qui sort de la BDD
        $rencontreRepository = static::getContainer()->get(RencontreRepository::class);
        $rencontrePersisted = $rencontreRepository->findOneBy(['id' => $rencontre->getId()]);
        $GestionRencontre = $container->get(GestionRencontre::class);
        // Faire le calcul et examiner la copie retournée par le service
        $rencontreAltered = $GestionRencontre->cloturer($rencontrePersisted);
        $this->assertEquals(Rencontre::STATUT_TERMINE, $rencontreAltered->getStatut());
        $this->assertEqualsWithDelta($maintenant->getTimestamp(), $rencontre->getHeureFin()->getTimestamp(), 3690);

        // Test du calcul des gains, même principe on récupère les paris de la BDD
        $pari1Alt = $this->entityManager->getRepository(Pari::class)->findOneBy(['id' => $pari1->getId()]);
        $pari2Alt = $this->entityManager->getRepository(Pari::class)->findOneBy(['id' => $pari2->getId()]);
        $this->assertEquals(350, $pari1Alt->getGain());
        $this->assertEquals(-200, $pari2Alt->getGain());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}
