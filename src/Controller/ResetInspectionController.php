<?php

namespace App\Controller;

use App\Entity\ResetInspection;
use App\Service\ResetInspectionService;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/reset-inspections')]
#[IsGranted('ROLE_SUPER_ADMIN')]
class ResetInspectionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ResetInspectionService $resetService,
        private PaginationService $paginationService
    ) {}

    #[Route('/', name: 'app_reset_inspection_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // Récupérer les paramètres de pagination
        $paginationParams = $this->paginationService->getPaginationFromRequest($request->query->all());
        $page = $paginationParams['page'];
        $limit = $paginationParams['limit'];

        // Récupérer toutes les réinitialisations
        $allResetInspections = $this->entityManager->getRepository(ResetInspection::class)
            ->findAll();

        // Utiliser le service de pagination
        $result = $this->paginationService->paginate($allResetInspections, $page, $limit);
        $resetInspections = $result['items'];
        $pagination = $result['pagination'];

        $statistics = $this->entityManager->getRepository(ResetInspection::class)
            ->getStatistics();

        return $this->render('admin/reset_inspection/index.html.twig', [
            'resetInspections' => $resetInspections,
            'pagination' => $pagination,
            'statistics' => $statistics,
        ]);
    }

    #[Route('/manual-reset', name: 'app_reset_inspection_manual', methods: ['POST'])]
    public function manualReset(Request $request): Response
    {
        $equipmentType = $request->request->get('equipment_type');
        $reason = $request->request->get('reason', 'Réinitialisation manuelle');

        if (!$equipmentType) {
            $this->addFlash('error', 'Type d\'équipement requis');
            return $this->redirectToRoute('app_reset_inspection_index');
        }

        try {
<<<<<<< HEAD
            // Si "all" est sélectionné, réinitialiser tous les équipements
=======
            // Si "all" est sélectionné, utiliser resetAllInspections
>>>>>>> 0ae0fcd2966c39ffb2310a5f9f5295022dc200be
            if ($equipmentType === 'all') {
                $allResults = $this->resetService->resetAllInspections(
                    'manual',
                    $this->getUser(),
                    $reason
                );
                
<<<<<<< HEAD
                $totalDeleted = 0;
                $allErrors = [];
                
                foreach ($allResults as $type => $results) {
                    $totalDeleted += $results['deleted'] ?? 0;
                    if (!empty($results['errors'])) {
                        $allErrors = array_merge($allErrors, $results['errors']);
                    }
                }
                
                $this->addFlash('success', sprintf(
                    'Réinitialisation de tous les équipements terminée: %d inspections supprimées',
                    $totalDeleted
                ));

                if (!empty($allErrors)) {
                    $this->addFlash('warning', 'Certaines erreurs ont été rencontrées: ' . implode(', ', $allErrors));
                }
            } else {
                // Réinitialisation d'un seul type
                $results = $this->resetService->resetInspectionsByType(
                    $equipmentType,
                    'manual',
                    $this->getUser(),
                    $reason
                );

                $this->addFlash('success', sprintf(
                    'Réinitialisation terminée: %d inspections supprimées',
                    $results['deleted']
                ));

                if (!empty($results['errors'])) {
                    $this->addFlash('warning', 'Certaines erreurs ont été rencontrées: ' . implode(', ', $results['errors']));
=======
                // Calculer les totaux
                $totalArchived = array_sum(array_column($allResults, 'archived'));
                $totalReset = array_sum(array_column($allResults, 'reset'));
                $allErrors = [];
                foreach ($allResults as $typeResults) {
                    $allErrors = array_merge($allErrors, $typeResults['errors']);
                }

                $this->addFlash('success', sprintf(
                    'Réinitialisation de tous les équipements terminée: %d archivées, %d réinitialisées',
                    $totalArchived,
                    $totalReset
                ));

                if (!empty($allErrors)) {
                    $this->addFlash('warning', 'Certaines erreurs ont été rencontrées: ' . implode(', ', $allErrors));
                }
            } else {
                // Réinitialisation d'un seul type
                $results = $this->resetService->resetInspectionsByType(
                    $equipmentType,
                    'manual',
                    $this->getUser(),
                    $reason
                );

                $this->addFlash('success', sprintf(
                    'Réinitialisation terminée: %d archivées, %d réinitialisées',
                    $results['archived'],
                    $results['reset']
                ));

                if (!empty($results['errors'])) {
                    $this->addFlash('warning', 'Certaines erreurs ont été rencontrées');
>>>>>>> 0ae0fcd2966c39ffb2310a5f9f5295022dc200be
                }
            }

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la réinitialisation: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_reset_inspection_index');
    }

    #[Route('/archive/{id}', name: 'app_reset_inspection_archive', methods: ['GET'])]
    public function showArchive(ResetInspection $resetInspection): Response
    {
        return $this->render('admin/reset_inspection/archive.html.twig', [
            'resetInspection' => $resetInspection,
        ]);
    }

    #[Route('/statistics', name: 'app_reset_inspection_statistics', methods: ['GET'])]
    public function statistics(): Response
    {
        $statistics = $this->entityManager->getRepository(ResetInspection::class)
            ->getStatistics();

        $byType = $this->entityManager->getRepository(ResetInspection::class)
            ->createQueryBuilder('r')
            ->select('r.equipmentType, r.resetType, COUNT(r.id) as count')
            ->groupBy('r.equipmentType, r.resetType')
            ->getQuery()
            ->getResult();

        $byDate = $this->entityManager->getRepository(ResetInspection::class)
            ->createQueryBuilder('r')
            ->select('r.resetDate as date, COUNT(r.id) as count')
            ->groupBy('r.resetDate')
            ->orderBy('r.resetDate', 'DESC')
            ->setMaxResults(30)
            ->getQuery()
            ->getResult();

        return $this->render('admin/reset_inspection/statistics.html.twig', [
            'statistics' => $statistics,
            'byType' => $byType,
            'byDate' => $byDate,
        ]);
    }
}
