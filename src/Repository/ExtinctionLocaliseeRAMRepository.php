<?php

namespace App\Repository;

use App\Entity\ExtinctionLocaliseeRAM;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExtinctionLocaliseeRAM>
 */
class ExtinctionLocaliseeRAMRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExtinctionLocaliseeRAM::class);
    }

    public function searchExtinctionsRAM(array $searchParams, int $limit = 20, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('e')
            ->orderBy('e.zone', 'ASC')
            ->addOrderBy('e.numerotation', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if (!empty($searchParams['zone'])) {
            $qb->andWhere('e.zone = :zone')
                ->setParameter('zone', $searchParams['zone']);
        }

        if (!empty($searchParams['numerotation'])) {
            $qb->andWhere('e.numerotation LIKE :numerotation')
                ->setParameter('numerotation', '%' . $searchParams['numerotation'] . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function countSearchExtinctionsRAM(array $searchParams): int
    {
        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)');

        if (!empty($searchParams['zone'])) {
            $qb->andWhere('e.zone = :zone')
                ->setParameter('zone', $searchParams['zone']);
        }

        if (!empty($searchParams['numerotation'])) {
            $qb->andWhere('e.numerotation LIKE :numerotation')
                ->setParameter('numerotation', '%' . $searchParams['numerotation'] . '%');
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getStatistiques(): array
    {
        return $this->createQueryBuilder('e')
            ->select('COUNT(e.id) as total')
            ->getQuery()
            ->getOneOrNullResult();
    }
}

