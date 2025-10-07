<?php

namespace App\Service;

use App\Entity\ResetInspection;
use App\Entity\InspectionExtincteur;
use App\Entity\InspectionSirene;
use App\Entity\InspectionExtinctionRAM;
use App\Entity\InspectionMonteCharge;
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
            'archived' => 0,
            'reset' => 0,
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
        $equipmentTypes = ['extincteur', 'sirene', 'extinction_ram', 'monte_charge'];

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
        $results = ['archived' => 0, 'reset' => 0, 'errors' => []];

        // Récupérer toutes les inspections actives
        $inspections = $this->entityManager->getRepository(InspectionExtincteur::class)
            ->createQueryBuilder('i')
            ->where('i.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();

        foreach ($inspections as $inspection) {
            try {
                // Archiver l'inspection
                $this->archiveInspection($inspection, 'extincteur', $resetType, $resetBy, $reason);
                $results['archived']++;

                // Marquer comme inactive
                $inspection->setIsActive(false);
                $inspection->setResetDate(new \DateTime());
                $inspection->setResetReason($reason);
                $results['reset']++;

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
        $results = ['archived' => 0, 'reset' => 0, 'errors' => []];

        $inspections = $this->entityManager->getRepository(InspectionSirene::class)
            ->createQueryBuilder('i')
            ->where('i.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();

        foreach ($inspections as $inspection) {
            try {
                $this->archiveInspection($inspection, 'sirene', $resetType, $resetBy, $reason);
                $results['archived']++;

                $inspection->setIsActive(false);
                $inspection->setResetDate(new \DateTime());
                $inspection->setResetReason($reason);
                $results['reset']++;

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
        $results = ['archived' => 0, 'reset' => 0, 'errors' => []];

        $inspections = $this->entityManager->getRepository(InspectionExtinctionRAM::class)
            ->createQueryBuilder('i')
            ->where('i.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();

        foreach ($inspections as $inspection) {
            try {
                $this->archiveInspection($inspection, 'extinction_ram', $resetType, $resetBy, $reason);
                $results['archived']++;

                $inspection->setIsActive(false);
                $inspection->setResetDate(new \DateTime());
                $inspection->setResetReason($reason);
                $results['reset']++;

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
        $results = ['archived' => 0, 'reset' => 0, 'errors' => []];

        $inspections = $this->entityManager->getRepository(InspectionMonteCharge::class)
            ->createQueryBuilder('i')
            ->where('i.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();

        foreach ($inspections as $inspection) {
            try {
                $this->archiveInspection($inspection, 'monte_charge', $resetType, $resetBy, $reason);
                $results['archived']++;

                $inspection->setIsActive(false);
                $inspection->setResetDate(new \DateTime());
                $inspection->setResetReason($reason);
                $results['reset']++;

            } catch (\Exception $e) {
                $results['errors'][] = "Erreur inspection ID {$inspection->getId()}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Archive une inspection dans ResetInspection
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
        }

        $resetInspection->setEquipmentId($equipmentId);
        $resetInspection->setEquipmentName($equipmentName);

        // Sérialiser les données de l'inspection
        $inspectionData = $this->serializeInspectionData($inspection);
        $resetInspection->setInspectionData($inspectionData);

        $this->entityManager->persist($resetInspection);
    }

    /**
     * Sérialise les données d'une inspection pour l'archivage
     */
    private function serializeInspectionData($inspection): array
    {
        $data = [
            'id' => $inspection->getId(),
            'dateInspection' => $inspection->getDateInspection()?->format('Y-m-d H:i:s'),
            'inspectePar' => $inspection->getInspectePar()?->getEmail() ?? $inspection->getInspecteur()?->getEmail(),
            'observations' => $inspection->getObservations(),
            'photoObservation' => $inspection->getPhotoObservation(),
        ];

        // Ajouter les données spécifiques selon le type
        if ($inspection instanceof InspectionExtincteur) {
            $data['criteres'] = $inspection->getCriteres();
            $data['valide'] = $inspection->isValide();
        } elseif ($inspection instanceof InspectionSirene || $inspection instanceof InspectionExtinctionRAM) {
            $data['conformite'] = $inspection->getConformite();
            $data['valide'] = $inspection->isValide();
        } elseif ($inspection instanceof InspectionMonteCharge) {
            $data['portesFermees'] = $inspection->getPortesFermees();
            $data['consignesRespectees'] = $inspection->getConsignesRespectees();
            $data['finsCoursesFonctionnent'] = $inspection->getFinsCoursesFonctionnent();
            $data['essaiVideRealise'] = $inspection->getEssaiVideRealise();
        }

        return $data;
    }

    /**
     * Vérifie si une réinitialisation est nécessaire
     */
    public function needsReset(string $equipmentType): bool
    {
        $now = new \DateTime();
        
        // Pour monte-charge : réinitialisation quotidienne
        if ($equipmentType === 'monte_charge') {
            $lastReset = $this->entityManager->getRepository(ResetInspection::class)
                ->createQueryBuilder('r')
                ->where('r.equipmentType = :type')
                ->andWhere('r.resetType = :resetType')
                ->andWhere('r.resetDate >= :yesterday')
                ->setParameter('type', $equipmentType)
                ->setParameter('resetType', 'daily')
                ->setParameter('yesterday', $now->modify('-1 day'))
                ->getQuery()
                ->getOneOrNullResult();

            return $lastReset === null;
        }
        
        // Pour autres équipements : réinitialisation mensuelle
        $lastReset = $this->entityManager->getRepository(ResetInspection::class)
            ->createQueryBuilder('r')
            ->where('r.equipmentType = :type')
            ->andWhere('r.resetType = :resetType')
            ->andWhere('r.resetDate >= :lastMonth')
            ->setParameter('type', $equipmentType)
            ->setParameter('resetType', 'monthly')
            ->setParameter('lastMonth', $now->modify('-1 month'))
            ->getQuery()
            ->getOneOrNullResult();

        return $lastReset === null;
    }
}
