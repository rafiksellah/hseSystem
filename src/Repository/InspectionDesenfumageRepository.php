<?php

namespace App\Repository;

use App\Entity\InspectionDesenfumage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InspectionDesenfumage>
 */
class InspectionDesenfumageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InspectionDesenfumage::class);
    }

    public function getDerniereInspection(int $desenfumageId): ?InspectionDesenfumage
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.desenfumage = :desenfumage')
            ->setParameter('desenfumage', $desenfumageId)
            ->orderBy('i.dateInspection', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getInspectionsAvecDetails(): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.desenfumage', 'd')
            ->addSelect('d')
            ->leftJoin('i.inspectePar', 'u')
            ->addSelect('u')
            ->orderBy('i.dateInspection', 'DESC')
            ->getQuery()
            ->getResult();
    }
}

