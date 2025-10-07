<?php

namespace App\Controller;

use App\Entity\Extincteur;
use App\Entity\InspectionExtincteur;
use App\Entity\RIA;
use App\Entity\InspectionRIA;
use App\Entity\MonteCharge;
use App\Entity\InspectionMonteCharge;
use App\Entity\PrisePompier;
use App\Entity\InspectionPrisePompier;
use App\Entity\IssueSecours;
use App\Entity\InspectionIssueSecours;
use App\Entity\Sirene;
use App\Entity\InspectionSirene;
use App\Entity\Desenfumage;
use App\Entity\InspectionDesenfumage;
use App\Entity\ExtinctionLocaliseeRAM;
use App\Entity\InspectionExtinctionRAM;
use App\Entity\User;
use App\Entity\RapportHSE;
use App\Repository\ExtincteurRepository;
use App\Repository\RIARepository;
use App\Repository\InspectionRIARepository;
use App\Repository\MonteChargeRepository;
use App\Repository\InspectionExtincteurRepository;
use App\Repository\InspectionMonteChargeRepository;
use App\Repository\PrisePompierRepository;
use App\Repository\InspectionPrisePompierRepository;
use App\Repository\IssueSecoursRepository;
use App\Repository\InspectionIssueSecoursRepository;
use App\Repository\SireneRepository;
use App\Repository\InspectionSireneRepository;
use App\Repository\DesenfumageRepository;
use App\Repository\InspectionDesenfumageRepository;
use App\Repository\ExtinctionLocaliseeRAMRepository;
use App\Repository\InspectionExtinctionRAMRepository;
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
            'stats_monte_charge' => $statsMonteCharge['stats_for_template'] ?? [],
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
            'conformite' => $request->query->get('conformite', '')
        ];

        // Filtrer par zone pour les non-super-admins (pour les permissions)
        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $searchParams['zone'] = $user->getZone();
        }

        // Récupérer tous les extincteurs (sans filtre de validation)
        $allExtincteurs = $extincteurRepository->searchExtincteurs(
            array_diff_key($searchParams, ['conformite' => '']),
            1000, // Récupérer plus pour filtrer après
            0
        );

        // Filtrer par conformité si nécessaire
        if (!empty($searchParams['conformite'])) {
            $allExtincteurs = array_filter($allExtincteurs, function($extincteur) use ($searchParams) {
                $conformite = $extincteur->getStatutConformite();
                return match($searchParams['conformite']) {
                    'conforme' => $conformite === 'Conforme',
                    'non_conforme' => $conformite === 'Non conforme',
                    'non_inspecte' => $conformite === 'Non inspecté',
                    default => true
                };
            });
            $allExtincteurs = array_values($allExtincteurs); // Réindexer le tableau
        }

        // Pagination manuelle
        $totalExtincteurs = count($allExtincteurs);
        $totalPages = ceil($totalExtincteurs / $limit);
        $offset = ($page - 1) * $limit;
        $extincteurs = array_slice($allExtincteurs, $offset, $limit);

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
            'conformite' => $request->query->get('conformite', '')
        ];

        // Filtrer par zone pour les non-super-admins (pour les permissions)
        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $searchParams['zone'] = $user->getZone();
        }

        // Récupérer tous les extincteurs (sans filtre de validation)
        $allExtincteurs = $extincteurRepository->searchExtincteurs(
            array_diff_key($searchParams, ['conformite' => '']),
            1000, // Récupérer plus pour filtrer après
            0
        );

        // Filtrer par conformité si nécessaire
        if (!empty($searchParams['conformite'])) {
            $allExtincteurs = array_filter($allExtincteurs, function($extincteur) use ($searchParams) {
                $conformite = $extincteur->getStatutConformite();
                return match($searchParams['conformite']) {
                    'conforme' => $conformite === 'Conforme',
                    'non_conforme' => $conformite === 'Non conforme',
                    'non_inspecte' => $conformite === 'Non inspecté',
                    default => true
                };
            });
            $allExtincteurs = array_values($allExtincteurs); // Réindexer le tableau
        }

        // Pagination manuelle
        $totalExtincteurs = count($allExtincteurs);
        $totalPages = ceil($totalExtincteurs / $limit);
        $offset = ($page - 1) * $limit;
        $extincteurs = array_slice($allExtincteurs, $offset, $limit);

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
        InspectionExtincteurRepository $inspectionRepository,
        Request $request
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        // Paramètres de recherche
        $searchParams = [
            'numerotation' => $request->query->get('numerotation', ''),
            'conformite' => $request->query->get('conformite', '')
        ];

        // Obtenir les extincteurs disponibles pour inspection
        $extincteurs = [];
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $extincteurs = $extincteurRepository->findAll();
        } else {
            $extincteurs = $extincteurRepository->findBy(['zone' => $user->getZone()]);
        }

        // Filtrer par numérotation
        if (!empty($searchParams['numerotation'])) {
            $extincteurs = array_filter($extincteurs, function($extincteur) use ($searchParams) {
                return stripos($extincteur->getNumerotation(), $searchParams['numerotation']) !== false;
            });
        }

        // Filtrer par conformité
        if (!empty($searchParams['conformite'])) {
            $extincteurs = array_filter($extincteurs, function($extincteur) use ($searchParams) {
                $conformite = $extincteur->getStatutConformite();
                return match($searchParams['conformite']) {
                    'conforme' => $conformite === 'Conforme',
                    'non_conforme' => $conformite === 'Non conforme',
                    'non_inspecte' => $conformite === 'Non inspecté',
                    default => true
                };
            });
        }

        // Réindexer le tableau
        $extincteurs = array_values($extincteurs);

        // Obtenir les dernières inspections
        $inspections = $inspectionRepository->getInspectionsAvecDetails();

        return $this->render('equipements/extincteurs/inspections.html.twig', [
            'extincteurs' => $extincteurs,
            'inspections' => $inspections,
            'user_zone' => $user->getZone(),
            'search_params' => $searchParams,
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
            'conformite' => $request->query->get('conformite', '')
        ];

        // Récupérer tous les RIA
        $allRias = $riaRepository->searchRIA(
            array_diff_key($searchParams, ['conformite' => '']),
            1000,
            0
        );

        // Filtrer par conformité
        if (!empty($searchParams['conformite'])) {
            $allRias = array_filter($allRias, function($ria) use ($searchParams) {
                $conformite = $ria->getStatutConformite();
                return match($searchParams['conformite']) {
                    'conforme' => $conformite === 'Conforme',
                    'non_conforme' => $conformite === 'Non conforme',
                    'non_inspecte' => $conformite === 'Non inspecté',
                    default => true
                };
            });
            $allRias = array_values($allRias);
        }

        // Pagination
        $totalRIAs = count($allRias);
        $totalPages = ceil($totalRIAs / $limit);
        $offset = ($page - 1) * $limit;
        $rias = array_slice($allRias, $offset, $limit);

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

    #[Route('/ria/{id}/inspecter', name: 'app_equipements_ria_inspecter')]
    public function inspecterRIA(
        RIA $ria,
        EntityManagerInterface $entityManager,
        InspectionRIARepository $inspectionRepository,
        Request $request,
        SluggerInterface $slugger
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        // Vérifier s'il y a déjà une inspection récente
        $derniereInspection = $inspectionRepository->getDerniereInspection($ria->getId());
        if ($derniereInspection && $derniereInspection->getDateInspection() > new \DateTime('-1 day')) {
            $this->addFlash('error', 'Ce RIA a déjà été inspecté récemment');
            return $this->redirectToRoute('app_equipements_ria');
        }

        if ($request->isMethod('POST')) {
            $inspection = new InspectionRIA();
            $inspection->setRia($ria);
            $inspection->setInspectePar($user);

            $criteres = [];
            foreach (InspectionRIA::CRITERES as $key => $label) {
                $criteres[$key] = $request->request->get('critere_' . $key) === 'oui';
            }

            $inspection->setCriteres($criteres);
            $inspection->setObservations($request->request->get('observations'));
            $inspection->setValide(!in_array(false, $criteres, true));

            $photoFile = $request->files->get('photo_observation');
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

                try {
                    $photoFile->move($this->getParameter('photos_directory'), $newFilename);
                    $inspection->setPhotoObservation($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de la photo');
                }
            }

            $entityManager->persist($inspection);
            $entityManager->flush();

            $this->addFlash('success', 'Inspection RIA enregistrée avec succès !');
            return $this->redirectToRoute('app_equipements_ria');
        }

        return $this->render('equipements/ria/inspecter.html.twig', [
            'ria' => $ria,
            'criteres' => InspectionRIA::CRITERES,
        ]);
    }

    // =============== MONTE-CHARGE TEFIL ===============

    #[Route('/monte-charge', name: 'app_equipements_monte_charge')]
    public function monteCharge(): Response {
        // Redirection vers le nouveau contrôleur MonteCharge
        return $this->redirectToRoute('app_monte_charge_index');
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

    /**
     * Export PDF - Détail d'une inspection extincteur
     */
    #[Route('/inspection/{id}/export-pdf', name: 'app_equipements_inspection_export_pdf')]
    public function exportInspectionExtincteurPDF(InspectionExtincteur $inspection): Response
    {
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);

        $html = $this->renderView('equipements/extincteurs/pdf_inspection_detail.html.twig', [
            'inspection' => $inspection,
            'criteres' => InspectionExtincteur::CRITERES,
            'date_export' => new \DateTime(),
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="inspection_extincteur_' . $inspection->getExtincteur()->getNumerotation() . '_' . date('Y-m-d') . '.pdf"'
            ]
        );
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

    /**
     * Export PDF - Détail d'une inspection monte-charge
     */
    #[Route('/monte-charge-inspection/{id}/export-pdf', name: 'app_equipements_monte_charge_inspection_export_pdf')]
    public function exportInspectionMonteChargePDF(InspectionMonteCharge $inspection): Response
    {
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);

        $html = $this->renderView('equipements/monte_charge/pdf_inspection_detail.html.twig', [
            'inspection' => $inspection,
            'questions' => InspectionMonteCharge::QUESTIONS,
            'date_export' => new \DateTime(),
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="inspection_monte_charge_' . $inspection->getMonteCharge()->getType() . '_' . $inspection->getNumeroPorte() . '_' . date('Y-m-d') . '.pdf"'
            ]
        );
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

    /**
     * Export PDF - Détail d'un RIA spécifique
     */
    #[Route('/ria/{id}/export-pdf', name: 'app_equipements_ria_export_detail_pdf')]
    public function exportRIADetailPDF(RIA $ria): Response
    {
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);

        $html = $this->renderView('equipements/ria/pdf_detail.html.twig', [
            'ria' => $ria,
            'date_export' => new \DateTime(),
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="ria_' . $ria->getNumerotation() . '_' . date('Y-m-d') . '.pdf"'
            ]
        );
    }

    // =============== PRISES POMPIERS - ÉTAT ===============

    #[Route('/prises-pompiers', name: 'app_equipements_prises_pompiers')]
    public function prisesPompiers(
        PrisePompierRepository $prisePompierRepository,
        Request $request
    ): Response {
        $page = $request->query->getInt('page', 1);
        $limit = 20;

        $searchParams = [
            'zone' => $request->query->get('zone', ''),
            'emplacement' => $request->query->get('emplacement', ''),
            'conformite' => $request->query->get('conformite', '')
        ];

        $allPrises = $prisePompierRepository->searchPrisesPompiers(
            array_diff_key($searchParams, ['conformite' => '']),
            1000,
            0
        );

        // Filtrer par conformité
        if (!empty($searchParams['conformite'])) {
            $allPrises = array_filter($allPrises, function($prise) use ($searchParams) {
                $conformite = $prise->getStatutConformite();
                return match($searchParams['conformite']) {
                    'conforme' => $conformite === 'Conforme',
                    'non_conforme' => $conformite === 'Non conforme',
                    'non_inspecte' => $conformite === 'Non inspecté',
                    default => true
                };
            });
            $allPrises = array_values($allPrises);
        }

        $totalPrises = count($allPrises);
        $totalPages = ceil($totalPrises / $limit);
        $offset = ($page - 1) * $limit;
        $prises = array_slice($allPrises, $offset, $limit);

        return $this->render('equipements/prises_pompiers/liste.html.twig', [
            'prises' => $prises,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'search_params' => $searchParams,
        ]);
    }

    #[Route('/prises-pompiers/nouveau', name: 'app_equipements_prises_pompiers_nouveau')]
    #[IsGranted('ROLE_ADMIN')]
    public function nouveauPrisePompier(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $prise = new PrisePompier();

        if ($request->isMethod('POST')) {
            $prise->setZone($request->request->get('zone'));
            $prise->setEmplacement($request->request->get('emplacement'));
            $prise->setDimatere($request->request->get('dimatere'));

            $entityManager->persist($prise);
            $entityManager->flush();

            $this->addFlash('success', 'Prise pompier ajoutée avec succès !');
            return $this->redirectToRoute('app_equipements_prises_pompiers');
        }

        return $this->render('equipements/prises_pompiers/nouveau.html.twig', [
            'prise' => $prise,
            'zones_disponibles' => PrisePompier::ZONES_PRISES,
            'emplacements_disponibles' => PrisePompier::EMPLACEMENTS_PRISES,
            'diametres_disponibles' => PrisePompier::DIAMETRES_DISPONIBLES,
        ]);
    }

    #[Route('/prises-pompiers/{id}/modifier', name: 'app_equipements_prises_pompiers_modifier')]
    #[IsGranted('ROLE_ADMIN')]
    public function modifierPrisePompier(
        PrisePompier $prise,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if ($request->isMethod('POST')) {
            $prise->setZone($request->request->get('zone'));
            $prise->setEmplacement($request->request->get('emplacement'));
            $prise->setDimatere($request->request->get('dimatere'));

            $entityManager->flush();

            $this->addFlash('success', 'Prise pompier modifiée avec succès !');
            return $this->redirectToRoute('app_equipements_prises_pompiers');
        }

        return $this->render('equipements/prises_pompiers/modifier.html.twig', [
            'prise' => $prise,
            'zones_disponibles' => PrisePompier::ZONES_PRISES,
            'emplacements_disponibles' => PrisePompier::EMPLACEMENTS_PRISES,
            'diametres_disponibles' => PrisePompier::DIAMETRES_DISPONIBLES,
        ]);
    }

    #[Route('/prises-pompiers/{id}/supprimer', name: 'app_equipements_prises_pompiers_supprimer')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function supprimerPrisePompier(
        PrisePompier $prise,
        EntityManagerInterface $entityManager
    ): Response {
        try {
            $identifiant = $prise->getIdentifiant();
            $entityManager->remove($prise);
            $entityManager->flush();

            $this->addFlash('success', 'La prise pompier (' . $identifiant . ') a été supprimée avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_equipements_prises_pompiers');
    }

    #[Route('/prises-pompiers/{id}/details', name: 'app_equipements_prise_pompier_details')]
    public function detailsPrisePompier(PrisePompier $prise): Response
    {
        return $this->render('equipements/prises_pompiers/details.html.twig', [
            'prise' => $prise,
        ]);
    }

    #[Route('/prises-pompiers/{id}/export-pdf', name: 'app_equipements_prise_pompier_export_detail_pdf')]
    public function exportPrisePompierDetailPDF(PrisePompier $prise): Response
    {
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);

        $html = $this->renderView('equipements/prises_pompiers/pdf_detail.html.twig', [
            'prise' => $prise,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="prise_pompier_' . $prise->getId() . '_' . date('Ymd') . '.pdf"'
            ]
        );
    }

    #[Route('/prises-pompiers/{id}/inspecter', name: 'app_equipements_prises_pompiers_inspecter')]
    public function inspecterPrisePompier(
        PrisePompier $prise,
        EntityManagerInterface $entityManager,
        InspectionPrisePompierRepository $inspectionRepository,
        Request $request,
        SluggerInterface $slugger
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        // Vérifier s'il y a déjà une inspection récente (optionnel pour prises pompiers)
        $derniereInspection = $inspectionRepository->getDerniereInspection($prise->getId());
        if ($derniereInspection && $derniereInspection->getDateInspection() > new \DateTime('-1 day')) {
            $this->addFlash('warning', 'Cette prise pompier a déjà été inspectée il y a moins de 24h');
            // On ne bloque pas, on affiche juste un warning
        }

        if ($request->isMethod('POST')) {
            $inspection = new InspectionPrisePompier();
            $inspection->setPrisePompier($prise);
            $inspection->setInspectePar($user);

            $criteres = [];
            foreach (InspectionPrisePompier::CRITERES as $key => $label) {
                $criteres[$key] = $request->request->get('critere_' . $key) === 'oui';
            }

            $inspection->setCriteres($criteres);
            $inspection->setObservations($request->request->get('observations'));
            $inspection->setValide(!in_array(false, $criteres, true));

            // Gérer l'upload de photo
            $photoFile = $request->files->get('photo_observation');
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

                try {
                    $photoFile->move($this->getParameter('photos_directory'), $newFilename);
                    $inspection->setPhotoObservation($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de la photo');
                }
            }

            $entityManager->persist($inspection);
            $entityManager->flush();

            $this->addFlash('success', 'Inspection enregistrée avec succès !');
            return $this->redirectToRoute('app_equipements_prises_pompiers');
        }

        return $this->render('equipements/prises_pompiers/inspecter.html.twig', [
            'prise' => $prise,
            'criteres' => InspectionPrisePompier::CRITERES,
        ]);
    }

    // =============== ISSUES DE SECOURS - ÉTAT ===============

    #[Route('/issues-secours', name: 'app_equipements_issues_secours')]
    public function issuesSecours(
        IssueSecoursRepository $issueSecoursRepository,
        Request $request
    ): Response {
        $page = $request->query->getInt('page', 1);
        $limit = 20;

        $searchParams = [
            'zone' => $request->query->get('zone', ''),
            'numerotation' => $request->query->get('numerotation', ''),
            'conformite' => $request->query->get('conformite', '')
        ];

        $allIssues = $issueSecoursRepository->searchIssuesSecours(
            array_diff_key($searchParams, ['conformite' => '']),
            1000,
            0
        );

        if (!empty($searchParams['conformite'])) {
            $allIssues = array_filter($allIssues, function($issue) use ($searchParams) {
                $conformite = $issue->getStatutConformite();
                return match($searchParams['conformite']) {
                    'conforme' => $conformite === 'Conforme',
                    'non_conforme' => $conformite === 'Non conforme',
                    'non_inspecte' => $conformite === 'Non inspecté',
                    default => true
                };
            });
            $allIssues = array_values($allIssues);
        }

        $totalIssues = count($allIssues);
        $totalPages = ceil($totalIssues / $limit);
        $offset = ($page - 1) * $limit;
        $issues = array_slice($allIssues, $offset, $limit);

        return $this->render('equipements/issues_secours/liste.html.twig', [
            'issues' => $issues,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'search_params' => $searchParams,
        ]);
    }

    #[Route('/issues-secours/nouveau', name: 'app_equipements_issues_secours_nouveau')]
    #[IsGranted('ROLE_ADMIN')]
    public function nouveauIssueSecours(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $issue = new IssueSecours();

        if ($request->isMethod('POST')) {
            $issue->setNumerotation($request->request->get('numerotation'));
            $issue->setZone($request->request->get('zone'));
            $issue->setType($request->request->get('type'));
            $issue->setBarreAntipanique($request->request->get('barre_antipanique'));

            $entityManager->persist($issue);
            $entityManager->flush();

            $this->addFlash('success', 'Issue de secours ajoutée avec succès !');
            return $this->redirectToRoute('app_equipements_issues_secours');
        }

        return $this->render('equipements/issues_secours/nouveau.html.twig', [
            'issue' => $issue,
            'zones_disponibles' => IssueSecours::ZONES_ISSUES,
            'numerotations_disponibles' => IssueSecours::NUMEROTATIONS_ISSUES,
            'types_disponibles' => IssueSecours::TYPES_ISSUES,
            'etats_barre' => IssueSecours::ETAT_BARRE,
        ]);
    }

    #[Route('/issues-secours/{id}/modifier', name: 'app_equipements_issues_secours_modifier')]
    #[IsGranted('ROLE_ADMIN')]
    public function modifierIssueSecours(
        IssueSecours $issue,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if ($request->isMethod('POST')) {
            $issue->setZone($request->request->get('zone'));
            $issue->setNumerotation($request->request->get('numerotation'));
            $issue->setType($request->request->get('type'));
            $issue->setBarreAntipanique($request->request->get('barre_antipanique'));

            $entityManager->flush();

            $this->addFlash('success', 'Issue de secours modifiée avec succès !');
            return $this->redirectToRoute('app_equipements_issues_secours');
        }

        return $this->render('equipements/issues_secours/modifier.html.twig', [
            'issue' => $issue,
            'zones_disponibles' => IssueSecours::ZONES_ISSUES,
            'numerotations_disponibles' => IssueSecours::NUMEROTATIONS_ISSUES,
            'types_disponibles' => IssueSecours::TYPES_ISSUES,
            'etats_barre' => IssueSecours::ETAT_BARRE,
        ]);
    }

    #[Route('/issues-secours/{id}/supprimer', name: 'app_equipements_issues_secours_supprimer')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function supprimerIssueSecours(
        IssueSecours $issue,
        EntityManagerInterface $entityManager
    ): Response {
        try {
            $identifiant = $issue->getZone() . ' - ' . $issue->getNumerotation();
            $entityManager->remove($issue);
            $entityManager->flush();

            $this->addFlash('success', 'L\'issue de secours (' . $identifiant . ') a été supprimée avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_equipements_issues_secours');
    }

    #[Route('/issues-secours/{id}/details', name: 'app_equipements_issue_secours_details')]
    public function detailsIssueSecours(IssueSecours $issue): Response
    {
        return $this->render('equipements/issues_secours/details.html.twig', [
            'issue' => $issue,
        ]);
    }

    #[Route('/issues-secours/{id}/export-pdf', name: 'app_equipements_issue_secours_export_detail_pdf')]
    public function exportIssueSecoursDetailPDF(IssueSecours $issue): Response
    {
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);

        $html = $this->renderView('equipements/issues_secours/pdf_detail.html.twig', [
            'issue' => $issue,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="issue_secours_' . $issue->getId() . '_' . date('Ymd') . '.pdf"'
            ]
        );
    }

    #[Route('/issues-secours/{id}/inspecter', name: 'app_equipements_issues_secours_inspecter')]
    public function inspecterIssueSecours(
        IssueSecours $issue,
        EntityManagerInterface $entityManager,
        Request $request,
        SluggerInterface $slugger
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $inspection = new InspectionIssueSecours();
            $inspection->setIssueSecours($issue);
            $inspection->setInspectePar($user);

            $criteres = [];
            foreach (InspectionIssueSecours::CRITERES as $key => $label) {
                $criteres[$key] = $request->request->get('critere_' . $key) === 'oui';
            }

            $inspection->setCriteres($criteres);
            $inspection->setObservations($request->request->get('observations'));
            $inspection->setValide(!in_array(false, $criteres, true));

            $photoFile = $request->files->get('photo_observation');
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

                try {
                    $photoFile->move($this->getParameter('photos_directory'), $newFilename);
                    $inspection->setPhotoObservation($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de la photo');
                }
            }

            $entityManager->persist($inspection);
            $entityManager->flush();

            $this->addFlash('success', 'Inspection enregistrée avec succès !');
            return $this->redirectToRoute('app_equipements_issues_secours');
        }

        return $this->render('equipements/issues_secours/inspecter.html.twig', [
            'issue' => $issue,
            'criteres' => InspectionIssueSecours::CRITERES,
        ]);
    }

    // =============== SIRENES - ÉTAT ===============

    #[Route('/sirenes', name: 'app_equipements_sirenes')]
    public function sirenes(
        SireneRepository $sireneRepository,
        Request $request
    ): Response {
        $page = $request->query->getInt('page', 1);
        $limit = 20;

        $searchParams = [
            'zone' => $request->query->get('zone', ''),
            'numerotation' => $request->query->get('numerotation', ''),
            'conformite' => $request->query->get('conformite', '')
        ];

        $allSirenes = $sireneRepository->searchSirenes(
            array_diff_key($searchParams, ['conformite' => '']),
            1000,
            0
        );

        if (!empty($searchParams['conformite'])) {
            $allSirenes = array_filter($allSirenes, function($sirene) use ($searchParams) {
                $conformite = $sirene->getStatutConformite();
                return match($searchParams['conformite']) {
                    'conforme' => $conformite === 'Conforme',
                    'non_conforme' => $conformite === 'Non conforme',
                    'non_inspecte' => $conformite === 'Non inspecté',
                    default => true
                };
            });
            $allSirenes = array_values($allSirenes);
        }

        $totalSirenes = count($allSirenes);
        $totalPages = ceil($totalSirenes / $limit);
        $offset = ($page - 1) * $limit;
        $sirenes = array_slice($allSirenes, $offset, $limit);

        return $this->render('equipements/sirenes/liste.html.twig', [
            'sirenes' => $sirenes,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'search_params' => $searchParams,
        ]);
    }

    #[Route('/sirenes/nouveau', name: 'app_equipements_sirenes_nouveau')]
    #[IsGranted('ROLE_ADMIN')]
    public function nouveauSirene(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $sirene = new Sirene();

        if ($request->isMethod('POST')) {
            $sirene->setNumerotation($request->request->get('numerotation'));
            $sirene->setZone($request->request->get('zone'));
            $sirene->setEmplacement($request->request->get('emplacement'));
            $sirene->setType($request->request->get('type'));

            $entityManager->persist($sirene);
            $entityManager->flush();

            $this->addFlash('success', 'Sirène ajoutée avec succès !');
            return $this->redirectToRoute('app_equipements_sirenes');
        }

        return $this->render('equipements/sirenes/nouveau.html.twig', [
            'sirene' => $sirene,
            'zones_disponibles' => Sirene::ZONES_SIRENE,
            'emplacements_disponibles' => Sirene::EMPLACEMENTS_SIRENE,
        ]);
    }

    #[Route('/sirenes/{id}/details', name: 'app_equipements_sirene_details')]
    public function detailsSirene(Sirene $sirene): Response
    {
        return $this->render('equipements/sirenes/details.html.twig', [
            'sirene' => $sirene,
        ]);
    }

    #[Route('/sirenes/{id}/export-pdf', name: 'app_equipements_sirene_export_detail_pdf')]
    public function exportSireneDetailPDF(Sirene $sirene): Response
    {
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);

        $html = $this->renderView('equipements/sirenes/pdf_detail.html.twig', [
            'sirene' => $sirene,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="sirene_' . $sirene->getId() . '_' . date('Ymd') . '.pdf"'
            ]
        );
    }

    #[Route('/sirenes/{id}/inspecter', name: 'app_equipements_sirenes_inspecter')]
    public function inspecterSirene(
        Sirene $sirene,
        EntityManagerInterface $entityManager,
        Request $request,
        SluggerInterface $slugger
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $inspection = new InspectionSirene();
            $inspection->setSirene($sirene);
            $inspection->setInspectePar($user);

            $conformite = $request->request->get('conformite');
            $inspection->setConformite($conformite);
            $inspection->setObservations($request->request->get('observations'));
            $inspection->setValide($conformite === 'Oui');

            $photoFile = $request->files->get('photo_observation');
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

                try {
                    $photoFile->move($this->getParameter('photos_directory'), $newFilename);
                    $inspection->setPhotoObservation($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de la photo');
                }
            }

            $entityManager->persist($inspection);
            $entityManager->flush();

            $this->addFlash('success', 'Inspection enregistrée avec succès !');
            return $this->redirectToRoute('app_equipements_sirenes');
        }

        return $this->render('equipements/sirenes/inspecter.html.twig', [
            'sirene' => $sirene,
        ]);
    }

    // =============== DÉSENFUMAGE - ÉTAT ===============

    #[Route('/desenfumage', name: 'app_equipements_desenfumage')]
    public function desenfumage(
        DesenfumageRepository $desenfumageRepository,
        Request $request
    ): Response {
        $page = $request->query->getInt('page', 1);
        $limit = 20;

        $searchParams = [
            'zone' => $request->query->get('zone', ''),
            'numerotation' => $request->query->get('numerotation', ''),
            'conformite' => $request->query->get('conformite', '')
        ];

        $allDesenfumages = $desenfumageRepository->searchDesenfumages(
            array_diff_key($searchParams, ['conformite' => '']),
            1000,
            0
        );

        if (!empty($searchParams['conformite'])) {
            $allDesenfumages = array_filter($allDesenfumages, function($desenfumage) use ($searchParams) {
                $conformite = $desenfumage->getStatutConformite();
                return match($searchParams['conformite']) {
                    'conforme' => $conformite === 'Conforme',
                    'non_conforme' => $conformite === 'Non conforme',
                    'non_inspecte' => $conformite === 'Non inspecté',
                    default => true
                };
            });
            $allDesenfumages = array_values($allDesenfumages);
        }

        $totalDesenfumages = count($allDesenfumages);
        $totalPages = ceil($totalDesenfumages / $limit);
        $offset = ($page - 1) * $limit;
        $desenfumages = array_slice($allDesenfumages, $offset, $limit);

        return $this->render('equipements/desenfumage/liste.html.twig', [
            'desenfumages' => $desenfumages,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'search_params' => $searchParams,
        ]);
    }

    #[Route('/desenfumage/nouveau', name: 'app_equipements_desenfumage_nouveau')]
    #[IsGranted('ROLE_ADMIN')]
    public function nouveauDesenfumage(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $desenfumage = new Desenfumage();

        if ($request->isMethod('POST')) {
            $desenfumage->setNumerotation($request->request->get('numerotation'));
            $desenfumage->setZone($request->request->get('zone'));
            $desenfumage->setEmplacement($request->request->get('emplacement'));
            $desenfumage->setType($request->request->get('type'));
            $desenfumage->setEtatCommande($request->request->get('etat_commande'));
            $desenfumage->setEtatSkydome($request->request->get('etat_skydome'));

            $entityManager->persist($desenfumage);
            $entityManager->flush();

            $this->addFlash('success', 'Désenfumage ajouté avec succès !');
            return $this->redirectToRoute('app_equipements_desenfumage');
        }

        return $this->render('equipements/desenfumage/nouveau.html.twig', [
            'desenfumage' => $desenfumage,
        ]);
    }

    #[Route('/desenfumage/{id}/details', name: 'app_equipements_desenfumage_details')]
    public function detailsDesenfumage(Desenfumage $desenfumage): Response
    {
        return $this->render('equipements/desenfumage/details.html.twig', [
            'desenfumage' => $desenfumage,
        ]);
    }

    #[Route('/desenfumage/{id}/export-pdf', name: 'app_equipements_desenfumage_export_detail_pdf')]
    public function exportDesenfumageDetailPDF(Desenfumage $desenfumage): Response
    {
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);

        $html = $this->renderView('equipements/desenfumage/pdf_detail.html.twig', [
            'desenfumage' => $desenfumage,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="desenfumage_' . $desenfumage->getId() . '_' . date('Ymd') . '.pdf"'
            ]
        );
    }

    #[Route('/desenfumage/{id}/inspecter', name: 'app_equipements_desenfumage_inspecter')]
    public function inspecterDesenfumage(
        Desenfumage $desenfumage,
        EntityManagerInterface $entityManager,
        Request $request,
        SluggerInterface $slugger
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $inspection = new InspectionDesenfumage();
            $inspection->setDesenfumage($desenfumage);
            $inspection->setInspectePar($user);

            $criteres = [];
            foreach (InspectionDesenfumage::CRITERES as $key => $label) {
                $criteres[$key] = $request->request->get('critere_' . $key) === 'oui';
            }

            $inspection->setCriteres($criteres);
            $inspection->setObservations($request->request->get('observations'));
            $inspection->setValide(!in_array(false, $criteres, true));

            $photoFile = $request->files->get('photo_observation');
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

                try {
                    $photoFile->move($this->getParameter('photos_directory'), $newFilename);
                    $inspection->setPhotoObservation($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de la photo');
                }
            }

            $entityManager->persist($inspection);
            $entityManager->flush();

            $this->addFlash('success', 'Inspection enregistrée avec succès !');
            return $this->redirectToRoute('app_equipements_desenfumage');
        }

        return $this->render('equipements/desenfumage/inspecter.html.twig', [
            'desenfumage' => $desenfumage,
            'criteres' => InspectionDesenfumage::CRITERES,
        ]);
    }

    // =============== EXTINCTION LOCALISÉE RAM ===============

    #[Route('/extinction-ram', name: 'app_equipements_extinction_ram')]
    public function extinctionRAM(
        ExtinctionLocaliseeRAMRepository $extinctionRAMRepository,
        Request $request
    ): Response {
        $page = $request->query->getInt('page', 1);
        $limit = 20;

        $searchParams = [
            'zone' => $request->query->get('zone', ''),
            'numerotation' => $request->query->get('numerotation', ''),
            'conformite' => $request->query->get('conformite', '')
        ];

        $allExtinctions = $extinctionRAMRepository->searchExtinctionsRAM(
            array_diff_key($searchParams, ['conformite' => '']),
            1000,
            0
        );

        if (!empty($searchParams['conformite'])) {
            $allExtinctions = array_filter($allExtinctions, function($extinction) use ($searchParams) {
                $conformite = $extinction->getStatutConformite();
                return match($searchParams['conformite']) {
                    'conforme' => $conformite === 'Conforme',
                    'non_conforme' => $conformite === 'Non conforme',
                    'non_inspecte' => $conformite === 'Non inspecté',
                    default => true
                };
            });
            $allExtinctions = array_values($allExtinctions);
        }

        $totalExtinctions = count($allExtinctions);
        $totalPages = ceil($totalExtinctions / $limit);
        $offset = ($page - 1) * $limit;
        $extinctions = array_slice($allExtinctions, $offset, $limit);

        return $this->render('equipements/extinction_ram/liste.html.twig', [
            'extinctions' => $extinctions,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'search_params' => $searchParams,
        ]);
    }

    #[Route('/extinction-ram/nouveau', name: 'app_equipements_extinction_ram_nouveau')]
    #[IsGranted('ROLE_ADMIN')]
    public function nouveauExtinctionRAM(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $extinction = new ExtinctionLocaliseeRAM();

        if ($request->isMethod('POST')) {
            $extinction->setNumerotation($request->request->get('numerotation'));
            $extinction->setZone($request->request->get('zone'));
            $extinction->setEmplacement($request->request->get('emplacement'));
            $extinction->setType($request->request->get('type'));
            $extinction->setVanne($request->request->get('vanne'));

            $entityManager->persist($extinction);
            $entityManager->flush();

            $this->addFlash('success', 'Système d\'extinction RAM ajouté avec succès !');
            return $this->redirectToRoute('app_equipements_extinction_ram');
        }

        return $this->render('equipements/extinction_ram/nouveau.html.twig', [
            'extinction' => $extinction,
        ]);
    }

    #[Route('/extinction-ram/{id}/details', name: 'app_equipements_extinction_ram_details')]
    public function detailsExtinctionRAM(ExtinctionLocaliseeRAM $ram): Response
    {
        return $this->render('equipements/extinction_ram/details.html.twig', [
            'ram' => $ram,
        ]);
    }

    #[Route('/extinction-ram/{id}/export-pdf', name: 'app_equipements_extinction_ram_export_detail_pdf')]
    public function exportExtinctionRAMDetailPDF(ExtinctionLocaliseeRAM $ram): Response
    {
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);

        $html = $this->renderView('equipements/extinction_ram/pdf_detail.html.twig', [
            'ram' => $ram,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="extinction_ram_' . $ram->getId() . '_' . date('Ymd') . '.pdf"'
            ]
        );
    }

    #[Route('/extinction-ram/{id}/inspecter', name: 'app_equipements_extinction_ram_inspecter')]
    public function inspecterExtinctionRAM(
        ExtinctionLocaliseeRAM $extinction,
        EntityManagerInterface $entityManager,
        Request $request,
        SluggerInterface $slugger
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $inspection = new InspectionExtinctionRAM();
            $inspection->setExtinctionLocaliseeRAM($extinction);
            $inspection->setInspectePar($user);

            $conformite = $request->request->get('conformite');
            $inspection->setConformite($conformite);
            $inspection->setObservations($request->request->get('observations'));
            $inspection->setValide($conformite === 'Oui');

            $photoFile = $request->files->get('photo_observation');
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

                try {
                    $photoFile->move($this->getParameter('photos_directory'), $newFilename);
                    $inspection->setPhotoObservation($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de la photo');
                }
            }

            $entityManager->persist($inspection);
            $entityManager->flush();

            $this->addFlash('success', 'Inspection enregistrée avec succès !');
            return $this->redirectToRoute('app_equipements_extinction_ram');
        }

        return $this->render('equipements/extinction_ram/inspecter.html.twig', [
            'extinction' => $extinction,
        ]);
    }

    // =============== EXPORTS EXCEL ET PDF ===============

    /**
     * Export Excel - État des RIA
     */
    #[Route('/ria/export-excel', name: 'app_equipements_ria_export_excel')]
    public function exportRIAExcel(
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

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('État RIA');

        // En-têtes
        $sheet->setCellValue('A1', 'Numéro');
        $sheet->setCellValue('B1', 'Zone');
        $sheet->setCellValue('C1', 'Agent Extincteur');
        $sheet->setCellValue('D1', 'Diamètre');
        $sheet->setCellValue('E1', 'Longueur');
        $sheet->setCellValue('F1', 'Statut');
        $sheet->setCellValue('G1', 'Dernière Inspection');
        $sheet->setCellValue('H1', 'Nb Inspections');

        // Style en-têtes
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ]
        ]);

        // Données
        $row = 2;
        foreach ($rias as $ria) {
            $sheet->setCellValue('A' . $row, $ria->getNumerotation());
            $sheet->setCellValue('B' . $row, $ria->getZone());
            $sheet->setCellValue('C' . $row, $ria->getAgentExtincteur() ?? '-');
            $sheet->setCellValue('D' . $row, $ria->getDimatere() ? $ria->getDimatere() . 'mm' : '-');
            $sheet->setCellValue('E' . $row, $ria->getLongueur() ? $ria->getLongueur() . 'm' : '-');
            $sheet->setCellValue('F' . $row, $ria->getStatutConformite());
            
            $derniereInspection = $ria->getDerniereInspection();
            $sheet->setCellValue('G' . $row, $derniereInspection ? $derniereInspection->getDateInspection()->format('d/m/Y') : '-');
            $sheet->setCellValue('H' . $row, $ria->getNombreInspections());

            // Coloration conditionnelle
            if ($ria->getStatutConformite() == 'Conforme') {
                $sheet->getStyle('F' . $row)->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C6EFCE']],
                    'font' => ['color' => ['rgb' => '006100']]
                ]);
            } elseif ($ria->getStatutConformite() == 'Non conforme') {
                $sheet->getStyle('F' . $row)->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC7CE']],
                    'font' => ['color' => ['rgb' => '9C0006']]
                ]);
            }

            $row++;
        }

        // Auto-size
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Réponse
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $response = new \Symfony\Component\HttpFoundation\Response();
        
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        $filename = 'etat_ria_' . date('Y-m-d_His') . '.xlsx';
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->setContent($content);

        return $response;
    }

    /**
     * Export Excel - Désenfumage
     */
    #[Route('/desenfumage/export-excel', name: 'app_equipements_desenfumage_export_excel')]
    public function exportDesenfumageExcel(
        DesenfumageRepository $desenfumageRepository,
        Request $request
    ): Response {
        $searchParams = [
            'zone' => $request->query->get('zone', ''),
            'numerotation' => $request->query->get('numerotation', ''),
        ];

        $desenfumages = $desenfumageRepository->searchDesenfumages($searchParams, 1000, 0);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Désenfumage');

        // En-têtes
        $sheet->setCellValue('A1', 'Numéro');
        $sheet->setCellValue('B1', 'Zone');
        $sheet->setCellValue('C1', 'Emplacement');
        $sheet->setCellValue('D1', 'Type');
        $sheet->setCellValue('E1', 'État Commande');
        $sheet->setCellValue('F1', 'État Skydome');
        $sheet->setCellValue('G1', 'Statut');
        $sheet->setCellValue('H1', 'Dernière Inspection');

        // Style en-têtes
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ]
        ]);

        // Données
        $row = 2;
        foreach ($desenfumages as $desenfumage) {
            $sheet->setCellValue('A' . $row, $desenfumage->getNumerotation());
            $sheet->setCellValue('B' . $row, $desenfumage->getZone());
            $sheet->setCellValue('C' . $row, $desenfumage->getEmplacement() ?? '-');
            $sheet->setCellValue('D' . $row, $desenfumage->getType() ?? '-');
            $sheet->setCellValue('E' . $row, $desenfumage->getEtatCommande() ?? '-');
            $sheet->setCellValue('F' . $row, $desenfumage->getEtatSkydome() ?? '-');
            $sheet->setCellValue('G' . $row, $desenfumage->getStatutConformite());
            
            $derniereInspection = $desenfumage->getDerniereInspection();
            $sheet->setCellValue('H' . $row, $derniereInspection ? $derniereInspection->getDateInspection()->format('d/m/Y') : '-');

            // Coloration conditionnelle
            if ($desenfumage->getStatutConformite() == 'Conforme') {
                $sheet->getStyle('G' . $row)->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C6EFCE']],
                    'font' => ['color' => ['rgb' => '006100']]
                ]);
            } elseif ($desenfumage->getStatutConformite() == 'Non conforme') {
                $sheet->getStyle('G' . $row)->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC7CE']],
                    'font' => ['color' => ['rgb' => '9C0006']]
                ]);
            }

            $row++;
        }

        // Auto-size
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Réponse
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $response = new \Symfony\Component\HttpFoundation\Response();
        
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        $filename = 'desenfumage_' . date('Y-m-d_His') . '.xlsx';
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->setContent($content);

        return $response;
    }

    /**
     * Export PDF - Désenfumage
     */
    #[Route('/desenfumage/export-pdf', name: 'app_equipements_desenfumage_export_pdf')]
    public function exportDesenfumagePDF(
        DesenfumageRepository $desenfumageRepository,
        Request $request
    ): Response {
        $searchParams = [
            'zone' => $request->query->get('zone', ''),
            'numerotation' => $request->query->get('numerotation', ''),
        ];

        $desenfumages = $desenfumageRepository->searchDesenfumages($searchParams, 1000, 0);

        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);

        $html = $this->renderView('equipements/desenfumage/pdf_etat.html.twig', [
            'desenfumages' => $desenfumages,
            'search_params' => $searchParams,
            'date_export' => new \DateTime(),
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="desenfumage_' . date('Y-m-d_His') . '.pdf"'
            ]
        );
    }

    /**
     * Export Excel - Extinction RAM
     */
    #[Route('/extinction-ram/export-excel', name: 'app_equipements_extinction_ram_export_excel')]
    public function exportExtinctionRAMExcel(
        ExtinctionLocaliseeRAMRepository $extinctionRepository,
        Request $request
    ): Response {
        $searchParams = [
            'zone' => $request->query->get('zone', ''),
            'numerotation' => $request->query->get('numerotation', ''),
        ];

        $extinctions = $extinctionRepository->searchExtinctionsRAM($searchParams, 1000, 0);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Extinction RAM');

        // En-têtes
        $sheet->setCellValue('A1', 'Numéro');
        $sheet->setCellValue('B1', 'Zone');
        $sheet->setCellValue('C1', 'Emplacement');
        $sheet->setCellValue('D1', 'Type');
        $sheet->setCellValue('E1', 'Vanne');
        $sheet->setCellValue('F1', 'Statut');
        $sheet->setCellValue('G1', 'Dernière Inspection');

        // Style en-têtes
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ]
        ]);

        // Données
        $row = 2;
        foreach ($extinctions as $extinction) {
            $sheet->setCellValue('A' . $row, $extinction->getNumerotation());
            $sheet->setCellValue('B' . $row, $extinction->getZone());
            $sheet->setCellValue('C' . $row, $extinction->getEmplacement() ?? '-');
            $sheet->setCellValue('D' . $row, $extinction->getType() ?? '-');
            $sheet->setCellValue('E' . $row, $extinction->getVanne() ?? '-');
            $sheet->setCellValue('F' . $row, $extinction->getStatutConformite());
            
            $derniereInspection = $extinction->getDerniereInspection();
            $sheet->setCellValue('G' . $row, $derniereInspection ? $derniereInspection->getDateInspection()->format('d/m/Y') : '-');

            // Coloration conditionnelle
            if ($extinction->getStatutConformite() == 'Conforme') {
                $sheet->getStyle('F' . $row)->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C6EFCE']],
                    'font' => ['color' => ['rgb' => '006100']]
                ]);
            } elseif ($extinction->getStatutConformite() == 'Non conforme') {
                $sheet->getStyle('F' . $row)->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC7CE']],
                    'font' => ['color' => ['rgb' => '9C0006']]
                ]);
            }

            $row++;
        }

        // Auto-size
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Réponse
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $response = new \Symfony\Component\HttpFoundation\Response();
        
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        $filename = 'extinction_ram_' . date('Y-m-d_His') . '.xlsx';
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->setContent($content);

        return $response;
    }

    /**
     * Export PDF - Extinction RAM
     */
    #[Route('/extinction-ram/export-pdf', name: 'app_equipements_extinction_ram_export_pdf')]
    public function exportExtinctionRAMPDF(
        ExtinctionLocaliseeRAMRepository $extinctionRepository,
        Request $request
    ): Response {
        $searchParams = [
            'zone' => $request->query->get('zone', ''),
            'numerotation' => $request->query->get('numerotation', ''),
        ];

        $extinctions = $extinctionRepository->searchExtinctionsRAM($searchParams, 1000, 0);

        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);

        $html = $this->renderView('equipements/extinction_ram/pdf_etat.html.twig', [
            'extinctions' => $extinctions,
            'search_params' => $searchParams,
            'date_export' => new \DateTime(),
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="extinction_ram_' . date('Y-m-d_His') . '.pdf"'
            ]
        );
    }

    /**
     * Export Excel - Issues de Secours
     */
    #[Route('/issues-secours/export-excel', name: 'app_equipements_issues_secours_export_excel')]
    public function exportIssuesSecoursExcel(
        IssueSecoursRepository $issueRepository,
        Request $request
    ): Response {
        $searchParams = [
            'zone' => $request->query->get('zone', ''),
            'numerotation' => $request->query->get('numerotation', ''),
        ];

        $issues = $issueRepository->searchIssuesSecours($searchParams, 1000, 0);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Issues de Secours');

        // En-têtes
        $sheet->setCellValue('A1', 'Numéro');
        $sheet->setCellValue('B1', 'Zone');
        $sheet->setCellValue('C1', 'Type');
        $sheet->setCellValue('D1', 'Barre Antipanique');
        $sheet->setCellValue('E1', 'Statut');
        $sheet->setCellValue('F1', 'Dernière Inspection');

        // Style en-têtes
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ]
        ]);

        // Données
        $row = 2;
        foreach ($issues as $issue) {
            $sheet->setCellValue('A' . $row, $issue->getNumerotation());
            $sheet->setCellValue('B' . $row, $issue->getZone());
            $sheet->setCellValue('C' . $row, $issue->getType() ?? '-');
            $sheet->setCellValue('D' . $row, $issue->getBarreAntipanique() ?? '-');
            $sheet->setCellValue('E' . $row, $issue->getStatutConformite());
            
            $derniereInspection = $issue->getDerniereInspection();
            $sheet->setCellValue('F' . $row, $derniereInspection ? $derniereInspection->getDateInspection()->format('d/m/Y') : '-');

            // Coloration conditionnelle
            if ($issue->getStatutConformite() == 'Conforme') {
                $sheet->getStyle('E' . $row)->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C6EFCE']],
                    'font' => ['color' => ['rgb' => '006100']]
                ]);
            } elseif ($issue->getStatutConformite() == 'Non conforme') {
                $sheet->getStyle('E' . $row)->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC7CE']],
                    'font' => ['color' => ['rgb' => '9C0006']]
                ]);
            }

            $row++;
        }

        // Auto-size
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Réponse
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $response = new \Symfony\Component\HttpFoundation\Response();
        
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        $filename = 'issues_secours_' . date('Y-m-d_His') . '.xlsx';
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->setContent($content);

        return $response;
    }

    /**
     * Export PDF - Issues de Secours
     */
    #[Route('/issues-secours/export-pdf', name: 'app_equipements_issues_secours_export_pdf')]
    public function exportIssuesSecoursPDF(
        IssueSecoursRepository $issueRepository,
        Request $request
    ): Response {
        $searchParams = [
            'zone' => $request->query->get('zone', ''),
            'numerotation' => $request->query->get('numerotation', ''),
        ];

        $issues = $issueRepository->searchIssuesSecours($searchParams, 1000, 0);

        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);

        $html = $this->renderView('equipements/issues_secours/pdf_etat.html.twig', [
            'issues' => $issues,
            'search_params' => $searchParams,
            'date_export' => new \DateTime(),
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="issues_secours_' . date('Y-m-d_His') . '.pdf"'
            ]
        );
    }

    /**
     * Export Excel - Monte-charge
     */
    #[Route('/monte-charge/export-excel', name: 'app_equipements_monte_charge_export_excel')]
    public function exportMonteChargeExcel(
        MonteChargeRepository $monteChargeRepository,
        InspectionMonteChargeRepository $inspectionRepository
    ): Response {
        $monteCharges = $monteChargeRepository->findAll();
        $inspections = $inspectionRepository->findBy([], ['dateInspection' => 'DESC']);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Monte-charge');

        // En-têtes
        $sheet->setCellValue('A1', 'Type');
        $sheet->setCellValue('B1', 'Zone');
        $sheet->setCellValue('C1', 'Numéro Porte');
        $sheet->setCellValue('D1', 'Date Inspection');
        $sheet->setCellValue('E1', 'Inspecteur');
        $sheet->setCellValue('F1', 'Résultat');
        $sheet->setCellValue('G1', 'Observations');

        // Style en-têtes
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ]
        ]);

        // Données
        $row = 2;
        foreach ($inspections as $inspection) {
            $sheet->setCellValue('A' . $row, $inspection->getMonteCharge()->getType());
            $sheet->setCellValue('B' . $row, $inspection->getMonteCharge()->getZone());
            $sheet->setCellValue('C' . $row, $inspection->getNumeroPorte());
            $sheet->setCellValue('D' . $row, $inspection->getDateInspection()->format('d/m/Y H:i'));
            $sheet->setCellValue('E' . $row, $inspection->getInspectePar()->getFullName());
            $sheet->setCellValue('F' . $row, $inspection->isValide() ? 'Conforme' : 'Non conforme');
            $sheet->setCellValue('G' . $row, $inspection->getObservations() ?? '-');

            // Coloration conditionnelle
            if ($inspection->isValide()) {
                $sheet->getStyle('F' . $row)->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C6EFCE']],
                    'font' => ['color' => ['rgb' => '006100']]
                ]);
            } else {
                $sheet->getStyle('F' . $row)->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC7CE']],
                    'font' => ['color' => ['rgb' => '9C0006']]
                ]);
            }

            $row++;
        }

        // Auto-size
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Réponse
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $response = new \Symfony\Component\HttpFoundation\Response();
        
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        $filename = 'monte_charge_' . date('Y-m-d_His') . '.xlsx';
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->setContent($content);

        return $response;
    }

    /**
     * Export PDF - Monte-charge
     */
    #[Route('/monte-charge/export-pdf', name: 'app_equipements_monte_charge_export_pdf')]
    public function exportMonteChargePDF(
        MonteChargeRepository $monteChargeRepository,
        InspectionMonteChargeRepository $inspectionRepository
    ): Response {
        $monteCharges = $monteChargeRepository->findAll();
        $inspections = $inspectionRepository->findBy([], ['dateInspection' => 'DESC']);

        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);

        $html = $this->renderView('equipements/monte_charge/pdf_etat.html.twig', [
            'monte_charges' => $monteCharges,
            'inspections' => $inspections,
            'date_export' => new \DateTime(),
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="monte_charge_' . date('Y-m-d_His') . '.pdf"'
            ]
        );
    }

    /**
     * Export Excel - Prises Pompiers
     */
    #[Route('/prises-pompiers/export-excel', name: 'app_equipements_prises_pompiers_export_excel')]
    public function exportPrisesPompiersExcel(
        PrisePompierRepository $prisePompierRepository,
        Request $request
    ): Response {
        $searchParams = [
            'zone' => $request->query->get('zone', ''),
            'emplacement' => $request->query->get('emplacement', ''),
        ];

        $prises = $prisePompierRepository->searchPrisesPompiers($searchParams, 1000, 0);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Prises Pompiers');

        // En-têtes
        $sheet->setCellValue('A1', 'Zone');
        $sheet->setCellValue('B1', 'Emplacement');
        $sheet->setCellValue('C1', 'Diamètre');
        $sheet->setCellValue('D1', 'Statut');
        $sheet->setCellValue('E1', 'Dernière Inspection');

        // Style en-têtes
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ]
        ]);

        // Données
        $row = 2;
        foreach ($prises as $prise) {
            $sheet->setCellValue('A' . $row, $prise->getZone());
            $sheet->setCellValue('B' . $row, $prise->getEmplacement() ?? '-');
            $sheet->setCellValue('C' . $row, $prise->getDimatere() ?? '-');
            $sheet->setCellValue('D' . $row, $prise->getStatutConformite());
            
            $derniereInspection = $prise->getDerniereInspection();
            $sheet->setCellValue('E' . $row, $derniereInspection ? $derniereInspection->getDateInspection()->format('d/m/Y') : '-');

            // Coloration conditionnelle
            if ($prise->getStatutConformite() == 'Conforme') {
                $sheet->getStyle('D' . $row)->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C6EFCE']],
                    'font' => ['color' => ['rgb' => '006100']]
                ]);
            } elseif ($prise->getStatutConformite() == 'Non conforme') {
                $sheet->getStyle('D' . $row)->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC7CE']],
                    'font' => ['color' => ['rgb' => '9C0006']]
                ]);
            }

            $row++;
        }

        // Auto-size
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Réponse
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $response = new \Symfony\Component\HttpFoundation\Response();
        
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        $filename = 'prises_pompiers_' . date('Y-m-d_His') . '.xlsx';
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->setContent($content);

        return $response;
    }

    /**
     * Export PDF - Prises Pompiers
     */
    #[Route('/prises-pompiers/export-pdf', name: 'app_equipements_prises_pompiers_export_pdf')]
    public function exportPrisesPompiersPDF(
        PrisePompierRepository $prisePompierRepository,
        Request $request
    ): Response {
        $searchParams = [
            'zone' => $request->query->get('zone', ''),
            'emplacement' => $request->query->get('emplacement', ''),
        ];

        $prises = $prisePompierRepository->searchPrisesPompiers($searchParams, 1000, 0);

        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);

        $html = $this->renderView('equipements/prises_pompiers/pdf_etat.html.twig', [
            'prises' => $prises,
            'search_params' => $searchParams,
            'date_export' => new \DateTime(),
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="prises_pompiers_' . date('Y-m-d_His') . '.pdf"'
            ]
        );
    }

    /**
     * Export Excel - Sirènes
     */
    #[Route('/sirenes/export-excel', name: 'app_equipements_sirenes_export_excel')]
    public function exportSirenesExcel(
        SireneRepository $sireneRepository,
        Request $request
    ): Response {
        $searchParams = [
            'zone' => $request->query->get('zone', ''),
            'numerotation' => $request->query->get('numerotation', ''),
        ];

        $sirenes = $sireneRepository->searchSirenes($searchParams, 1000, 0);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sirènes');

        // En-têtes
        $sheet->setCellValue('A1', 'Numéro');
        $sheet->setCellValue('B1', 'Zone');
        $sheet->setCellValue('C1', 'Emplacement');
        $sheet->setCellValue('D1', 'Type');
        $sheet->setCellValue('E1', 'Statut');
        $sheet->setCellValue('F1', 'Dernière Inspection');

        // Style en-têtes
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ]
        ]);

        // Données
        $row = 2;
        foreach ($sirenes as $sirene) {
            $sheet->setCellValue('A' . $row, $sirene->getNumerotation());
            $sheet->setCellValue('B' . $row, $sirene->getZone());
            $sheet->setCellValue('C' . $row, $sirene->getEmplacement() ?? '-');
            $sheet->setCellValue('D' . $row, $sirene->getType() ?? '-');
            $sheet->setCellValue('E' . $row, $sirene->getStatutConformite());
            
            $derniereInspection = $sirene->getDerniereInspection();
            $sheet->setCellValue('F' . $row, $derniereInspection ? $derniereInspection->getDateInspection()->format('d/m/Y') : '-');

            // Coloration conditionnelle
            if ($sirene->getStatutConformite() == 'Conforme') {
                $sheet->getStyle('E' . $row)->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C6EFCE']],
                    'font' => ['color' => ['rgb' => '006100']]
                ]);
            } elseif ($sirene->getStatutConformite() == 'Non conforme') {
                $sheet->getStyle('E' . $row)->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC7CE']],
                    'font' => ['color' => ['rgb' => '9C0006']]
                ]);
            }

            $row++;
        }

        // Auto-size
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Réponse
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $response = new \Symfony\Component\HttpFoundation\Response();
        
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        $filename = 'sirenes_' . date('Y-m-d_His') . '.xlsx';
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->setContent($content);

        return $response;
    }

    /**
     * Export PDF - Sirènes
     */
    #[Route('/sirenes/export-pdf', name: 'app_equipements_sirenes_export_pdf')]
    public function exportSirenesPDF(
        SireneRepository $sireneRepository,
        Request $request
    ): Response {
        $searchParams = [
            'zone' => $request->query->get('zone', ''),
            'numerotation' => $request->query->get('numerotation', ''),
        ];

        $sirenes = $sireneRepository->searchSirenes($searchParams, 1000, 0);

        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);

        $html = $this->renderView('equipements/sirenes/pdf_etat.html.twig', [
            'sirenes' => $sirenes,
            'search_params' => $searchParams,
            'date_export' => new \DateTime(),
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="sirenes_' . date('Y-m-d_His') . '.pdf"'
            ]
        );
    }
}
