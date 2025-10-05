<?php

namespace App\Repository;

use App\Entity\IssueSecours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IssueSecours>
 */
class IssueSecoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IssueSecours::class);
    }

    public function searchIssuesSecours(array $searchParams, int $limit = 20, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('i')
            ->orderBy('i.zone', 'ASC')
            ->addOrderBy('i.numerotation', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if (!empty($searchParams['zone'])) {
            $qb->andWhere('i.zone = :zone')
                ->setParameter('zone', $searchParams['zone']);
        }

        if (!empty($searchParams['numerotation'])) {
            $qb->andWhere('i.numerotation LIKE :numerotation')
                ->setParameter('numerotation', '%' . $searchParams['numerotation'] . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function countSearchIssuesSecours(array $searchParams): int
    {
        $qb = $this->createQueryBuilder('i')
            ->select('COUNT(i.id)');

        if (!empty($searchParams['zone'])) {
            $qb->andWhere('i.zone = :zone')
                ->setParameter('zone', $searchParams['zone']);
        }

        if (!empty($searchParams['numerotation'])) {
            $qb->andWhere('i.numerotation LIKE :numerotation')
                ->setParameter('numerotation', '%' . $searchParams['numerotation'] . '%');
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getStatistiques(): array
    {
        return $this->createQueryBuilder('i')
            ->select('COUNT(i.id) as total')
            ->getQuery()
            ->getOneOrNullResult();
    }
}

