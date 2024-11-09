<?php

namespace App\Repository;

use App\Entity\Article;
use App\Model\SearchData;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    private PaginatorInterface $paginatorInterface;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginatorInterface)
    {
        parent::__construct($registry, Article::class);

        $this->paginatorInterface = $paginatorInterface;
    }

//    /**
//     * @return Article[] Returns an array of Article objects
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

//    public function findOneBySomeField($value): ?Article
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


    /**
     * À adapter en fonction de l'id marque et l'id catégorie
     * 
     *
     * @param SearchData $searchData
     * @return PaginationInterface
     */
    public function findBySearch(SearchData $searchData, ?int $id = null, ?string $type = null): PaginationInterface
    {
        $queryBuilder = $this->createQueryBuilder('a');

        if(!empty($searchData->query))
        {
            $queryBuilder->where('a.nomArticle LIKE :query')
            ->setParameter('query', '%' . $searchData->query . '%');
        }
        
        if(!empty($id) && !empty($type))
        {
            if($type == 'marque')
                $queryBuilder->andWhere('a.idMarque = :id')->setParameter('id', $id);
            else 
                $queryBuilder->andWhere('a.idCategorie = :id')->setParameter('id', $id);
        }
        
        $queryBuilder->orderBy('a.id', 'ASC');

        $query = $queryBuilder->getQuery()->getResult();

        return $this->paginatorInterface->paginate($query, $searchData->page,12);
    }

}
