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

    public function save(InspectionMonteCharge $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(InspectionMonteCharge $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return InspectionMonteCharge[] Returns an array of InspectionMonteCharge objects
     */
    public function findByMonteCharge(int $monteChargeId): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.monteCharge = :monteChargeId')
            ->setParameter('monteChargeId', $monteChargeId)
            ->orderBy('i.dateInspection', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return InspectionMonteCharge[] Returns an array of InspectionMonteCharge objects
     */
    public function findByInspecteur(int $inspecteurId): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.inspecteur = :inspecteurId')
            ->setParameter('inspecteurId', $inspecteurId)
            ->orderBy('i.dateInspection', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return InspectionMonteCharge[] Returns an array of InspectionMonteCharge objects
     */
    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('i')
            ->orderBy('i.dateInspection', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les inspections avec les détails des monte-charges
     */
    public function getInspectionsAvecDetails(): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.monteCharge', 'm')
            ->leftJoin('i.inspecteur', 'u')
            ->addSelect('m', 'u')
            ->orderBy('i.dateInspection', 'DESC')
            ->getQuery()
            ->getResult();
    }
}