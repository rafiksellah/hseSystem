<?php

namespace App\Repository;

use App\Entity\RIA;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<RIA>
 */
class RIARepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RIA::class);
    }

    /**
     * Recherche des RIA par zone et filtres
     */
    public function searchRIA(array $searchParams, int $limit = 20, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('r')
            ->orderBy('r.zone', 'ASC')
            ->addOrderBy('r.numerotation', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if (!empty($searchParams['zone'])) {
            $qb->andWhere('r.zone = :zone')
                ->setParameter('zone', $searchParams['zone']);
        }

        if (!empty($searchParams['numerotation'])) {
            $qb->andWhere('r.numerotation LIKE :numerotation')
                ->setParameter('numerotation', '%' . $searchParams['numerotation'] . '%');
        }

        if (isset($searchParams['valide'])) {
            $qb->andWhere('r.valide = :valide')
                ->setParameter('valide', $searchParams['valide']);
        }

        return $qb->getQuery()->getResult();
    }

    public function countSearchRIA(array $searchParams): int
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)');

        if (!empty($searchParams['zone'])) {
            $qb->andWhere('r.zone = :zone')
                ->setParameter('zone', $searchParams['zone']);
        }

        if (!empty($searchParams['numerotation'])) {
            $qb->andWhere('r.numerotation LIKE :numerotation')
                ->setParameter('numerotation', '%' . $searchParams['numerotation'] . '%');
        }

        if (isset($searchParams['valide'])) {
            $qb->andWhere('r.valide = :valide')
                ->setParameter('valide', $searchParams['valide']);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Obtenir les RIA non validés par zone
     */
    public function getRIANonValidesParZone(string $zone): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.zone = :zone')
            ->andWhere('r.valide = false')
            ->setParameter('zone', $zone)
            ->orderBy('r.numerotation', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques des RIA par zone
     */
    public function getStatistiquesParZone(): array
    {
        return $this->createQueryBuilder('r')
            ->select('r.zone, COUNT(r.id) as total, SUM(CASE WHEN r.valide = true THEN 1 ELSE 0 END) as valides')
            ->groupBy('r.zone')
            ->getQuery()
            ->getResult();
    }
}
