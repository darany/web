<?php
namespace App\Tests\Service;

use App\Entity\Rencontre;
use App\Entity\Pari;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Test unitaire de l'entité Rencontre
 */
class RencontreTest extends KernelTestCase
{

    public function testRencontreEnCours(): void
    {
        self::bootKernel();
        $rencontre = new Rencontre();
        $rencontre->setStatut(Rencontre::STATUT_EN_COURS);
        $rencontre->setScoreEquipeA(75);
        $rencontre->setScoreEquipeB(33);
        $scores = $rencontre->getDisplayableScores();
        $this->assertEquals("75 - 33", $scores);
    }

    public function testRencontreAVenir(): void
    {
        self::bootKernel();
        $rencontre = new Rencontre();
        $rencontre->setStatut(Rencontre::STATUT_A_VENIR);
        $rencontre->setScoreEquipeA(1);
        $rencontre->setScoreEquipeB(2);
        $scores = $rencontre->getDisplayableScores();
        $this->assertEquals('—', $scores);
    }

    public function testRencontreTerminee(): void {
        self::bootKernel();
        $rencontre = new Rencontre();
        $rencontre->setStatut(Rencontre::STATUT_TERMINE);
        $rencontre->setScoreEquipeA(55);
        $rencontre->setScoreEquipeB(20);
        $scores = $rencontre->getDisplayableScores();
        $this->assertEquals("55 - 20", $scores);
    }

    public function testRencontreIsTerminee(): void {
        self::bootKernel();
        $rencontre = new Rencontre();
        $rencontre->setStatut(Rencontre::STATUT_TERMINE);
        $this->assertEquals(true, $rencontre->isTerminee());
        $rencontre->setStatut(Rencontre::STATUT_A_VENIR);
        $this->assertEquals(false, $rencontre->isTerminee());
        $rencontre->setStatut(Rencontre::STATUT_EN_COURS);
        $this->assertEquals(false, $rencontre->isTerminee());
    }

    public function testRencontreIsAvenir(): void {
        self::bootKernel();
        $rencontre = new Rencontre();
        $rencontre->setStatut(Rencontre::STATUT_TERMINE);
        $this->assertEquals(false, $rencontre->isAvenir());
        $rencontre->setStatut(Rencontre::STATUT_A_VENIR);
        $this->assertEquals(true, $rencontre->isAvenir());
        $rencontre->setStatut(Rencontre::STATUT_EN_COURS);
        $this->assertEquals(false, $rencontre->isAvenir());
    }

    public function testTotalDesMises(): void {
        self::bootKernel();
        $rencontre = new Rencontre();
        $paris = new Pari();
        $paris->setMise(10);
        $rencontre->addPari($paris);
        $paris = new Pari();
        $paris->setMise(12.5);
        $rencontre->addPari($paris);
        $paris = new Pari();
        $paris->setMise(23.2);
        $rencontre->addPari($paris);
        $this->assertEquals(45.7, $rencontre->getTotalDesMises());
    }

    public function testGetJour(): void {
        self::bootKernel();
        $rencontre = new Rencontre();
        $rencontre->setHeureDebut(new \DateTime('2021-01-03 12:00:00'));
        $this->assertEquals("3 janvier 2021", $rencontre->isAvenir());
    }

    public function testGetHoraire(): void {
        self::bootKernel();
        $rencontre = new Rencontre();
        $rencontre->setHeureDebut(new \DateTime('2021-01-03 12:00:00'));
        $rencontre->setHeureFin(new \DateTime('2021-01-03 13:00:00'));
        $this->assertEquals("12:00 - 13:00", $rencontre->getHoraire());
    }

    public function testStatutString(): void {
        self::bootKernel();
        $rencontre = new Rencontre();
        $rencontre->setStatut(Rencontre::STATUT_TERMINE);
        $this->assertEquals("Terminé", $rencontre->getStatutString());
        $rencontre->setStatut(Rencontre::STATUT_A_VENIR);
        $this->assertEquals("À venir", $rencontre->getStatutString());
        $rencontre->setStatut(Rencontre::STATUT_EN_COURS);
        $this->assertEquals("En cours", $rencontre->getStatutString());
    }
}
