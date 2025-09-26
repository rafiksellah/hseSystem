<?php

namespace App\Repository;

use App\Entity\MonteCharge;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MonteCharge>
 */
class MonteChargeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MonteCharge::class);
    }

    /**
     * Obtenir tous les monte-charges avec leurs inspections
     */
    public function getMonteChargesAvecInspections(): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.inspections', 'i')
            ->addSelect('i')
            ->leftJoin('i.inspectePar', 'u')
            ->addSelect('u')
            ->orderBy('m.type', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques des monte-charges
     */
    public function getStatistiques(): array
    {
        return $this->createQueryBuilder('m')
            ->select('m.type, COUNT(i.id) as nombreInspections')
            ->leftJoin('m.inspections', 'i')
            ->groupBy('m.type')
            ->getQuery()
            ->getResult();
    }
}
