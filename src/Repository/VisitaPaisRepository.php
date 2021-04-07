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

    public function findPaisesGroupDateCantidad()
    {
        return $this->createQueryBuilder('v')
            ->select('SUM(v.cantidad) as cantidad, v.pais, v.gec, v.ioc, v.continente')
            ->groupBy('v.gec')
            ->getQuery()->getResult();

    }

    public function findGroupYear()
    {
        return $this->createQueryBuilder('v')
            ->select("sum(v.cantidad) as cantidad, DATE_FORMAT(v.date, '%Y') as date")
            ->groupBy('date')
            ->getQuery()->getResult();
    }

    public function findVisitaTotal()
    {
        return $this->createQueryBuilder('v')
            ->select('SUM(v.cantidad) as cantidad')
            ->getQuery()->getOneOrNullResult();
    }

    public function findVisitaHoy($dateNow)
    {
        return $this->createQueryBuilder('v')
            ->select('SUM(v.cantidad) as cantidad')
            ->where('v.date = :dateNow')
            ->setParameter('dateNow', $dateNow)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $day
     * @return int|mixed|string|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @Description var string day with example -7 days
     */
    public function findVisitaBefore($day)
    {
        $hoy = new \DateTime(date('d-m-Y'));
        $sieteDia = $hoy->modify($day);

        return $this->createQueryBuilder('v')
            ->select('SUM(v.cantidad) as cantidad')
            ->where("v.date > '".$sieteDia->format('Y-m-d')."'")
            ->getQuery()->getOneOrNullResult();
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
