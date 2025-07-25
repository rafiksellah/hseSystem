<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\RapportHSE;
use App\Form\UserProfileType;
use App\Form\UserRapportHSEType;
use App\Service\ExcelExportService;
use App\Repository\RapportHSERepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/user')]
#[IsGranted('ROLE_USER')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_dashboard')]
    public function dashboard(RapportHSERepository $rapportHSERepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Statistiques de l'utilisateur
        $totalRapports = $rapportHSERepository->count(['user' => $user]);
        $rapportsEnCours = $rapportHSERepository->count(['user' => $user, 'statut' => RapportHSE::STATUT_EN_COURS]);
        $rapportsClotures = $rapportHSERepository->count(['user' => $user, 'statut' => RapportHSE::STATUT_CLOTURE]);

        // Derniers rapports de l'utilisateur
        $derniers_rapports = $rapportHSERepository->findBy(
            ['user' => $user],
            ['dateCreation' => 'DESC'],
            5
        );

        // Graphique des rapports par mois (6 derniers mois)
        $rapportsParMois = $rapportHSERepository->getRapportsParMoisPourUtilisateur($user, 6);

        return $this->render('user/dashboard.html.twig', [
            'user' => $user,
            'total_rapports' => $totalRapports,
            'rapports_en_cours' => $rapportsEnCours,
            'rapports_clotures' => $rapportsClotures,
            'derniers_rapports' => $derniers_rapports,
            'rapports_par_mois' => $rapportsParMois,
        ]);
    }

    #[Route('/profil', name: 'app_user_profil')]
    public function profil(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response {

        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si un nouveau mot de passe a été fourni
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword($user, $plainPassword)
                );
            }

            $entityManager->flush();
            $this->addFlash('success', 'Votre profil a été mis à jour avec succès !');

            return $this->redirectToRoute('app_user_profil');
        }

        return $this->render('user/profil.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/rapports', name: 'app_user_rapports')]
    public function mesRapports(RapportHSERepository $rapportHSERepository, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Vérifier que l'utilisateur est bien connecté
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        $page = $request->query->getInt('page', 1);
        $limit = 10;

        // Paramètres de recherche pour l'utilisateur connecté UNIQUEMENT
        $searchParams = [
            'user' => $user, // Ceci garantit qu'on ne récupère que les rapports de cet utilisateur
            'zone' => $request->query->get('zone', ''),
            'dateDebut' => $request->query->get('dateDebut', ''),
            'dateFin' => $request->query->get('dateFin', ''),
            'statut' => $request->query->get('statut', ''),
        ];

        // Récupérer UNIQUEMENT les rapports de l'utilisateur connecté
        $rapports = $rapportHSERepository->searchRapportsForUser(
            $searchParams,
            $limit,
            ($page - 1) * $limit
        );

        $totalRapports = $rapportHSERepository->countSearchRapportsForUser($searchParams);
        $totalPages = ceil($totalRapports / $limit);

        // Obtenir les zones disponibles pour le filtre selon la zone de l'utilisateur
        $zonesDisponibles = RapportHSE::getZonesForUserZone($user->getZone());

        // Debug : vérifier que tous les rapports appartiennent bien à l'utilisateur connecté
        foreach ($rapports as $rapport) {
            if ($rapport->getUser()->getId() !== $user->getId()) {
                throw new \Exception('Erreur de sécurité : un rapport d\'un autre utilisateur a été trouvé !');
            }
        }

        return $this->render('user/rapports.html.twig', [
            'rapports' => $rapports,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'search_params' => $searchParams,
            'zones_disponibles' => $zonesDisponibles,
            'user_zone' => $user->getZone(),
            'user' => $user,
            'debug_user_id' => $user->getId(), // Pour debug dans le template
        ]);
    }

    #[Route('/rapport/{id}', name: 'app_user_rapport_detail', requirements: ['id' => '\d+'])]
    public function detailRapport(RapportHSE $rapport): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Vérifier que le rapport appartient à l'utilisateur connecté
        if ($rapport->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce rapport.');
        }

        return $this->render('user/detail_rapport.html.twig', [
            'rapport' => $rapport,
        ]);
    }

    #[Route('/rapport/{id}/modifier', name: 'app_user_rapport_modifier', requirements: ['id' => '\d+'])]
    public function modifierRapport(
        RapportHSE $rapport,
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        // Vérifier que le rapport appartient à l'utilisateur connecté
        if ($rapport->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce rapport.');
        }

        // Vérifier que le rapport peut être modifié (pas encore clôturé)
        if ($rapport->getStatut() === RapportHSE::STATUT_CLOTURE) {
            $this->addFlash('error', 'Ce rapport est déjà clôturé et ne peut plus être modifié.');
            return $this->redirectToRoute('app_user_rapport_detail', ['id' => $rapport->getId()]);
        }

        $form = $this->createForm(UserRapportHSEType::class, $rapport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload de la photo du constat
            $photoConstatFile = $form->get('photoConstatFile')->getData();
            if ($photoConstatFile) {
                // Supprimer l'ancienne photo si elle existe
                if ($rapport->getPhotoConstat()) {
                    $oldFile = $this->getParameter('uploads_directory') . '/' . $rapport->getPhotoConstat();
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                $originalFilename = pathinfo($photoConstatFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoConstatFile->guessExtension();

                try {
                    $photoConstatFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                    $rapport->setPhotoConstat($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de la photo du constat');
                }
            }

            // Gérer l'upload de la photo d'action
            $photoActionFile = $form->get('photoActionFile')->getData();
            if ($photoActionFile) {
                // Supprimer l'ancienne photo si elle existe
                if ($rapport->getPhotoActionCloturee()) {
                    $oldFile = $this->getParameter('uploads_directory') . '/' . $rapport->getPhotoActionCloturee();
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                $originalFilename = pathinfo($photoActionFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoActionFile->guessExtension();

                try {
                    $photoActionFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                    $rapport->setPhotoActionCloturee($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de la photo d\'action');
                }
            }

            // Définir le statut en fonction de l'action clôturée
            if ($rapport->getActionCloturee() === 'oui') {
                $rapport->setStatut(RapportHSE::STATUT_CLOTURE);
            } else {
                $rapport->setStatut(RapportHSE::STATUT_EN_COURS);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Votre rapport HSE a été modifié avec succès !');
            return $this->redirectToRoute('app_user_rapport_detail', ['id' => $rapport->getId()]);
        }

        return $this->render('user/modifier_rapport.html.twig', [
            'form' => $form,
            'rapport' => $rapport,
        ]);
    }

    #[Route('/statistiques', name: 'app_user_statistiques')]
    public function statistiques(RapportHSERepository $rapportHSERepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Statistiques détaillées
        $stats = [
            'total' => $rapportHSERepository->count(['user' => $user]),
            'en_cours' => $rapportHSERepository->count(['user' => $user, 'statut' => RapportHSE::STATUT_EN_COURS]),
            'clotures' => $rapportHSERepository->count(['user' => $user, 'statut' => RapportHSE::STATUT_CLOTURE]),
            'ce_mois' => $rapportHSERepository->getRapportsCeMoisPourUtilisateur($user),
            'cette_annee' => $rapportHSERepository->getRapportsCetteAnneePourUtilisateur($user),
        ];

        // Rapports par zone de travail (selon la zone de l'utilisateur)
        $rapportsParZone = $rapportHSERepository->getRapportsParZonePourUtilisateur($user);

        // Évolution mensuelle (12 derniers mois)
        $evolutionMensuelle = $rapportHSERepository->getRapportsParMoisPourUtilisateur($user, 12);

        // Obtenir les zones disponibles pour l'utilisateur
        $zonesDisponibles = RapportHSE::getZonesForUserZone($user->getZone());

        return $this->render('user/statistiques.html.twig', [
            'stats' => $stats,
            'rapports_par_zone' => $rapportsParZone,
            'evolution_mensuelle' => $evolutionMensuelle,
            'zones_disponibles' => $zonesDisponibles,
            'user_zone' => $user->getZone(),
        ]);
    }

    #[Route('/rapport/nouveau', name: 'app_user_rapport_nouveau')]
    public function nouveauRapport(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour créer un rapport.');
        }

        $rapport = new RapportHSE();

        // Pré-remplir les informations de l'utilisateur connecté
        $rapport->setUser($user);
        $rapport->setCodeAgt($user->getCodeAgent());
        $rapport->setNom($user->getNom() . ' ' . $user->getPrenom());
        $rapport->setDate(new \DateTime());
        $rapport->setHeure(new \DateTime());
        // La zone utilisateur sera automatiquement définie par le setter setUser() dans RapportHSE

        $form = $this->createForm(UserRapportHSEType::class, $rapport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload de la photo du constat
            $photoConstatFile = $form->get('photoConstatFile')->getData();
            if ($photoConstatFile) {
                $originalFilename = pathinfo($photoConstatFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoConstatFile->guessExtension();

                try {
                    $photoConstatFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                    $rapport->setPhotoConstat($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de la photo du constat');
                }
            }

            // Gérer l'upload de la photo d'action
            $photoActionFile = $form->get('photoActionFile')->getData();
            if ($photoActionFile) {
                $originalFilename = pathinfo($photoActionFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoActionFile->guessExtension();

                try {
                    $photoActionFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                    $rapport->setPhotoActionCloturee($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de la photo d\'action');
                }
            }

            // Définir le statut en fonction de l'action clôturée
            if ($rapport->getActionCloturee() === 'oui') {
                $rapport->setStatut(RapportHSE::STATUT_CLOTURE);
            } else {
                $rapport->setStatut(RapportHSE::STATUT_EN_COURS);
            }

            $entityManager->persist($rapport);
            $entityManager->flush();

            $this->addFlash('success', 'Votre rapport HSE a été créé avec succès !');
            return $this->redirectToRoute('app_user_rapports');
        }

        // Obtenir les zones disponibles pour l'aide dans le template
        $zonesDisponibles = RapportHSE::getZonesForUserZone($user->getZone());

        return $this->render('user/nouveau_rapport.html.twig', [
            'form' => $form,
            'user' => $user,
            'zones_disponibles' => $zonesDisponibles,
            'user_zone' => $user->getZone(),
        ]);
    }

    #[Route('/rapports/export', name: 'app_user_rapports_export')]
    public function exportMesRapports(
        RapportHSERepository $rapportRepository,
        ExcelExportService $excelExportService
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        // Récupérer seulement les rapports de l'utilisateur connecté
        $rapports = $rapportRepository->findBy(['user' => $user]);

        $filename = sprintf(
            'Mes_Rapports_HSE_%s_%s',
            $user->getZone(),
            date('Y-m-d')
        );

        return $excelExportService->exportRapportsHSE($rapports, $filename);
    }

    /**
     * Route pour obtenir les zones disponibles via AJAX (si nécessaire)
     */
    #[Route('/zones-disponibles', name: 'app_user_zones_disponibles', methods: ['GET'])]
    public function getZonesDisponibles(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non connecté'], 401);
        }

        $zones = RapportHSE::getZonesForUserZone($user->getZone());

        return $this->json([
            'zones' => $zones,
            'user_zone' => $user->getZone()
        ]);
    }
}