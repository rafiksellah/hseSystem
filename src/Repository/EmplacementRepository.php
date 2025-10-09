<?php

namespace App\Repository;

use App\Entity\Emplacement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Emplacement>
 */
class EmplacementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Emplacement::class);
    }

    /**
     * Trouver les emplacements par type d'équipement
     */
    public function findByTypeEquipement(string $typeEquipement): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.typeEquipement = :type')
            ->setParameter('type', $typeEquipement)
            ->orderBy('e.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Trouver les emplacements par zone
     */
    public function findByZone(string $zone): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.zone = :zone')
            ->setParameter('zone', $zone)
            ->orderBy('e.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouver un emplacement par nom (insensible à la casse)
     */
    public function findByName(string $nom): ?Emplacement
    {
        return $this->createQueryBuilder('e')
            ->andWhere('LOWER(e.nom) = LOWER(:nom)')
            ->setParameter('nom', $nom)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Obtenir tous les emplacements groupés par zone
     */
    public function findAllGroupedByZone(): array
    {
        $emplacements = $this->findBy([], ['zone' => 'ASC', 'nom' => 'ASC']);
        
        $grouped = [];
        foreach ($emplacements as $emplacement) {
            $zone = $emplacement->getZone();
            if (!isset($grouped[$zone])) {
                $grouped[$zone] = [];
            }
            $grouped[$zone][] = $emplacement;
        }
        
        return $grouped;
    }

    /**
     * Chercher des emplacements par nom (pour l'autocomplétion)
     */
    public function searchByName(string $search): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.nom LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('e.nom', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }
}
