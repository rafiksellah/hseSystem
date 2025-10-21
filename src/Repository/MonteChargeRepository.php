<?php

namespace App\Repository;

use App\Entity\MonteCharge;
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

    public function save(MonteCharge $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MonteCharge $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return MonteCharge[] Returns an array of MonteCharge objects
     */
    public function findByZone(string $zone): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.zone = :zone')
            ->setParameter('zone', $zone)
            ->orderBy('m.numeroMonteCharge', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return MonteCharge[] Returns an array of MonteCharge objects
     */
    public function findByEmplacement(string $emplacement): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.emplacement = :emplacement')
            ->setParameter('emplacement', $emplacement)
            ->orderBy('m.numeroMonteCharge', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return MonteCharge[] Returns an array of MonteCharge objects
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.numeroMonteCharge', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return MonteCharge[] Returns an array of MonteCharge objects with filters
     */
    public function findWithFilters(string $search = '', string $zone = '', string $statut = '', string $emplacement = ''): array
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.inspections', 'i')
            ->addSelect('i')
            ->orderBy('m.numeroMonteCharge', 'ASC');

        if (!empty($search)) {
            $qb->andWhere('m.numeroMonteCharge LIKE :search OR m.emplacement LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if (!empty($zone)) {
            $qb->andWhere('m.zone = :zone')
                ->setParameter('zone', $zone);
        }

        if (!empty($emplacement)) {
            $qb->andWhere('m.emplacement = :emplacement')
                ->setParameter('emplacement', $emplacement);
        }

        if (!empty($statut)) {
            if ($statut === 'Conforme') {
                // Une inspection est conforme si tous les critères sont "Oui"
                $qb->andWhere('i.portesFermees = :oui')
                   ->andWhere('i.consignesRespectees = :oui')
                   ->andWhere('i.finsCoursesFonctionnent = :oui')
                   ->andWhere('i.essaiVideRealise = :oui')
                   ->andWhere('i.isActive = true')
                   ->setParameter('oui', 'Oui');
            } elseif ($statut === 'Non conforme') {
                // Une inspection est non conforme si au moins un critère est "Non"
                $qb->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->eq('i.portesFermees', ':non'),
                        $qb->expr()->eq('i.consignesRespectees', ':non'),
                        $qb->expr()->eq('i.finsCoursesFonctionnent', ':non'),
                        $qb->expr()->eq('i.essaiVideRealise', ':non')
                    )
                )
                ->andWhere('i.isActive = true')
                ->setParameter('non', 'Non');
            } elseif ($statut === 'Non inspecté') {
                $qb->andWhere('i.id IS NULL');
            }
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne les statistiques des monte-charges
     */
    public function getStatistiques(): array
    {
        $qb = $this->createQueryBuilder('m');
        
        $total = $qb->select('COUNT(m.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // Statistiques par zone
        $statsParZone = $this->createQueryBuilder('m')
            ->select('m.zone, COUNT(m.id) as total')
            ->groupBy('m.zone')
            ->getQuery()
            ->getResult();

        // Statistiques par emplacement
        $statsParEmplacement = $this->createQueryBuilder('m')
            ->select('m.emplacement, COUNT(m.id) as total')
            ->groupBy('m.emplacement')
            ->getQuery()
            ->getResult();

        // Format pour le template dashboard
        $statsForTemplate = [];
        foreach ($statsParZone as $stat) {
            $statsForTemplate[] = [
                'type' => $stat['zone'],
                'nombreInspections' => $stat['total']
            ];
        }

        return [
            'total' => $total,
            'par_zone' => $statsParZone,
            'par_emplacement' => $statsParEmplacement,
            'stats_for_template' => $statsForTemplate
        ];
    }
}