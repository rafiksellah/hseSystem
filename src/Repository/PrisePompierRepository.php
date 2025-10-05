<?php

namespace App\Repository;

use App\Entity\PrisePompier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrisePompier>
 */
class PrisePompierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrisePompier::class);
    }

    /**
     * Recherche des prises pompiers par zone et filtres
     */
    public function searchPrisesPompiers(array $searchParams, int $limit = 20, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('p')
            ->orderBy('p.zone', 'ASC')
            ->addOrderBy('p.emplacement', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        // Filtre par zone
        if (!empty($searchParams['zone'])) {
            $qb->andWhere('p.zone = :zone')
                ->setParameter('zone', $searchParams['zone']);
        }

        // Filtre par emplacement
        if (!empty($searchParams['emplacement'])) {
            $qb->andWhere('p.emplacement LIKE :emplacement')
                ->setParameter('emplacement', '%' . $searchParams['emplacement'] . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function countSearchPrisesPompiers(array $searchParams): int
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)');

        // Filtre par zone
        if (!empty($searchParams['zone'])) {
            $qb->andWhere('p.zone = :zone')
                ->setParameter('zone', $searchParams['zone']);
        }

        // Filtre par emplacement
        if (!empty($searchParams['emplacement'])) {
            $qb->andWhere('p.emplacement LIKE :emplacement')
                ->setParameter('emplacement', '%' . $searchParams['emplacement'] . '%');
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Statistiques des prises pompiers
     */
    public function getStatistiques(): array
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id) as total')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Obtenir les prises pompiers avec leurs dernières inspections
     */
    public function getPrisesPompiersAvecInspections(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.inspections', 'i')
            ->addSelect('i')
            ->leftJoin('i.inspectePar', 'u')
            ->addSelect('u')
            ->orderBy('p.zone', 'ASC')
            ->addOrderBy('p.emplacement', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

