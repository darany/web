<?php
namespace App\Service;

use App\Service\GestionPari;
use App\Entity\Rencontre;
use Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Gestion des rencontres
 */
class GestionRencontre
{
    private GestionPari $gestionPari;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(GestionPari $gestionPari, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->gestionPari = $gestionPari;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * Clôture un match et calcule les gains des paris
     * 
     * Un commentateur peut clore un match à l’issue de la rencontre, l’action est manuelle, 
     * car un match peut durer plus longtemps que le temps donné s’il y a eu prolongation. 
     * Si le temps a été dépassé, l’action de « clore » un match change l’heure de fin de celui-ci.
     * La fermeture d’un match calcule le montant gagné ou perdu par l’utilisateur en fonction de son pari.
     *
     * @param Rencontre $rencontre
     * @return void
     */
    public function cloturer(Rencontre $rencontre): Rencontre
    {
        $now = new \DateTime(); //Heure de cloture du match
        $this->logger->debug('Clôture du match {rencontreId} à {now}', [
            'rencontreId' => $rencontre->getId(), 'now' => $now->format('Y-m-d H:i:s')]);
        //$this->entityManager->getConnection()->setAutoCommit(false);
        $this->entityManager->getConnection()->setNestTransactionsWithSavepoints(true);
        $this->entityManager->beginTransaction();
        try {
            // Clôture du match
            $rencontre->setStatut(Rencontre::STATUT_TERMINE);
            $rencontre->setHeureFin($now);
            $this->entityManager->persist($rencontre);

            // Calculer les gains des paris
            foreach ($rencontre->getParis() as $pari) {
                $gain = $this->gestionPari->calculerGain($pari);
                $pari->setGain($gain);
                $this->logger->debug('Gain du pari {pariId} = {gain}', [
                    'pariId' => $pari->getId(), 'gain' => $gain]);
                $this->entityManager->persist($pari);
            }

            // Commit de la transaction
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollBack();
            $this->logger->critical('Got exeception: {message}', ['message' => $e->getMessage()]);
            throw $e;
        } finally {
            $this->entityManager->getConnection()->setAutoCommit(true);
        }
        return $rencontre;
    }
}