<?php

namespace App\Repository;

use App\Entity\Pari;
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

   public function findOneByRencontreIdAndUserId($rencontreId, $userId): ?Pari
   {
       return $this->createQueryBuilder('pari')
           ->join('pari.rencontre', 'rencontre')
           ->join('pari.user', 'user')
           ->andWhere('rencontre.id = :rencontre_id')
           ->andWhere('user.id = :user_id')
           ->setParameter('rencontre_id', $rencontreId)
           ->setParameter('user_id', $userId)
           ->getQuery()
           ->getOneOrNullResult()
       ;
   }
}
