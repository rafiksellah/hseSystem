<?php

namespace App\Repository;

use App\Entity\InspectionExtinctionRAM;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InspectionExtinctionRAM>
 */
class InspectionExtinctionRAMRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InspectionExtinctionRAM::class);
    }

    public function getDerniereInspection(int $extinctionRAMId): ?InspectionExtinctionRAM
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.extinctionLocaliseeRAM = :extinction')
            ->setParameter('extinction', $extinctionRAMId)
            ->orderBy('i.dateInspection', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getInspectionsAvecDetails(): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.extinctionLocaliseeRAM', 'e')
            ->addSelect('e')
            ->leftJoin('i.inspectePar', 'u')
            ->addSelect('u')
            ->orderBy('i.dateInspection', 'DESC')
            ->getQuery()
            ->getResult();
    }
}

