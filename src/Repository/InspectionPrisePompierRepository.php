<?php

namespace App\Repository;

use App\Entity\InspectionPrisePompier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InspectionPrisePompier>
 */
class InspectionPrisePompierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InspectionPrisePompier::class);
    }

    /**
     * Obtenir la dernière inspection d'une prise pompier
     */
    public function getDerniereInspection(int $prisePompierId): ?InspectionPrisePompier
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.prisePompier = :prise')
            ->setParameter('prise', $prisePompierId)
            ->orderBy('i.dateInspection', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Obtenir les inspections avec détails
     */
    public function getInspectionsAvecDetails(): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.prisePompier', 'p')
            ->addSelect('p')
            ->leftJoin('i.inspectePar', 'u')
            ->addSelect('u')
            ->orderBy('i.dateInspection', 'DESC')
            ->getQuery()
            ->getResult();
    }
}

