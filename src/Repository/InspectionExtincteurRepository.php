<?php

namespace App\Repository;

use App\Entity\InspectionExtincteur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InspectionExtincteur>
 */
class InspectionExtincteurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InspectionExtincteur::class);
    }

    /**
     * Obtenir les inspections avec leurs détails
     */
    public function getInspectionsAvecDetails(): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.extincteur', 'e')
            ->addSelect('e')
            ->leftJoin('i.inspectePar', 'u')
            ->addSelect('u')
            ->orderBy('i.dateInspection', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtenir la dernière inspection d'un extincteur
     */
    public function getDerniereInspection(int $extincteurId): ?InspectionExtincteur
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.extincteur = :extincteur')
            ->setParameter('extincteur', $extincteurId)
            ->orderBy('i.dateInspection', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Statistiques des inspections
     */
    public function getStatistiquesInspections(): array
    {
        return $this->createQueryBuilder('i')
            ->select('COUNT(i.id) as total, SUM(CASE WHEN i.valide = true THEN 1 ELSE 0 END) as valides')
            ->getQuery()
            ->getSingleResult();
    }
}
