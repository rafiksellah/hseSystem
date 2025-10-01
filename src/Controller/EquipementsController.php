<?php

namespace App\Controller;

use App\Entity\Extincteur;
use App\Entity\InspectionExtincteur;
use App\Entity\RIA;
use App\Entity\MonteCharge;
use App\Entity\InspectionMonteCharge;
use App\Entity\User;
use App\Entity\RapportHSE;
use App\Repository\ExtincteurRepository;
use App\Repository\RIARepository;
use App\Repository\MonteChargeRepository;
use App\Repository\InspectionExtincteurRepository;
use App\Repository\InspectionMonteChargeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/equipements')]
#[IsGranted('ROLE_USER')]
class EquipementsController extends AbstractController
{
    #[Route('/', name: 'app_equipements_dashboard')]
    public function dashboard(
        ExtincteurRepository $extincteurRepository,
        RIARepository $riaRepository,
        MonteChargeRepository $monteChargeRepository
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        // Statistiques générales
        $statsExtincteurs = $extincteurRepository->getStatistiquesParZone();
        $statsRIA = $riaRepository->getStatistiquesParZone();
        $statsMonteCharge = $monteChargeRepository->getStatistiques();

        // Filtrer par zone pour les non-super-admins
        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $userZone = $user->getZone();
            $statsExtincteurs = array_filter($statsExtincteurs, fn($stat) => $stat['zone'] === $userZone);
            $statsRIA = array_filter($statsRIA, fn($stat) => $stat['zone'] === $userZone);
        }

        return $this->render('equipements/dashboard.html.twig', [
            'stats_extincteurs' => $statsExtincteurs,
            'stats_ria' => $statsRIA,
            'stats_monte_charge' => $statsMonteCharge,
            'user_zone' => $user->getZone(),
            'is_super_admin' => in_array('ROLE_SUPER_ADMIN', $user->getRoles()),
        ]);
    }

    // =============== EXTINCTEURS - ÉTAT ===============

    #[Route('/extincteurs/etat', name: 'app_equipements_extincteurs_etat')]
    public function etatExtincteurs(
        ExtincteurRepository $extincteurRepository,
        Request $request
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $page = $request->query->getInt('page', 1);
        $limit = 50; // Plus de résultats pour la vue État

        $searchParams = [
            'zone' => '',
            'emplacement' => $request->query->get('emplacement', ''),
            'numerotation' => $request->query->get('numerotation', ''),
            'valide' => $request->query->get('valide', '')
        ];

        // Filtrer par zone pour les non-super-admins (pour les permissions)
        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $searchParams['zone'] = $user->getZone();
        }

        $extincteurs = $extincteurRepository->searchExtincteurs(
            $searchParams,
            $limit,
            ($page - 1) * $limit
        );

        $totalExtincteurs = $extincteurRepository->countSearchExtincteurs($searchParams);
        $totalPages = ceil($totalExtincteurs / $limit);

        // Emplacements disponibles selon la zone de l'utilisateur
        $emplacementsDisponibles = [];
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $emplacementsDisponibles = array_merge(
                RapportHSE::ZONES_SIMTIS,
                RapportHSE::ZONES_SIMTIS_TISSAGE
            );
        } else {
            $emplacementsDisponibles = RapportHSE::getZonesForUserZone($user->getZone());
        }

        return $this->render('equipements/extincteurs/etat.html.twig', [
            'extincteurs' => $extincteurs,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'search_params' => $searchParams,
            'emplacements_disponibles' => $emplacementsDisponibles,
            'user_zone' => $user->getZone(),
            'is_admin' => in_array('ROLE_ADMIN', $user->getRoles()),
        ]);
    }

    #[Route('/extincteurs', name: 'app_equipements_extincteurs')]
    public function extincteurs(
        ExtincteurRepository $extincteurRepository,
        Request $request
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $page = $request->query->getInt('page', 1);
        $limit = 20;

        $searchParams = [
            'zone' => '',
            'emplacement' => $request->query->get('emplacement', ''),
            'numerotation' => $request->query->get('numerotation', ''),
            'valide' => $request->query->get('valide', '')
        ];

        // Filtrer par zone pour les non-super-admins (pour les permissions)
        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $searchParams['zone'] = $user->getZone();
        }

        $extincteurs = $extincteurRepository->searchExtincteurs(
            $searchParams,
            $limit,
            ($page - 1) * $limit
        );

        $totalExtincteurs = $extincteurRepository->countSearchExtincteurs($searchParams);
        $totalPages = ceil($totalExtincteurs / $limit);

        // Emplacements disponibles selon la zone de l'utilisateur
        $emplacementsDisponibles = [];
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            // Super admin voit tous les emplacements (SIMTIS + TISSAGE)
            $emplacementsDisponibles = array_merge(
                RapportHSE::ZONES_SIMTIS,
                RapportHSE::ZONES_SIMTIS_TISSAGE
            );
        } else {
            // Admin voit seulement les emplacements de sa zone
            $emplacementsDisponibles = RapportHSE::getZonesForUserZone($user->getZone());
        }

        return $this->render('equipements/extincteurs/liste.html.twig', [
            'extincteurs' => $extincteurs,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'search_params' => $searchParams,
            'emplacements_disponibles' => $emplacementsDisponibles,
            'user_zone' => $user->getZone(),
            'is_admin' => in_array('ROLE_ADMIN', $user->getRoles()),
        ]);
    }

    #[Route('/extincteurs/nouveau', name: 'app_equipements_extincteurs_nouveau')]
    #[IsGranted('ROLE_ADMIN')]
    public function nouveauExtincteur(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $extincteur = new Extincteur();
        /** @var User $user */
        $user = $this->getUser();

        // Pré-remplir la zone si l'utilisateur n'est pas super admin
        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $extincteur->setZone($user->getZone());
        }

        if ($request->isMethod('POST')) {
            $extincteur->setNumerotation($request->request->get('numerotation'));
            $extincteur->setZone($request->request->get('zone'));
            $extincteur->setEmplacement($request->request->get('emplacement'));
            $extincteur->setAgentExtincteur($request->request->get('agent_extincteur'));
            $extincteur->setType($request->request->get('type'));
            $extincteur->setCapacite($request->request->get('capacite'));
            $extincteur->setNumeroSerie($request->request->get('numero_serie'));

            // Dates
            if ($request->request->get('date_fabrication')) {
                $extincteur->setDateFabrication(new \DateTime($request->request->get('date_fabrication')));
            }
            if ($request->request->get('date_epreuve')) {
                $extincteur->setDateEpreuve(new \DateTime($request->request->get('date_epreuve')));
            }
            if ($request->request->get('date_fin_vie')) {
                $extincteur->setDateFinDeVie(new \DateTime($request->request->get('date_fin_vie')));
            }

            $entityManager->persist($extincteur);
            $entityManager->flush();

            $this->addFlash('success', 'Extincteur ajouté avec succès !');
            return $this->redirectToRoute('app_equipements_extincteurs');
        }

        $zonesDisponibles = [];
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $zonesDisponibles = User::ZONES_DISPONIBLES;
        } else {
            $zonesDisponibles = [$user->getZone() => $user->getZone()];
        }
        return $this->render('equipements/extincteurs/nouveau.html.twig', [
            'extincteur' => $extincteur,
            'zones_disponibles' => Extincteur::getZonesForUser($user),
            'agents_disponibles' => Extincteur::AGENTS_EXTINCTEUR,
            'types_disponibles' => Extincteur::TYPES_DISPONIBLES,
            'capacites_disponibles' => Extincteur::CAPACITES_DISPONIBLES,
            'emplacements_simtis' => RapportHSE::ZONES_SIMTIS,
            'emplacements_simtis_tissage' => RapportHSE::ZONES_SIMTIS_TISSAGE,
        ]);
    }

    #[Route('/extincteurs/{id}/valider', name: 'app_equipements_extincteurs_valider')]
    public function validerExtincteur(
        Extincteur $extincteur,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        // Vérifier les permissions
        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles()) && $extincteur->getZone() !== $user->getZone()) {
            throw $this->createAccessDeniedException('Accès non autorisé à cet extincteur');
        }

        if ($extincteur->isValide()) {
            $this->addFlash('error', 'Cet extincteur a déjà été validé');
            return $this->redirectToRoute('app_equipements_extincteurs');
        }

        if ($request->isMethod('POST')) {
            $existe = $request->request->get('existe') === 'oui';

            $extincteur->setValide(true);
            $extincteur->setDateValidation(new \DateTime());
            $extincteur->setValidePar($user);

            if (!$existe) {
                // Si l'extincteur n'existe pas, on peut ajouter une note ou un statut
                // Pour l'instant, on le marque comme validé mais non existant
            }

            $entityManager->flush();

            $message = $existe ? 'Extincteur validé comme existant' : 'Extincteur marqué comme non existant';
            $this->addFlash('success', $message);
            return $this->redirectToRoute('app_equipements_extincteurs');
        }

        return $this->render('equipements/extincteurs/valider.html.twig', [
            'extincteur' => $extincteur,
        ]);
    }

    // =============== INSPECTION MLCI ===============

    #[Route('/extincteurs/inspections', name: 'app_equipements_inspections')]
    public function inspections(
        ExtincteurRepository $extincteurRepository,
        InspectionExtincteurRepository $inspectionRepository
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        // Obtenir les extincteurs disponibles pour inspection
        $extincteurs = [];
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $extincteurs = $extincteurRepository->findAll();
        } else {
            $extincteurs = $extincteurRepository->findBy(['zone' => $user->getZone()]);
        }

        // Obtenir les dernières inspections
        $inspections = $inspectionRepository->getInspectionsAvecDetails();

        return $this->render('equipements/extincteurs/inspections.html.twig', [
            'extincteurs' => $extincteurs,
            'inspections' => $inspections,
            'user_zone' => $user->getZone(),
        ]);
    }

    #[Route('/extincteurs/{id}/inspecter', name: 'app_equipements_extincteurs_inspecter')]
    public function inspecterExtincteur(
        Extincteur $extincteur,
        EntityManagerInterface $entityManager,
        InspectionExtincteurRepository $inspectionRepository,
        Request $request,
        SluggerInterface $slugger
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        // Vérifier les permissions
        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles()) && $extincteur->getZone() !== $user->getZone()) {
            throw $this->createAccessDeniedException('Accès non autorisé à cet extincteur');
        }

        // Vérifier s'il y a déjà une inspection récente (dans les 24h)
        $derniereInspection = $inspectionRepository->getDerniereInspection($extincteur->getId());
        if ($derniereInspection && $derniereInspection->getDateInspection() > new \DateTime('-1 day')) {
            $this->addFlash('error', 'Cet extincteur a déjà été inspecté récemment');
            return $this->redirectToRoute('app_equipements_inspections');
        }

        if ($request->isMethod('POST')) {
            $inspection = new InspectionExtincteur();
            $inspection->setExtincteur($extincteur);
            $inspection->setInspectePar($user);

            // Récupérer les réponses aux critères
            $criteres = [];
            foreach (InspectionExtincteur::CRITERES as $key => $label) {
                $criteres[$key] = $request->request->get('critere_' . $key) === 'oui';
            }

            $inspection->setCriteres($criteres);
            $inspection->setObservations($request->request->get('observations'));

            // Valider si tous les critères sont OK
            $tousValides = !in_array(false, $criteres, true);
            $inspection->setValide($tousValides);

            // Gérer l'upload de photo si présent
            $photoFile = $request->files->get('photo_observation');
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('photos_directory'),
                        $newFilename
                    );
                    $inspection->setPhotoObservation($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de la photo');
                }
            }

            $entityManager->persist($inspection);
            $entityManager->flush();

            $this->addFlash('success', 'Inspection enregistrée avec succès !');
            return $this->redirectToRoute('app_equipements_inspections');
        }

        return $this->render('equipements/extincteurs/inspecter.html.twig', [
            'extincteur' => $extincteur,
            'criteres' => InspectionExtincteur::CRITERES,
        ]);
    }

    // =============== RIA - ÉTAT ===============

    #[Route('/ria', name: 'app_equipements_ria')]
    public function ria(
        RIARepository $riaRepository,
        Request $request
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $page = $request->query->getInt('page', 1);
        $limit = 20;

        $searchParams = [
            'zone' => $request->query->get('zone', ''),
            'numerotation' => $request->query->get('numerotation', ''),
            'valide' => $request->query->get('valide', '')
        ];

        $rias = $riaRepository->searchRIA(
            $searchParams,
            $limit,
            ($page - 1) * $limit
        );

        $totalRIAs = $riaRepository->countSearchRIA($searchParams);
        $totalPages = ceil($totalRIAs / $limit);

        // Les zones RIA sont fixes (pas liées à SIMTIS/TISSAGE)
        $zonesDisponibles = RIA::ZONES_RIA;

        return $this->render('equipements/ria/liste.html.twig', [
            'rias' => $rias,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'search_params' => $searchParams,
            'zones_disponibles' => $zonesDisponibles,
            'user_zone' => $user->getZone(),
            'is_admin' => in_array('ROLE_ADMIN', $user->getRoles()),
        ]);
    }

    #[Route('/ria/nouveau', name: 'app_equipements_ria_nouveau')]
    #[IsGranted('ROLE_ADMIN')]
    public function nouveauRIA(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $ria = new RIA();
        /** @var User $user */
        $user = $this->getUser();

        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $ria->setZone($user->getZone());
        }

        if ($request->isMethod('POST')) {
            $ria->setNumerotation($request->request->get('numerotation'));
            $ria->setZone($request->request->get('zone'));
            $ria->setAgentExtincteur($request->request->get('agent_extincteur'));
            $ria->setDimatere($request->request->get('dimatere') ? (int)$request->request->get('dimatere') : null);
            $ria->setLongueur($request->request->get('longueur') ? (int)$request->request->get('longueur') : null);

            $entityManager->persist($ria);
            $entityManager->flush();

            $this->addFlash('success', 'RIA ajouté avec succès !');
            return $this->redirectToRoute('app_equipements_ria');
        }

        $zonesDisponibles = [];
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $zonesDisponibles = User::ZONES_DISPONIBLES;
        } else {
            $zonesDisponibles = [$user->getZone() => $user->getZone()];
        }

        return $this->render('equipements/ria/nouveau.html.twig', [
            'ria' => $ria,
            'zones_disponibles' => RIA::ZONES_RIA,
            'agents_disponibles' => RIA::AGENTS_DISPONIBLES,
        ]);
    }

    #[Route('/ria/{id}/details', name: 'app_equipements_ria_details')]
    public function detailsRIA(RIA $ria): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Vérifier les permissions (optionnel pour RIA)
        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles()) && $ria->getZone() !== $user->getZone()) {
            // Pour RIA, on peut autoriser tout le monde à voir
            // throw $this->createAccessDeniedException('Accès non autorisé à ce RIA');
        }

        return $this->render('equipements/ria/details.html.twig', [
            'ria' => $ria,
        ]);
    }

    #[Route('/ria/{id}/valider', name: 'app_equipements_ria_valider')]
    public function validerRIA(
        RIA $ria,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles()) && $ria->getZone() !== $user->getZone()) {
            throw $this->createAccessDeniedException('Accès non autorisé à ce RIA');
        }

        if ($ria->isValide()) {
            $this->addFlash('error', 'Ce RIA a déjà été validé');
            return $this->redirectToRoute('app_equipements_ria');
        }

        if ($request->isMethod('POST')) {
            $existe = $request->request->get('existe') === 'oui';

            $ria->setValide(true);
            $ria->setDateValidation(new \DateTime());
            $ria->setValidePar($user);

            $entityManager->flush();

            $message = $existe ? 'RIA validé comme existant' : 'RIA marqué comme non existant';
            $this->addFlash('success', $message);
            return $this->redirectToRoute('app_equipements_ria');
        }

        return $this->render('equipements/ria/valider.html.twig', [
            'ria' => $ria,
        ]);
    }

    // =============== MONTE-CHARGE TEFIL ===============

    #[Route('/monte-charge', name: 'app_equipements_monte_charge')]
    public function monteCharge(
        MonteChargeRepository $monteChargeRepository,
        InspectionMonteChargeRepository $inspectionRepository
    ): Response {
        $monteCharges = $monteChargeRepository->getMonteChargesAvecInspections();
        $inspections = $inspectionRepository->getInspectionsAvecDetails();

        return $this->render('equipements/monte_charge/liste.html.twig', [
            'monte_charges' => $monteCharges,
            'inspections' => $inspections,
            'portes' => InspectionMonteCharge::PORTES,
        ]);
    }

    #[Route('/monte-charge/{id}/inspecter/{porte}', name: 'app_equipements_monte_charge_inspecter')]
    public function inspecterPorteMonteCharge(
        MonteCharge $monteCharge,
        string $porte,
        Request $request,
        EntityManagerInterface $entityManager,
        InspectionMonteChargeRepository $inspectionRepository,
        SluggerInterface $slugger
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        // Vérifier si la porte est valide pour ce monte-charge
        $portesDisponibles = MonteCharge::getPortesForType($monteCharge->getType());
        if (!array_key_exists($porte, $portesDisponibles)) {
            throw $this->createNotFoundException('Porte non trouvée pour ce monte-charge');
        }

        // Vérifier s'il y a déjà une inspection récente (dans les 24h)
        $derniereInspection = $inspectionRepository->findOneBy([
            'monteCharge' => $monteCharge,
            'numeroPorte' => $porte
        ], ['dateInspection' => 'DESC']);

        if ($derniereInspection && $derniereInspection->getDateInspection() > new \DateTime('-1 day')) {
            $this->addFlash('error', 'Cette porte a déjà été inspectée récemment');
            return $this->redirectToRoute('app_equipements_monte_charge');
        }

        if ($request->isMethod('POST')) {
            $inspection = new InspectionMonteCharge();
            $inspection->setMonteCharge($monteCharge);
            $inspection->setNumeroPorte($porte);
            $inspection->setInspectePar($user);

            // Récupérer les réponses aux questions
            $reponses = [];
            foreach (InspectionMonteCharge::QUESTIONS as $key => $question) {
                $reponses[$key] = $request->request->get('question_' . $key) === 'oui';
            }

            $inspection->setReponses($reponses);
            $inspection->setObservations($request->request->get('observations'));

            // Valider si toutes les questions sont OK
            $toutesValides = !in_array(false, $reponses, true);
            $inspection->setValide($toutesValides);

            // Gérer l'upload de photo si présent
            $photoFile = $request->files->get('photo_observation');
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('photos_directory'), // À définir dans services.yaml
                        $newFilename
                    );
                    $inspection->setPhotoObservation($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de la photo');
                }
            }

            $entityManager->persist($inspection);
            $entityManager->flush();

            $this->addFlash('success', 'Inspection enregistrée avec succès !');
            return $this->redirectToRoute('app_equipements_monte_charge');
        }

        return $this->render('equipements/monte_charge/inspecter.html.twig', [
            'monte_charge' => $monteCharge,
            'porte' => $porte,
            'porte_nom' => InspectionMonteCharge::PORTES[$porte],
            'questions' => InspectionMonteCharge::QUESTIONS,
        ]);
    }

    #[Route('/extincteurs/{id}/modifier', name: 'app_equipements_extincteurs_modifier')]
    #[IsGranted('ROLE_ADMIN')]
    public function modifierExtincteur(
        Extincteur $extincteur,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        // Vérifier les permissions
        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles()) && $extincteur->getZone() !== $user->getZone()) {
            throw $this->createAccessDeniedException('Accès non autorisé à cet extincteur');
        }

        if ($request->isMethod('POST')) {
            $extincteur->setNumerotation($request->request->get('numerotation'));
            $extincteur->setZone($request->request->get('zone'));
            $extincteur->setEmplacement($request->request->get('emplacement'));
            $extincteur->setAgentExtincteur($request->request->get('agent_extincteur'));
            $extincteur->setType($request->request->get('type'));
            $extincteur->setCapacite($request->request->get('capacite'));
            $extincteur->setNumeroSerie($request->request->get('numero_serie'));

            // Dates
            if ($request->request->get('date_fabrication')) {
                $extincteur->setDateFabrication(new \DateTime($request->request->get('date_fabrication')));
            }
            if ($request->request->get('date_epreuve')) {
                $extincteur->setDateEpreuve(new \DateTime($request->request->get('date_epreuve')));
            }
            if ($request->request->get('date_fin_vie')) {
                $extincteur->setDateFinDeVie(new \DateTime($request->request->get('date_fin_vie')));
            }

            $entityManager->flush();

            $this->addFlash('success', 'Extincteur modifié avec succès !');
            return $this->redirectToRoute('app_equipements_extincteurs');
        }

        $zonesDisponibles = [];
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $zonesDisponibles = User::ZONES_DISPONIBLES;
        } else {
            $zonesDisponibles = [$user->getZone() => $user->getZone()];
        }

        return $this->render('equipements/extincteurs/modifier.html.twig', [
            'extincteur' => $extincteur,
            'zones_disponibles' => $zonesDisponibles,
            'agents_disponibles' => Extincteur::AGENTS_EXTINCTEUR,
            'types_disponibles' => Extincteur::TYPES_DISPONIBLES,
            'capacites_disponibles' => Extincteur::CAPACITES_DISPONIBLES,
            'emplacements_simtis' => RapportHSE::ZONES_SIMTIS,
            'emplacements_simtis_tissage' => RapportHSE::ZONES_SIMTIS_TISSAGE,
        ]);
    }

    #[Route('/extincteurs/{id}/supprimer', name: 'app_equipements_extincteurs_supprimer')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function supprimerExtincteur(
        Extincteur $extincteur,
        EntityManagerInterface $entityManager
    ): Response {
        try {
            $numerotation = $extincteur->getNumerotation();
            $zone = $extincteur->getZone();

            $entityManager->remove($extincteur);
            $entityManager->flush();

            $this->addFlash('success', 'L\'extincteur ' . $numerotation . ' (' . $zone . ') a été supprimé avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_equipements_extincteurs');
    }

    #[Route('/ria/{id}/modifier', name: 'app_equipements_ria_modifier')]
    #[IsGranted('ROLE_ADMIN')]
    public function modifierRIA(
        RIA $ria,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles()) && $ria->getZone() !== $user->getZone()) {
            throw $this->createAccessDeniedException('Accès non autorisé à ce RIA');
        }

        if ($request->isMethod('POST')) {
            $ria->setNumerotation($request->request->get('numerotation'));
            $ria->setZone($request->request->get('zone'));
            $ria->setAgentExtincteur($request->request->get('agent_extincteur'));
            $ria->setDimatere($request->request->get('dimatere') ? (int)$request->request->get('dimatere') : null);
            $ria->setLongueur($request->request->get('longueur') ? (int)$request->request->get('longueur') : null);

            $entityManager->flush();

            $this->addFlash('success', 'RIA modifié avec succès !');
            return $this->redirectToRoute('app_equipements_ria');
        }

        $zonesDisponibles = [];
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $zonesDisponibles = User::ZONES_DISPONIBLES;
        } else {
            $zonesDisponibles = [$user->getZone() => $user->getZone()];
        }

        return $this->render('equipements/ria/modifier.html.twig', [
            'ria' => $ria,
            'zones_disponibles' => RIA::ZONES_RIA,
            'agents_disponibles' => RIA::AGENTS_DISPONIBLES,
        ]);
    }

    #[Route('/ria/{id}/supprimer', name: 'app_equipements_ria_supprimer')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function supprimerRIA(
        RIA $ria,
        EntityManagerInterface $entityManager
    ): Response {
        try {
            $numerotation = $ria->getNumerotation();
            $zone = $ria->getZone();

            $entityManager->remove($ria);
            $entityManager->flush();

            $this->addFlash('success', 'Le RIA ' . $numerotation . ' (' . $zone . ') a été supprimé avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_equipements_ria');
    }

    #[Route('/monte-charge/nouveau', name: 'app_equipements_monte_charge_nouveau')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function nouveauMonteCharge(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $monteCharge = new MonteCharge();

        if ($request->isMethod('POST')) {
            $monteCharge->setType($request->request->get('type'));
            $monteCharge->setZone($request->request->get('zone'));

            $entityManager->persist($monteCharge);
            $entityManager->flush();

            $this->addFlash('success', 'Monte-charge ajouté avec succès !');
            return $this->redirectToRoute('app_equipements_monte_charge');
        }

        return $this->render('equipements/monte_charge/nouveau.html.twig', [
            'monte_charge' => $monteCharge,
            'types_disponibles' => MonteCharge::TYPES,
            'zones_disponibles' => MonteCharge::ZONES,
        ]);
    }

    #[Route('/monte-charge/{id}/modifier', name: 'app_equipements_monte_charge_modifier')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function modifierMonteCharge(
        MonteCharge $monteCharge,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if ($request->isMethod('POST')) {
            $monteCharge->setType($request->request->get('type'));
            $monteCharge->setZone($request->request->get('zone'));

            $entityManager->flush();

            $this->addFlash('success', 'Monte-charge modifié avec succès !');
            return $this->redirectToRoute('app_equipements_monte_charge');
        }

        return $this->render('equipements/monte_charge/modifier.html.twig', [
            'monte_charge' => $monteCharge,
            'types_disponibles' => MonteCharge::TYPES,
            'zones_disponibles' => MonteCharge::ZONES,
        ]);
    }

    #[Route('/monte-charge/{id}/supprimer', name: 'app_equipements_monte_charge_supprimer')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function supprimerMonteCharge(
        MonteCharge $monteCharge,
        EntityManagerInterface $entityManager
    ): Response {
        try {
            $type = $monteCharge->getType();

            $entityManager->remove($monteCharge);
            $entityManager->flush();

            $this->addFlash('success', 'Le monte-charge ' . $type . ' a été supprimé avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_equipements_monte_charge');
    }

    #[Route('/inspection/{id}/modifier', name: 'app_equipements_inspection_modifier')]
    #[IsGranted('ROLE_ADMIN')]
    public function modifierInspection(
        InspectionExtincteur $inspection,
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        // Vérifier les permissions
        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles()) && 
            $inspection->getExtincteur()->getZone() !== $user->getZone()) {
            throw $this->createAccessDeniedException('Accès non autorisé à cette inspection');
        }

        if ($request->isMethod('POST')) {
            // Récupérer les réponses aux critères
            $criteres = [];
            foreach (InspectionExtincteur::CRITERES as $key => $label) {
                $criteres[$key] = $request->request->get('critere_' . $key) === 'oui';
            }

            $inspection->setCriteres($criteres);
            $inspection->setObservations($request->request->get('observations'));

            // Valider si tous les critères sont OK
            $tousValides = !in_array(false, $criteres, true);
            $inspection->setValide($tousValides);

            // Gérer l'upload de nouvelle photo
            $photoFile = $request->files->get('photo_observation');
            if ($photoFile) {
                // Supprimer l'ancienne photo si elle existe
                if ($inspection->getPhotoObservation()) {
                    $oldPhotoPath = $this->getParameter('photos_directory') . '/' . $inspection->getPhotoObservation();
                    if (file_exists($oldPhotoPath)) {
                        unlink($oldPhotoPath);
                    }
                }

                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('photos_directory'),
                        $newFilename
                    );
                    $inspection->setPhotoObservation($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de la photo');
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Inspection modifiée avec succès !');
            return $this->redirectToRoute('app_equipements_inspection_details', ['id' => $inspection->getId()]);
        }

        return $this->render('equipements/extincteurs/modifier_inspection.html.twig', [
            'inspection' => $inspection,
            'extincteur' => $inspection->getExtincteur(),
            'criteres' => InspectionExtincteur::CRITERES,
        ]);
    }

    #[Route('/inspection/{id}/details', name: 'app_equipements_inspection_details')]
    public function voirDetailsInspection(
        InspectionExtincteur $inspection
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        // Vérifier les permissions
        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles()) && 
            $inspection->getExtincteur()->getZone() !== $user->getZone()) {
            throw $this->createAccessDeniedException('Accès non autorisé à cette inspection');
        }

        return $this->render('equipements/extincteurs/inspection_details.html.twig', [
            'inspection' => $inspection,
            'criteres' => InspectionExtincteur::CRITERES
        ]);
    }

    #[Route('/inspection/{id}/supprimer', name: 'app_equipements_inspection_supprimer')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function supprimerInspection(
        InspectionExtincteur $inspection,
        EntityManagerInterface $entityManager
    ): Response {
        try {
            $extincteur = $inspection->getExtincteur()->getNumerotation();

            $entityManager->remove($inspection);
            $entityManager->flush();

            $this->addFlash('success', 'L\'inspection de l\'extincteur ' . $extincteur . ' a été supprimée.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_equipements_inspections');
    }

    #[Route('/monte-charge-inspection/{id}/details', name: 'app_equipements_monte_charge_inspection_details')]
    public function voirDetailsInspectionMonteCharge(
        InspectionMonteCharge $inspection
    ): Response {
        return $this->render('equipements/monte_charge/inspection_details.html.twig', [
            'inspection' => $inspection,
            'questions' => InspectionMonteCharge::QUESTIONS
        ]);
    }

    #[Route('/monte-charge-inspection/{id}/supprimer', name: 'app_equipements_monte_charge_inspection_supprimer')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function supprimerInspectionMonteCharge(
        InspectionMonteCharge $inspection,
        EntityManagerInterface $entityManager
    ): Response {
        try {
            $monteCharge = $inspection->getMonteCharge()->getType();
            $porte = $inspection->getNumeroPorte();

            $entityManager->remove($inspection);
            $entityManager->flush();

            $this->addFlash('success', 'L\'inspection du monte-charge ' . $monteCharge . ' (porte ' . $porte . ') a été supprimée.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_equipements_monte_charge');
    }

    // Méthode utilitaire pour initialiser les monte-charges par défaut
    #[Route('/init-monte-charges', name: 'app_equipements_init_monte_charges')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function initMonteCharges(EntityManagerInterface $entityManager): Response
    {
        $monteChargeRepository = $entityManager->getRepository(MonteCharge::class);

        if ($monteChargeRepository->count([]) > 0) {
            $this->addFlash('info', 'Les monte-charges sont déjà initialisés.');
            return $this->redirectToRoute('app_equipements_monte_charge');
        }

        // Créer les monte-charges par défaut
        $mc1 = new MonteCharge();
        $mc1->setType('CHARGE01');
        $mc1->setZone('RDC TISSAGE-RENTRAGE-OURDISSOIR');
        $entityManager->persist($mc1);

        $mc2 = new MonteCharge();
        $mc2->setType('CHARGE02');
        $mc2->setZone('RDC TISSAGE-MEZZANINE-PRATO');
        $entityManager->persist($mc2);

        $entityManager->flush();

        $this->addFlash('success', 'Monte-charges initialisés avec succès !');
        return $this->redirectToRoute('app_equipements_monte_charge');
    }

    /**
     * Export Excel - État des extincteurs
     */
    #[Route('/extincteurs/export-excel', name: 'app_equipements_extincteurs_export_excel')]
    public function exportExtincteursExcel(
        ExtincteurRepository $extincteurRepository,
        Request $request
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $searchParams = [
            'zone' => '',
            'emplacement' => $request->query->get('emplacement', ''),
            'numerotation' => $request->query->get('numerotation', ''),
            'valide' => $request->query->get('valide', '')
        ];

        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $searchParams['zone'] = $user->getZone();
        }

        $extincteurs = $extincteurRepository->searchExtincteurs($searchParams, 1000, 0);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('État Extincteurs');

        // En-têtes
        $sheet->setCellValue('A1', 'N° Extincteur');
        $sheet->setCellValue('B1', 'Zone');
        $sheet->setCellValue('C1', 'Emplacement');
        $sheet->setCellValue('D1', 'Agent');
        $sheet->setCellValue('E1', 'Type');
        $sheet->setCellValue('F1', 'Capacité');
        $sheet->setCellValue('G1', 'Date Fabrication');
        $sheet->setCellValue('H1', 'Date Épreuve');
        $sheet->setCellValue('I1', 'Date Fin de Vie');
        $sheet->setCellValue('J1', 'N° Série');
        $sheet->setCellValue('K1', 'Validé');
        $sheet->setCellValue('L1', 'Date Validation');
        $sheet->setCellValue('M1', 'Validé Par');
        $sheet->setCellValue('N1', 'Statut Conformité');
        $sheet->setCellValue('O1', 'Dernière Inspection');
        $sheet->setCellValue('P1', 'Nb Inspections');

        // Style en-têtes
        $sheet->getStyle('A1:P1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ]
        ]);

        // Données
        $row = 2;
        foreach ($extincteurs as $extincteur) {
            $sheet->setCellValue('A' . $row, $extincteur->getNumerotation());
            $sheet->setCellValue('B' . $row, $extincteur->getZone());
            $sheet->setCellValue('C' . $row, $extincteur->getEmplacement() ?? '-');
            $sheet->setCellValue('D' . $row, $extincteur->getAgentExtincteur() ?? '-');
            $sheet->setCellValue('E' . $row, $extincteur->getType() ?? '-');
            $sheet->setCellValue('F' . $row, $extincteur->getCapacite() ?? '-');
            $sheet->setCellValue('G' . $row, $extincteur->getDateFabrication() ? $extincteur->getDateFabrication()->format('d/m/Y') : '-');
            $sheet->setCellValue('H' . $row, $extincteur->getDateEpreuve() ? $extincteur->getDateEpreuve()->format('d/m/Y') : '-');
            $sheet->setCellValue('I' . $row, $extincteur->getDateFinDeVie() ? $extincteur->getDateFinDeVie()->format('d/m/Y') : '-');
            $sheet->setCellValue('J' . $row, $extincteur->getNumeroSerie() ?? '-');
            $sheet->setCellValue('K' . $row, $extincteur->isValide() ? 'Oui' : 'Non');
            $sheet->setCellValue('L' . $row, $extincteur->getDateValidation() ? $extincteur->getDateValidation()->format('d/m/Y') : '-');
            $sheet->setCellValue('M' . $row, $extincteur->getValidePar() ? $extincteur->getValidePar()->getFullName() : '-');
            $sheet->setCellValue('N' . $row, $extincteur->getStatutConformite());
            
            $derniereInspection = $extincteur->getDerniereInspection();
            $sheet->setCellValue('O' . $row, $derniereInspection ? $derniereInspection->getDateInspection()->format('d/m/Y') : '-');
            $sheet->setCellValue('P' . $row, $extincteur->getNombreInspections());

            // Coloration conditionnelle
            if ($extincteur->getStatutConformite() == 'Conforme') {
                $sheet->getStyle('N' . $row)->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C6EFCE']],
                    'font' => ['color' => ['rgb' => '006100']]
                ]);
            } elseif ($extincteur->getStatutConformite() == 'Non conforme') {
                $sheet->getStyle('N' . $row)->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC7CE']],
                    'font' => ['color' => ['rgb' => '9C0006']]
                ]);
            }

            $row++;
        }

        // Auto-size
        foreach (range('A', 'P') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Réponse
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $response = new \Symfony\Component\HttpFoundation\Response();
        
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        $filename = 'etat_extincteurs_' . date('Y-m-d_His') . '.xlsx';
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->setContent($content);

        return $response;
    }

    /**
     * Export PDF - État des extincteurs
     */
    #[Route('/extincteurs/export-pdf', name: 'app_equipements_extincteurs_export_pdf')]
    public function exportExtincteursPDF(
        ExtincteurRepository $extincteurRepository,
        Request $request
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $searchParams = [
            'zone' => '',
            'emplacement' => $request->query->get('emplacement', ''),
            'numerotation' => $request->query->get('numerotation', ''),
            'valide' => $request->query->get('valide', '')
        ];

        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $searchParams['zone'] = $user->getZone();
        }

        $extincteurs = $extincteurRepository->searchExtincteurs($searchParams, 1000, 0);

        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);

        $html = $this->renderView('equipements/extincteurs/pdf_etat.html.twig', [
            'extincteurs' => $extincteurs,
            'search_params' => $searchParams,
            'date_export' => new \DateTime(),
            'user' => $user
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="etat_extincteurs_' . date('Y-m-d_His') . '.pdf"'
            ]
        );
    }

    /**
     * Export PDF - État des RIA
     */
    #[Route('/ria/export-pdf', name: 'app_equipements_ria_export_pdf')]
    public function exportRIAPDF(
        RIARepository $riaRepository,
        Request $request
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $searchParams = [
            'zone' => $request->query->get('zone', ''),
            'numerotation' => $request->query->get('numerotation', ''),
            'valide' => $request->query->get('valide', '')
        ];

        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $searchParams['zone'] = $user->getZone();
        }

        $rias = $riaRepository->searchRIA($searchParams, 1000, 0);

        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);

        $html = $this->renderView('equipements/ria/pdf_etat.html.twig', [
            'rias' => $rias,
            'search_params' => $searchParams,
            'date_export' => new \DateTime(),
            'user' => $user
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="etat_ria_' . date('Y-m-d_His') . '.pdf"'
            ]
        );
    }
}
