<?php

namespace App\Repository;

use App\Entity\VisitaPais;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VisitaPais|null find($id, $lockMode = null, $lockVersion = null)
 * @method VisitaPais|null findOneBy(array $criteria, array $orderBy = null)
 * @method VisitaPais[]    findAll()
 * @method VisitaPais[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VisitaPaisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VisitaPais::class);
    }

    // /**
    //  * @return VisitaPais[] Returns an array of VisitaPais objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?VisitaPais
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
