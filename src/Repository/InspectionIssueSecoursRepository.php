<?php

namespace App\Repository;

use App\Entity\InspectionIssueSecours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InspectionIssueSecours>
 */
class InspectionIssueSecoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InspectionIssueSecours::class);
    }

    public function getDerniereInspection(int $issueSecoursId): ?InspectionIssueSecours
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.issueSecours = :issue')
            ->setParameter('issue', $issueSecoursId)
            ->orderBy('i.dateInspection', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getInspectionsAvecDetails(): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.issueSecours', 'issue')
            ->addSelect('issue')
            ->leftJoin('i.inspectePar', 'u')
            ->addSelect('u')
            ->orderBy('i.dateInspection', 'DESC')
            ->getQuery()
            ->getResult();
    }
}

