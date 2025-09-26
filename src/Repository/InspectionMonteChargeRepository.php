<?php

namespace App\Repository;

use App\Entity\InspectionMonteCharge;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InspectionMonteCharge>
 */
class InspectionMonteChargeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InspectionMonteCharge::class);
    }

    /**
     * Obtenir les inspections avec détails
     */
    public function getInspectionsAvecDetails(): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.monteCharge', 'm')
            ->addSelect('m')
            ->leftJoin('i.inspectePar', 'u')
            ->addSelect('u')
            ->orderBy('i.dateInspection', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtenir la dernière inspection d'une porte
     */
    public function getDerniereInspectionPorte(int $monteChargeId, string $numeroPorte): ?InspectionMonteCharge
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.monteCharge = :monteCharge')
            ->andWhere('i.numeroPorte = :numeroPorte')
            ->setParameter('monteCharge', $monteChargeId)
            ->setParameter('numeroPorte', $numeroPorte)
            ->orderBy('i.dateInspection', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
