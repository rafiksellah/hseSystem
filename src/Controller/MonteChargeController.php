<?php

namespace App\Controller;

use App\Entity\MonteCharge;
use App\Entity\InspectionMonteCharge;
use App\Entity\RapportHSE;
use App\Form\MonteChargeType;
use App\Form\InspectionMonteChargeType;
use App\Repository\MonteChargeRepository;
use App\Repository\InspectionMonteChargeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Service\ExcelExportService;
use App\Service\PdfExportService;
use App\Service\PaginationService;

#[Route('/monte-charge')]
#[IsGranted('ROLE_USER')]
class MonteChargeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MonteChargeRepository $monteChargeRepository,
        private InspectionMonteChargeRepository $inspectionRepository,
        private SluggerInterface $slugger,
        private ExcelExportService $excelExportService,
        private PdfExportService $pdfExportService,
        private PaginationService $paginationService
    ) {}

    #[Route('/', name: 'app_monte_charge_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $search = $request->query->get('search', '');
        $zone = $request->query->get('zone', '');
        $statut = $request->query->get('statut', '');

        // Récupérer les paramètres de pagination
        $paginationParams = $this->paginationService->getPaginationFromRequest($request->query->all());
        $page = $paginationParams['page'];
        $limit = $paginationParams['limit'];

        // Récupérer tous les monte-charges avec filtres
        $allMonteCharges = $this->monteChargeRepository->findWithFilters($search, $zone, $statut);

        // Utiliser le service de pagination
        $result = $this->paginationService->paginate($allMonteCharges, $page, $limit);
        $monteCharges = $result['items'];
        $pagination = $result['pagination'];

        return $this->render('monte_charge/index.html.twig', [
            'monte_charges' => $monteCharges,
            'pagination' => $pagination,
            'search' => $search,
            'zone' => $zone,
            'statut' => $statut,
        ]);
    }

    #[Route('/new', name: 'app_monte_charge_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request): Response
    {
        $monteCharge = new MonteCharge();
        $form = $this->createForm(MonteChargeType::class, $monteCharge);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si le numéro de monte-charge existe déjà
            $existingMonteCharge = $this->monteChargeRepository->findOneBy([
                'numeroMonteCharge' => $monteCharge->getNumeroMonteCharge()
            ]);

            if ($existingMonteCharge) {
                $this->addFlash('error', 'Un monte-charge avec ce numéro existe déjà. Veuillez choisir un autre numéro.');
                return $this->render('monte_charge/new.html.twig', [
                    'monte_charge' => $monteCharge,
                    'form' => $form,
                    'emplacements_simtis' => MonteCharge::EMPLACEMENTS_SIMTIS,
                    'emplacements_simtis_tissage' => MonteCharge::EMPLACEMENTS_TISSAGE,
                ]);
            }

            $this->entityManager->persist($monteCharge);
            $this->entityManager->flush();

            $this->addFlash('success', 'Monte-charge créé avec succès.');
            return $this->redirectToRoute('app_monte_charge_index');
        }

        return $this->render('monte_charge/new.html.twig', [
            'monte_charge' => $monteCharge,
            'form' => $form,
            'emplacements_simtis' => MonteCharge::EMPLACEMENTS_SIMTIS,
            'emplacements_simtis_tissage' => MonteCharge::EMPLACEMENTS_TISSAGE,
        ]);
    }

    #[Route('/{id}', name: 'app_monte_charge_show', methods: ['GET'])]
    public function show(MonteCharge $monteCharge): Response
    {
        $inspections = $this->inspectionRepository->findByMonteCharge($monteCharge->getId());

        return $this->render('monte_charge/show.html.twig', [
            'monte_charge' => $monteCharge,
            'inspections' => $inspections,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_monte_charge_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, MonteCharge $monteCharge): Response
    {
        $originalNumero = $monteCharge->getNumeroMonteCharge();
        $form = $this->createForm(MonteChargeType::class, $monteCharge);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si le numéro a été modifié et s'il existe déjà
            if ($monteCharge->getNumeroMonteCharge() !== $originalNumero) {
                $existingMonteCharge = $this->monteChargeRepository->findOneBy([
                    'numeroMonteCharge' => $monteCharge->getNumeroMonteCharge()
                ]);

                if ($existingMonteCharge && $existingMonteCharge->getId() !== $monteCharge->getId()) {
                    $this->addFlash('error', 'Un monte-charge avec ce numéro existe déjà. Veuillez choisir un autre numéro.');
                    return $this->render('monte_charge/edit.html.twig', [
                        'monte_charge' => $monteCharge,
                        'form' => $form,
                        'emplacements_simtis' => MonteCharge::EMPLACEMENTS_SIMTIS,
                        'emplacements_simtis_tissage' => MonteCharge::EMPLACEMENTS_TISSAGE,
                    ]);
                }
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Monte-charge modifié avec succès.');
            return $this->redirectToRoute('app_monte_charge_index');
        }

        return $this->render('monte_charge/edit.html.twig', [
            'monte_charge' => $monteCharge,
            'form' => $form,
            'emplacements_simtis' => MonteCharge::EMPLACEMENTS_SIMTIS,
            'emplacements_simtis_tissage' => MonteCharge::EMPLACEMENTS_TISSAGE,
        ]);
    }

    #[Route('/{id}', name: 'app_monte_charge_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, MonteCharge $monteCharge): Response
    {
        if ($this->isCsrfTokenValid('delete'.$monteCharge->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($monteCharge);
            $this->entityManager->flush();

            $this->addFlash('success', 'Monte-charge supprimé avec succès.');
        }

        return $this->redirectToRoute('app_monte_charge_index');
    }

    #[Route('/{id}/inspection/new', name: 'app_monte_charge_inspection_new', methods: ['GET', 'POST'])]
    public function newInspection(Request $request, MonteCharge $monteCharge): Response
    {
        if ($request->isMethod('POST')) {
            $inspection = new InspectionMonteCharge();
            $inspection->setMonteCharge($monteCharge);
            $inspection->setInspecteur($this->getUser());

            // Récupération des données du formulaire
            $data = $request->request->all('inspection_monte_charge');
            
            $inspection->setPortesFermees($data['portesFermees'] ?? null);
            $inspection->setConsignesRespectees($data['consignesRespectees'] ?? null);
            $inspection->setFinsCoursesFonctionnent($data['finsCoursesFonctionnent'] ?? null);
            $inspection->setEssaiVideRealise($data['essaiVideRealise'] ?? null);
            $inspection->setObservations($data['observations'] ?? null);

            // Gestion de l'upload de la photo
            $photoFile = $request->files->get('inspection_monte_charge')['photoObservation'] ?? null;
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads',
                        $newFilename
                    );
                    $inspection->setPhotoObservation($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de la photo.');
                }
            }

            $this->entityManager->persist($inspection);
            $this->entityManager->flush();

            $this->addFlash('success', 'Inspection enregistrée avec succès.');
            return $this->redirectToRoute('app_monte_charge_show', ['id' => $monteCharge->getId()]);
        }

        return $this->render('monte_charge/inspection_new.html.twig', [
            'monte_charge' => $monteCharge,
        ]);
    }

    #[Route('/inspection/{id}/edit', name: 'app_monte_charge_inspection_edit', methods: ['GET', 'POST'])]
    public function editInspection(Request $request, InspectionMonteCharge $inspection): Response
    {
        if ($request->isMethod('POST')) {
            // Récupération des données du formulaire
            $data = $request->request->all('inspection_monte_charge');
            
            $inspection->setPortesFermees($data['portesFermees'] ?? null);
            $inspection->setConsignesRespectees($data['consignesRespectees'] ?? null);
            $inspection->setFinsCoursesFonctionnent($data['finsCoursesFonctionnent'] ?? null);
            $inspection->setEssaiVideRealise($data['essaiVideRealise'] ?? null);
            $inspection->setObservations($data['observations'] ?? null);

            // Gestion de l'upload de la photo
            $photoFile = $request->files->get('inspection_monte_charge')['photoObservation'] ?? null;
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads',
                        $newFilename
                    );
                    $inspection->setPhotoObservation($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de la photo.');
                }
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Inspection modifiée avec succès.');
            return $this->redirectToRoute('app_monte_charge_show', ['id' => $inspection->getMonteCharge()->getId()]);
        }

        return $this->render('monte_charge/inspection_edit.html.twig', [
            'inspection' => $inspection,
            'monte_charge' => $inspection->getMonteCharge(),
        ]);
    }

    #[Route('/export/excel', name: 'app_monte_charge_export_excel', methods: ['GET'])]
    public function exportExcel(): Response
    {
        $monteCharges = $this->monteChargeRepository->findAllOrdered();
        
        $data = [];
        foreach ($monteCharges as $monteCharge) {
            $data[] = [
                'Numéro' => $monteCharge->getNumeroMonteCharge(),
                'Zone' => $monteCharge->getZone(),
                'Emplacement' => $monteCharge->getEmplacement(),
                'Porte' => $monteCharge->getNumeroPorte(),
                'Statut' => $monteCharge->getStatutConformite(),
                'Dernière Inspection' => $monteCharge->getDerniereInspection() ? 
                    $monteCharge->getDerniereInspection()->getDateInspection()->format('d/m/Y H:i') : 'Jamais inspecté',
                'Inspecteur' => $monteCharge->getDerniereInspection() ? 
                    $monteCharge->getDerniereInspection()->getInspecteur()->getNom() . ' ' . 
                    $monteCharge->getDerniereInspection()->getInspecteur()->getPrenom() : ''
            ];
        }

        return $this->excelExportService->export('Monte_Charges', $data);
    }

    #[Route('/export/pdf', name: 'app_monte_charge_export_pdf', methods: ['GET'])]
    public function exportPdf(): Response
    {
        $monteCharges = $this->monteChargeRepository->findAllOrdered();
        
        $html = $this->renderView('monte_charge/export_pdf.html.twig', [
            'monte_charges' => $monteCharges,
        ]);

        return $this->pdfExportService->export('Monte_Charges', $html);
    }
}
