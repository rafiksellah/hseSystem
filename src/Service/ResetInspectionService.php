<?php

namespace App\Service;

use App\Entity\ResetInspection;
use App\Entity\InspectionExtincteur;
use App\Entity\InspectionSirene;
use App\Entity\InspectionExtinctionRAM;
use App\Entity\InspectionMonteCharge;
use App\Entity\InspectionRIA;
use App\Entity\InspectionDesenfumage;
use App\Entity\InspectionIssueSecours;
use App\Entity\InspectionPrisePompier;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class ResetInspectionService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {}

    /**
     * Réinitialise toutes les inspections d'un type d'équipement
     */
    public function resetInspectionsByType(string $equipmentType, string $resetType = 'manual', ?User $resetBy = null, ?string $reason = null): array
    {
        $results = [
            'deleted' => 0,
            'errors' => []
        ];

        try {
            switch ($equipmentType) {
                case 'extincteur':
                    $results = $this->resetExtincteurInspections($resetType, $resetBy, $reason);
                    break;
                case 'sirene':
                    $results = $this->resetSireneInspections($resetType, $resetBy, $reason);
                    break;
                case 'extinction_ram':
                    $results = $this->resetExtinctionRAMInspections($resetType, $resetBy, $reason);
                    break;
                case 'monte_charge':
                    $results = $this->resetMonteChargeInspections($resetType, $resetBy, $reason);
                    break;
                case 'ria':
                    $results = $this->resetRIAInspections($resetType, $resetBy, $reason);
                    break;
                case 'desenfumage':
                    $results = $this->resetDesenfumageInspections($resetType, $resetBy, $reason);
                    break;
                case 'issue_secours':
                    $results = $this->resetIssueSecoursInspections($resetType, $resetBy, $reason);
                    break;
                case 'prise_pompier':
                    $results = $this->resetPrisePompierInspections($resetType, $resetBy, $reason);
                    break;
                default:
                    throw new \InvalidArgumentException("Type d'équipement non supporté: {$equipmentType}");
            }

            $this->entityManager->flush();
            $this->logger->info("Réinitialisation {$resetType} des inspections {$equipmentType} terminée", $results);

        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de la réinitialisation {$equipmentType}: " . $e->getMessage());
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Réinitialise toutes les inspections (tous types)
     */
    public function resetAllInspections(string $resetType = 'manual', ?User $resetBy = null, ?string $reason = null): array
    {
        $allResults = [];
        $equipmentTypes = [
            'extincteur', 
            'sirene', 
            'extinction_ram', 
            'monte_charge',
            'ria',
            'desenfumage',
            'issue_secours',
            'prise_pompier'
        ];

        foreach ($equipmentTypes as $type) {
            $allResults[$type] = $this->resetInspectionsByType($type, $resetType, $resetBy, $reason);
        }

        return $allResults;
    }

    /**
     * Réinitialise les inspections d'extincteurs
     */
    private function resetExtincteurInspections(string $resetType, ?User $resetBy, ?string $reason): array
    {
        $results = ['deleted' => 0, 'errors' => []];

        // Récupérer toutes les inspections actives
        $inspections = $this->entityManager->getRepository(InspectionExtincteur::class)
            ->createQueryBuilder('i')
            ->where('i.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();

        foreach ($inspections as $inspection) {
            try {
                // Archiver l'inspection avant suppression
                $this->archiveInspection($inspection, 'extincteur', $resetType, $resetBy, $reason);
                
                // Supprimer physiquement l'inspection
                $this->entityManager->remove($inspection);
                $results['deleted']++;

            } catch (\Exception $e) {
                $results['errors'][] = "Erreur inspection ID {$inspection->getId()}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Réinitialise les inspections de sirènes
     */
    private function resetSireneInspections(string $resetType, ?User $resetBy, ?string $reason): array
    {
        $results = ['deleted' => 0, 'errors' => []];

        $inspections = $this->entityManager->getRepository(InspectionSirene::class)
            ->createQueryBuilder('i')
            ->where('i.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();

        foreach ($inspections as $inspection) {
            try {
                $this->archiveInspection($inspection, 'sirene', $resetType, $resetBy, $reason);
                $this->entityManager->remove($inspection);
                $results['deleted']++;

            } catch (\Exception $e) {
                $results['errors'][] = "Erreur inspection ID {$inspection->getId()}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Réinitialise les inspections d'extinction RAM
     */
    private function resetExtinctionRAMInspections(string $resetType, ?User $resetBy, ?string $reason): array
    {
        $results = ['deleted' => 0, 'errors' => []];

        $inspections = $this->entityManager->getRepository(InspectionExtinctionRAM::class)
            ->createQueryBuilder('i')
            ->where('i.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();

        foreach ($inspections as $inspection) {
            try {
                $this->archiveInspection($inspection, 'extinction_ram', $resetType, $resetBy, $reason);
                $this->entityManager->remove($inspection);
                $results['deleted']++;

            } catch (\Exception $e) {
                $results['errors'][] = "Erreur inspection ID {$inspection->getId()}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Réinitialise les inspections de monte-charge
     */
    private function resetMonteChargeInspections(string $resetType, ?User $resetBy, ?string $reason): array
    {
        $results = ['deleted' => 0, 'errors' => []];

        $inspections = $this->entityManager->getRepository(InspectionMonteCharge::class)
            ->createQueryBuilder('i')
            ->where('i.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();

        foreach ($inspections as $inspection) {
            try {
                $this->archiveInspection($inspection, 'monte_charge', $resetType, $resetBy, $reason);
                $this->entityManager->remove($inspection);
                $results['deleted']++;

            } catch (\Exception $e) {
                $results['errors'][] = "Erreur inspection ID {$inspection->getId()}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Réinitialise les inspections RIA
     */
    private function resetRIAInspections(string $resetType, ?User $resetBy, ?string $reason): array
    {
        $results = ['deleted' => 0, 'errors' => []];

        // RIA n'a pas de champ isActive, on récupère toutes les inspections
        $inspections = $this->entityManager->getRepository(InspectionRIA::class)->findAll();

        foreach ($inspections as $inspection) {
            try {
                $this->archiveInspection($inspection, 'ria', $resetType, $resetBy, $reason);
                $this->entityManager->remove($inspection);
                $results['deleted']++;

            } catch (\Exception $e) {
                $results['errors'][] = "Erreur inspection ID {$inspection->getId()}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Réinitialise les inspections Désenfumage
     */
    private function resetDesenfumageInspections(string $resetType, ?User $resetBy, ?string $reason): array
    {
        $results = ['deleted' => 0, 'errors' => []];

        // Désenfumage n'a pas de champ isActive
        $inspections = $this->entityManager->getRepository(InspectionDesenfumage::class)->findAll();

        foreach ($inspections as $inspection) {
            try {
                $this->archiveInspection($inspection, 'desenfumage', $resetType, $resetBy, $reason);
                $this->entityManager->remove($inspection);
                $results['deleted']++;

            } catch (\Exception $e) {
                $results['errors'][] = "Erreur inspection ID {$inspection->getId()}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Réinitialise les inspections Issues de Secours
     */
    private function resetIssueSecoursInspections(string $resetType, ?User $resetBy, ?string $reason): array
    {
        $results = ['deleted' => 0, 'errors' => []];

        // Issues de Secours n'a pas de champ isActive
        $inspections = $this->entityManager->getRepository(InspectionIssueSecours::class)->findAll();

        foreach ($inspections as $inspection) {
            try {
                $this->archiveInspection($inspection, 'issue_secours', $resetType, $resetBy, $reason);
                $this->entityManager->remove($inspection);
                $results['deleted']++;

            } catch (\Exception $e) {
                $results['errors'][] = "Erreur inspection ID {$inspection->getId()}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Réinitialise les inspections Prises Pompiers
     */
    private function resetPrisePompierInspections(string $resetType, ?User $resetBy, ?string $reason): array
    {
        $results = ['deleted' => 0, 'errors' => []];

        // Prises Pompiers n'a pas de champ isActive
        $inspections = $this->entityManager->getRepository(InspectionPrisePompier::class)->findAll();

        foreach ($inspections as $inspection) {
            try {
                $this->archiveInspection($inspection, 'prise_pompier', $resetType, $resetBy, $reason);
                $this->entityManager->remove($inspection);
                $results['deleted']++;

            } catch (\Exception $e) {
                $results['errors'][] = "Erreur inspection ID {$inspection->getId()}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Archive une inspection dans ResetInspection avant suppression
     */
    private function archiveInspection($inspection, string $equipmentType, string $resetType, ?User $resetBy, ?string $reason): void
    {
        $resetInspection = new ResetInspection();
        $resetInspection->setEquipmentType($equipmentType);
        $resetInspection->setResetType($resetType);
        $resetInspection->setResetDate(new \DateTime());
        $resetInspection->setResetBy($resetBy);
        $resetInspection->setResetReason($reason);

        // Récupérer les données de l'équipement
        $equipment = null;
        $equipmentName = 'N/A';
        $equipmentId = 0;

        switch ($equipmentType) {
            case 'extincteur':
                $equipment = $inspection->getExtincteur();
                $equipmentName = $equipment ? $equipment->getNumerotation() : 'N/A';
                $equipmentId = $equipment ? $equipment->getId() : 0;
                break;
            case 'sirene':
                $equipment = $inspection->getSirene();
                $equipmentName = $equipment ? $equipment->getNumerotation() : 'N/A';
                $equipmentId = $equipment ? $equipment->getId() : 0;
                break;
            case 'extinction_ram':
                $equipment = $inspection->getExtinctionLocaliseeRAM();
                $equipmentName = $equipment ? $equipment->getNumerotation() : 'N/A';
                $equipmentId = $equipment ? $equipment->getId() : 0;
                break;
            case 'monte_charge':
                $equipment = $inspection->getMonteCharge();
                $equipmentName = $equipment ? $equipment->getNumeroMonteCharge() : 'N/A';
                $equipmentId = $equipment ? $equipment->getId() : 0;
                break;
            case 'ria':
                $equipment = $inspection->getRia();
                $equipmentName = $equipment ? $equipment->getNumerotation() : 'N/A';
                $equipmentId = $equipment ? $equipment->getId() : 0;
                break;
            case 'desenfumage':
                $equipment = $inspection->getDesenfumage();
                $equipmentName = $equipment ? $equipment->getNumerotation() : 'N/A';
                $equipmentId = $equipment ? $equipment->getId() : 0;
                break;
            case 'issue_secours':
                $equipment = $inspection->getIssueSecours();
                $equipmentName = $equipment ? $equipment->getNumerotation() : 'N/A';
                $equipmentId = $equipment ? $equipment->getId() : 0;
                break;
            case 'prise_pompier':
                $equipment = $inspection->getPrisePompier();
                $equipmentName = $equipment ? 'Prise ' . $equipment->getId() : 'N/A';                $equipmentId = $equipment ? $equipment->getId() : 0;
                break;
        }

        $resetInspection->setEquipmentId($equipmentId);
        $resetInspection->setEquipmentName($equipmentName);

        // Construire les données de l'inspection en JSON
        // Note: InspectionMonteCharge utilise getInspecteur() au lieu de getInspectePar()
        $inspecteur = null;
        if (method_exists($inspection, 'getInspecteur')) {
            $inspecteur = $inspection->getInspecteur();
        } elseif (method_exists($inspection, 'getInspectePar')) {
            $inspecteur = $inspection->getInspectePar();
        }
        
        $inspectionData = [
            'date_inspection' => $inspection->getDateInspection()->format('Y-m-d H:i:s'),
            'inspected_by' => $inspecteur ? $inspecteur->getFullName() : 'N/A',
        ];
        
        // Ajouter le statut valide/conforme si disponible
        if (method_exists($inspection, 'isValide')) {
            $inspectionData['was_valid'] = $inspection->isValide();
        }
        
        // Ajouter les observations si disponibles
        if (method_exists($inspection, 'getObservations') && $inspection->getObservations()) {
            $inspectionData['observations'] = $inspection->getObservations();
        }

        $resetInspection->setInspectionData($inspectionData);
        $this->entityManager->persist($resetInspection);
    }

    /**
     * Vérifie si une réinitialisation a déjà été effectuée ce mois-ci
     */
    public function needsReset(string $equipmentType): bool
    {
        $firstDayOfMonth = new \DateTime('first day of this month 00:00:00');

        $count = $this->entityManager->getRepository(ResetInspection::class)
            ->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.equipmentType = :type')
            ->andWhere('r.resetDate >= :startDate')
            ->andWhere('r.resetType = :resetType')
            ->setParameter('type', $equipmentType)
            ->setParameter('startDate', $firstDayOfMonth)
            ->setParameter('resetType', 'automatic')
            ->getQuery()
            ->getSingleScalarResult();

        return $count === 0;
    }

    /**
     * Compte le nombre d'inspections actives pour un type d'équipement
     */
    public function countActiveInspections(string $equipmentType): int
    {
        switch ($equipmentType) {
            case 'extincteur':
                return $this->entityManager->getRepository(InspectionExtincteur::class)
                    ->count(['isActive' => true]);
            case 'sirene':
                return $this->entityManager->getRepository(InspectionSirene::class)
                    ->count(['isActive' => true]);
            case 'extinction_ram':
                return $this->entityManager->getRepository(InspectionExtinctionRAM::class)
                    ->count(['isActive' => true]);
            case 'monte_charge':
                return $this->entityManager->getRepository(InspectionMonteCharge::class)
                    ->count(['isActive' => true]);
            case 'ria':
                return $this->entityManager->getRepository(InspectionRIA::class)->count([]);
            case 'desenfumage':
                return $this->entityManager->getRepository(InspectionDesenfumage::class)->count([]);
            case 'issue_secours':
                return $this->entityManager->getRepository(InspectionIssueSecours::class)->count([]);
            case 'prise_pompier':
                return $this->entityManager->getRepository(InspectionPrisePompier::class)->count([]);
            default:
                return 0;
        }
    }
}
