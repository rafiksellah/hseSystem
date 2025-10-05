<?php

namespace App\Repository;

use App\Entity\InspectionSirene;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InspectionSirene>
 */
class InspectionSireneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InspectionSirene::class);
    }

    public function getDerniereInspection(int $sireneId): ?InspectionSirene
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.sirene = :sirene')
            ->setParameter('sirene', $sireneId)
            ->orderBy('i.dateInspection', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getInspectionsAvecDetails(): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.sirene', 's')
            ->addSelect('s')
            ->leftJoin('i.inspectePar', 'u')
            ->addSelect('u')
            ->orderBy('i.dateInspection', 'DESC')
            ->getQuery()
            ->getResult();
    }
}

