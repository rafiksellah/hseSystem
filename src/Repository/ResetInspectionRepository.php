<?php

namespace App\Repository;

use App\Entity\ResetInspection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResetInspection>
 */
class ResetInspectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResetInspection::class);
    }

    /**
     * Trouve les réinitialisations par type d'équipement
     */
    public function findByEquipmentType(string $equipmentType): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.equipmentType = :type')
            ->setParameter('type', $equipmentType)
            ->orderBy('r.resetDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les réinitialisations par période
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.resetDate BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->orderBy('r.resetDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les réinitialisations par type de reset
     */
    public function findByResetType(string $resetType): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.resetType = :type')
            ->setParameter('type', $resetType)
            ->orderBy('r.resetDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques des réinitialisations
     */
    public function getStatistics(): array
    {
        $qb = $this->createQueryBuilder('r');
        
        $total = $qb->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $byType = $this->createQueryBuilder('r')
            ->select('r.equipmentType, COUNT(r.id) as count')
            ->groupBy('r.equipmentType')
            ->getQuery()
            ->getResult();

        $byResetType = $this->createQueryBuilder('r')
            ->select('r.resetType, COUNT(r.id) as count')
            ->groupBy('r.resetType')
            ->getQuery()
            ->getResult();

        return [
            'total' => $total,
            'by_equipment_type' => $byType,
            'by_reset_type' => $byResetType
        ];
    }

    /**
     * Trouve les réinitialisations récentes
     */
    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.resetDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
