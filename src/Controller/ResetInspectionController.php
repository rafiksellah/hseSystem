<?php

namespace App\Controller;

use App\Entity\ResetInspection;
use App\Service\ResetInspectionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/reset-inspections')]
#[IsGranted('ROLE_ADMIN')]
class ResetInspectionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ResetInspectionService $resetService
    ) {}

    #[Route('/', name: 'app_reset_inspection_index', methods: ['GET'])]
    public function index(): Response
    {
        $resetInspections = $this->entityManager->getRepository(ResetInspection::class)
            ->findRecent(50);

        $statistics = $this->entityManager->getRepository(ResetInspection::class)
            ->getStatistics();

        return $this->render('admin/reset_inspection/index.html.twig', [
            'resetInspections' => $resetInspections,
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
