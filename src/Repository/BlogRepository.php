<?php

namespace App\Repository;

use App\Entity\Blog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Blog|null find($id, $lockMode = null, $lockVersion = null)
 * @method Blog|null findOneBy(array $criteria, array $orderBy = null)
 * @method Blog[]    findAll()
 * @method Blog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BlogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Blog::class);
    }

    public function findBlogTag($slug, $first = 1, $maxResult = null)
    {
        return $this->createQueryBuilder('b')
            ->join('b.tags', 't')
            ->where('t.slug = :slug')
            ->andWhere('b.public = 1')
            ->setParameter('slug', $slug)
            ->setFirstResult($first)
            ->setMaxResults($maxResult)
            ->getQuery()->getResult();
    }

    public function findBlogRelacionado($id, $slug)
    {
        return $this->createQueryBuilder('b')
            ->join('b.category', 'c')
            ->where('c.slug = :slug')
            ->andWhere('b.id <> :id')
            ->setParameter('slug', $slug)
            ->setParameter('id', $id)
            ->getQuery()->getResult();
    }

    // /**
    //  * @return Blog[] Returns an array of Blog objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Blog
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
