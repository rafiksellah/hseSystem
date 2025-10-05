<?php

namespace App\Repository;

use App\Entity\Sirene;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sirene>
 */
class SireneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sirene::class);
    }

    public function searchSirenes(array $searchParams, int $limit = 20, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('s')
            ->orderBy('s.zone', 'ASC')
            ->addOrderBy('s.numerotation', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if (!empty($searchParams['zone'])) {
            $qb->andWhere('s.zone = :zone')
                ->setParameter('zone', $searchParams['zone']);
        }

        if (!empty($searchParams['numerotation'])) {
            $qb->andWhere('s.numerotation LIKE :numerotation')
                ->setParameter('numerotation', '%' . $searchParams['numerotation'] . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function countSearchSirenes(array $searchParams): int
    {
        $qb = $this->createQueryBuilder('s')
            ->select('COUNT(s.id)');

        if (!empty($searchParams['zone'])) {
            $qb->andWhere('s.zone = :zone')
                ->setParameter('zone', $searchParams['zone']);
        }

        if (!empty($searchParams['numerotation'])) {
            $qb->andWhere('s.numerotation LIKE :numerotation')
                ->setParameter('numerotation', '%' . $searchParams['numerotation'] . '%');
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getStatistiques(): array
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT(s.id) as total')
            ->getQuery()
            ->getOneOrNullResult();
    }
}

