<?php

namespace App\Repository;

use App\Entity\ArticleFavori;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<ArticleFavori>
 *
 * @method ArticleFavori|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArticleFavori|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArticleFavori[]    findAll()
 * @method ArticleFavori[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleFavoriRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticleFavori::class);
    }

//    /**
//     * @return ArticleFavori[] Returns an array of ArticleFavori objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ArticleFavori
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
