<?php
namespace App\Tests\Service;

use App\Entity\Rencontre;
use App\Entity\Pari;
use App\Entity\Equipe;
use App\Service\GestionPari;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Test unitaire de la classe GestionPari
 */
class GestionPariTest extends KernelTestCase
{

    public function testRencontreAVenir(): void
    {
        self::bootKernel();
        $pari = new Pari();
        $pari->setMise(100);
        $pari->setEquipe($this->buildEquipeA());
        $rencontre = $this->buildGainEquipeA();
        $rencontre->setStatut(Rencontre::STATUT_A_VENIR);
        $pari->setRencontre($rencontre);
        $container = static::getContainer();
        $gestionPari = $container->get(GestionPari::class);
        $gain = $gestionPari->calculerGain($pari);
        $this->assertEquals(0, $gain);
    }

    public function testRencontreEnCours(): void
    {
        self::bootKernel();
        $pari = new Pari();
        $pari->setMise(100);
        $pari->setEquipe($this->buildEquipeA());
        $rencontre = $this->buildGainEquipeA();
        $rencontre->setStatut(Rencontre::STATUT_EN_COURS);
        $pari->setRencontre($rencontre);
        $container = static::getContainer();
        $gestionPari = $container->get(GestionPari::class);
        $gain = $gestionPari->calculerGain($pari);
        $this->assertEquals(0, $gain);
    }

    public function testGainEquipeA(): void
    {
        self::bootKernel();
        $pari = new Pari();
        $pari->setMise(100);
        $pari->setEquipe($this->buildEquipeA());
        $rencontre = $this->buildGainEquipeA();
        $pari->setRencontre($rencontre);
        $container = static::getContainer();
        $gestionPari = $container->get(GestionPari::class);
        $gain = $gestionPari->calculerGain($pari);
        $this->assertEquals(200, $gain);
    }

    public function testPertePari(): void
    {
        self::bootKernel();
        $pari = new Pari();
        $pari->setMise(100);
        $pari->setEquipe($this->buildEquipeB());
        $rencontre = $this->buildGainEquipeA();
        $pari->setRencontre($rencontre);
        $container = static::getContainer();
        $gestionPari = $container->get(GestionPari::class);
        $gain = $gestionPari->calculerGain($pari);
        $this->assertEquals(-100, $gain);
    }

    public function testGainEquipeB(): void
    {
        self::bootKernel();
        $pari = new Pari();
        $pari->setMise(100);
        $pari->setEquipe($this->buildEquipeB());
        $rencontre = $this->buildGainEquipeA();
        $pari->setRencontre($rencontre);
        $rencontre->setScoreEquipeB(70);
        $container = static::getContainer();
        $gestionPari = $container->get(GestionPari::class);
        $gain = $gestionPari->calculerGain($pari);
        $this->assertEquals(350, $gain);
    }

    private function buildGainEquipeA(): Rencontre {
        $rencontre = new Rencontre();
        $maintenant = new \DateTime();
        $rencontre->setHeureDebut($maintenant);
        $maintenant->add(new \DateInterval('PT1H'));
        $rencontre->setHeureFin($maintenant);
        $rencontre->setStatut(Rencontre::STATUT_TERMINE);
        $rencontre->setEquipeA($this->buildEquipeA());
        $rencontre->setEquipeB($this->buildEquipeB());
        $rencontre->setCoteEquipeA(2.0);
        $rencontre->setCoteEquipeB(3.5);
        $rencontre->setScoreEquipeA(55);
        $rencontre->setScoreEquipeB(20);
        return $rencontre;
    }

    private function buildEquipeA(): Equipe {
        $equipeA = new Equipe();
        $equipeA->setNom("Kansas City Chiefs");
        $equipeA->setPays("USA");
        return $equipeA;
    }

    private function buildEquipeB(): Equipe {
        $equipeB = new Equipe();
        $equipeB->setNom("Los Angeles Rams");
        $equipeB->setPays("USA");
        return $equipeB;
    }
}
