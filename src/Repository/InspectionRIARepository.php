<?php

namespace App\Repository;

use App\Entity\InspectionRIA;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InspectionRIA>
 */
class InspectionRIARepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InspectionRIA::class);
    }

    public function getDerniereInspection(int $riaId): ?InspectionRIA
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.ria = :ria')
            ->setParameter('ria', $riaId)
            ->orderBy('i.dateInspection', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getInspectionsAvecDetails(): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.ria', 'r')
            ->addSelect('r')
            ->leftJoin('i.inspectePar', 'u')
            ->addSelect('u')
            ->orderBy('i.dateInspection', 'DESC')
            ->getQuery()
            ->getResult();
    }
}

