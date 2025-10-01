<?php

namespace App\Repository;

use App\Entity\Extincteur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Extincteur>
 */
class ExtincteurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Extincteur::class);
    }

    /**
     * Recherche des extincteurs par emplacement et filtres
     */
    public function searchExtincteurs(array $searchParams, int $limit = 20, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('e')
            ->orderBy('e.emplacement', 'ASC')
            ->addOrderBy('e.numerotation', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        // Filtre par zone (SIMTIS ou SIMTIS TISSAGE) - pour les permissions
        if (!empty($searchParams['zone'])) {
            $qb->andWhere('e.zone = :zone')
                ->setParameter('zone', $searchParams['zone']);
        }

        // Filtre par emplacement (Administration, Broderie, etc.)
        if (!empty($searchParams['emplacement'])) {
            $qb->andWhere('e.emplacement = :emplacement')
                ->setParameter('emplacement', $searchParams['emplacement']);
        }

        // Filtre par numérotation
        if (!empty($searchParams['numerotation'])) {
            $qb->andWhere('e.numerotation LIKE :numerotation')
                ->setParameter('numerotation', '%' . $searchParams['numerotation'] . '%');
        }

        // Filtre par statut de validation
        if (isset($searchParams['valide'])) {
            $qb->andWhere('e.valide = :valide')
                ->setParameter('valide', $searchParams['valide']);
        }

        return $qb->getQuery()->getResult();
    }

    public function countSearchExtincteurs(array $searchParams): int
    {
        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)');

        // Filtre par zone (SIMTIS ou SIMTIS TISSAGE) - pour les permissions
        if (!empty($searchParams['zone'])) {
            $qb->andWhere('e.zone = :zone')
                ->setParameter('zone', $searchParams['zone']);
        }

        // Filtre par emplacement (Administration, Broderie, etc.)
        if (!empty($searchParams['emplacement'])) {
            $qb->andWhere('e.emplacement = :emplacement')
                ->setParameter('emplacement', $searchParams['emplacement']);
        }

        // Filtre par numérotation
        if (!empty($searchParams['numerotation'])) {
            $qb->andWhere('e.numerotation LIKE :numerotation')
                ->setParameter('numerotation', '%' . $searchParams['numerotation'] . '%');
        }

        // Filtre par statut de validation
        if (isset($searchParams['valide'])) {
            $qb->andWhere('e.valide = :valide')
                ->setParameter('valide', $searchParams['valide']);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Obtenir les extincteurs non validés par zone
     */
    public function getExtincteursNonValidesParZone(string $zone): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.zone = :zone')
            ->andWhere('e.valide = false')
            ->setParameter('zone', $zone)
            ->orderBy('e.numerotation', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques des extincteurs par zone
     */
    public function getStatistiquesParZone(): array
    {
        return $this->createQueryBuilder('e')
            ->select('e.zone, COUNT(e.id) as total, SUM(CASE WHEN e.valide = true THEN 1 ELSE 0 END) as valides')
            ->groupBy('e.zone')
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtenir les extincteurs avec leurs dernières inspections
     */
    public function getExtincteursAvecInspections(): array
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.inspections', 'i')
            ->addSelect('i')
            ->leftJoin('i.inspectePar', 'u')
            ->addSelect('u')
            ->orderBy('e.zone', 'ASC')
            ->addOrderBy('e.numerotation', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
