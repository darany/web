<?php

namespace App\Repository;

use App\Entity\Pari;
use App\Entity\Rencontre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pari>
 *
 * @method Pari|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pari|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pari[]    findAll()
 * @method Pari[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PariRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pari::class);
    }

    /**
     * Retourne le pari d'un utilisateur pour une rencontre donnée ou null
     *
     * @param integer $rencontreId
     * @param integer $userId
     * @return Pari|null
     */
   public function findOneByRencontreIdAndUserId(int $rencontreId, int  $userId): ?Pari
   {
       return $this->createQueryBuilder('pari')
           ->join('pari.rencontre', 'rencontre')
           ->join('pari.user', 'user')
           ->andWhere('rencontre.id = :rencontre_id')
           ->andWhere('user.id = :user_id')
           ->setParameter('rencontre_id', $rencontreId)
           ->setParameter('user_id', $userId)
           ->getQuery()
           ->getOneOrNullResult();
   }

   /**
    * Retourne les paris d'un utilisateur qui concernent les matchs terminés
    * ou un tableau vide si aucun pari n'est trouvé
    *
    * @param integer $userId
    * @return array|null
    */
   public function findParisByUserId(int $userId): ?array
   {
       return $this->createQueryBuilder('pari')
           ->join('pari.rencontre', 'rencontre')
           ->join('pari.user', 'user')
           ->andWhere('user.id = :user_id')
           ->setParameter('user_id', $userId)
           ->andWhere('rencontre.statut = :statut')
           ->setParameter('statut', Rencontre::STATUT_TERMINE)
           ->orderBy('pari.date', 'ASC')
           ->getQuery()
           ->getResult();
   }
}
