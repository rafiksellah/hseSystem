<?php

namespace App\Repository;

use App\Entity\Desenfumage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Desenfumage>
 */
class DesenfumageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Desenfumage::class);
    }

    public function searchDesenfumages(array $searchParams, int $limit = 20, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('d')
            ->orderBy('d.zone', 'ASC')
            ->addOrderBy('d.numerotation', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if (!empty($searchParams['zone'])) {
            $qb->andWhere('d.zone = :zone')
                ->setParameter('zone', $searchParams['zone']);
        }

        if (!empty($searchParams['numerotation'])) {
            $qb->andWhere('d.numerotation LIKE :numerotation')
                ->setParameter('numerotation', '%' . $searchParams['numerotation'] . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function countSearchDesenfumages(array $searchParams): int
    {
        $qb = $this->createQueryBuilder('d')
            ->select('COUNT(d.id)');

        if (!empty($searchParams['zone'])) {
            $qb->andWhere('d.zone = :zone')
                ->setParameter('zone', $searchParams['zone']);
        }

        if (!empty($searchParams['numerotation'])) {
            $qb->andWhere('d.numerotation LIKE :numerotation')
                ->setParameter('numerotation', '%' . $searchParams['numerotation'] . '%');
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getStatistiques(): array
    {
        return $this->createQueryBuilder('d')
            ->select('COUNT(d.id) as total')
            ->getQuery()
            ->getOneOrNullResult();
    }
}

