<?php
namespace App\Tests\Service;

use App\Entity\Rencontre;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

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

    private function testRencontreTerminee(): void {
        self::bootKernel();
        $rencontre = new Rencontre();
        $rencontre->setStatut(Rencontre::STATUT_TERMINE);
        $rencontre->setScoreEquipeA(55);
        $rencontre->setScoreEquipeB(20);
        $scores = $rencontre->getDisplayableScores();
        $this->assertEquals("55 - 20", $scores);
    }
}
