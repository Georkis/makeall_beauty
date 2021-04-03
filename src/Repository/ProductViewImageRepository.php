<?php

namespace App\Repository;

use App\Entity\ProductViewImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProductViewImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductViewImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductViewImage[]    findAll()
 * @method ProductViewImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductViewImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductViewImage::class);
    }

    // /**
    //  * @return ProductViewImage[] Returns an array of ProductViewImage objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProductViewImage
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
