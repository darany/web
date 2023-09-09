<?php
namespace App\Service;

use App\Entity\Pari;
use App\Entity\Rencontre;

class GestionPari
{
    /**
     * Calule le gain d'un pari sportif en fonction de la mise et de la cote
     *
     * @param Pari $pari
     * @return float
     */
    public function calculerGain(Pari $pari): float
    {
        $gain = 0;
        $rencontre = $pari->getRencontre();
        // Les ne peuvent être calculés que sur les matches terminés
        if ($rencontre->getStatut() == Rencontre::STATUT_TERMINE) {
            $gain = - $pari->getMise(); // Par défaut on perd la mise, sauf si...
            if ($pari->getEquipe() == $rencontre->getEquipeA()) {
                if ($rencontre->getScoreEquipeA() > $rencontre->getScoreEquipeB()) {
                    $gain = $pari->getMise() * $rencontre->getCoteEquipeA();
                }
            } else {
                if ($rencontre->getScoreEquipeB() > $rencontre->getScoreEquipeA()) {
                    $gain = $pari->getMise() * $rencontre->getCoteEquipeB();
                }
            }
        }        
        return $gain;
    }
}
