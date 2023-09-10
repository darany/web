<?php

namespace App\Repository;

use App\Entity\Rencontre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rencontre>
 *
 * @method Rencontre|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rencontre|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rencontre[]    findAll()
 * @method Rencontre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RencontreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rencontre::class);
    }

   /**
    * @return Rencontre[] Retourne les matchs du jour 
    */
   public function rencontresDuJour(): array
   {
        $dateTime = new \DateTime();
        return $this->createQueryBuilder('rencontre')
           ->andWhere('rencontre.heureDebut BETWEEN :dateMin AND :dateMax')
           ->setParameters([
                 'dateMin' => $dateTime->format('Y-m-d 00:00:00'),
                 'dateMax' => $dateTime->format('Y-m-d 23:59:59')
            ])
           ->orderBy('rencontre.heureDebut', 'ASC')
           ->getQuery()
           ->getResult();
   }


   /**
    * @return Rencontre[] Retourne tous les matchs
    */
   public function toutesLesRencontres(): array
   {
        return $this->createQueryBuilder('rencontre')
            ->orderBy('rencontre.heureDebut', 'ASC')
            ->getQuery()
            ->getResult();
   }

   /**
    * @return Rencontre Retourne le détail d'un match
    */
   public function findRencontreById($id): ?Rencontre
   {
       return $this->createQueryBuilder('rencontre')
           ->andWhere('rencontre.id = :id')
           ->setParameter('id', $id)
           ->getQuery()
           ->getOneOrNullResult();
   }
}
