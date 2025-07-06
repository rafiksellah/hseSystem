<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\RapportHSE;
use App\Form\UserProfileType;
use App\Form\UserRapportHSEType;
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
        $user = $this->getUser();

        // Statistiques de l'utilisateur
        $totalRapports = $rapportHSERepository->count(['user' => $user]);
        $rapportsEnCours = $rapportHSERepository->count(['user' => $user, 'statut' => 'En cours']);
        $rapportsClotures = $rapportHSERepository->count(['user' => $user, 'statut' => 'Clôturé']);

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

        /** 
         * @var User $user
         */
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
        $user = $this->getUser();
        $page = $request->query->getInt('page', 1);
        $limit = 10;

        // Paramètres de recherche pour l'utilisateur connecté
        $searchParams = [
            'user' => $user,
            'zone' => $request->query->get('zone', ''),
            'dateDebut' => $request->query->get('dateDebut', ''),
            'dateFin' => $request->query->get('dateFin', ''),
            'statut' => $request->query->get('statut', ''),
        ];

        $rapports = $rapportHSERepository->searchRapportsForUser(
            $searchParams,
            $limit,
            ($page - 1) * $limit
        );

        $totalRapports = $rapportHSERepository->countSearchRapportsForUser($searchParams);
        $totalPages = ceil($totalRapports / $limit);

        return $this->render('user/rapports.html.twig', [
            'rapports' => $rapports,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'search_params' => $searchParams,
        ]);
    }

    #[Route('/rapport/{id}', name: 'app_user_rapport_detail', requirements: ['id' => '\d+'])]
    public function detailRapport(RapportHSE $rapport): Response
    {
        // Vérifier que le rapport appartient à l'utilisateur connecté
        if ($rapport->getUser() !== $this->getUser()) {
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
        // Vérifier que le rapport appartient à l'utilisateur connecté
        if ($rapport->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce rapport.');
        }

        // Vérifier que le rapport peut être modifié (pas encore clôturé)
        if ($rapport->getStatut() === 'Clôturé') {
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
                $rapport->setStatut('Clôturé');
            } else {
                $rapport->setStatut('En cours');
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
        $user = $this->getUser();

        // Statistiques détaillées
        $stats = [
            'total' => $rapportHSERepository->count(['user' => $user]),
            'en_cours' => $rapportHSERepository->count(['user' => $user, 'statut' => 'En cours']),
            'clotures' => $rapportHSERepository->count(['user' => $user, 'statut' => 'Clôturé']),
            'ce_mois' => $rapportHSERepository->getRapportsCeMoisPourUtilisateur($user),
            'cette_annee' => $rapportHSERepository->getRapportsCetteAnneePourUtilisateur($user),
        ];

        // Rapports par zone
        $rapportsParZone = $rapportHSERepository->getRapportsParZonePourUtilisateur($user);

        // Évolution mensuelle (12 derniers mois)
        $evolutionMensuelle = $rapportHSERepository->getRapportsParMoisPourUtilisateur($user, 12);

        return $this->render('user/statistiques.html.twig', [
            'stats' => $stats,
            'rapports_par_zone' => $rapportsParZone,
            'evolution_mensuelle' => $evolutionMensuelle,
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
                $rapport->setStatut('Clôturé');
            } else {
                $rapport->setStatut('En cours');
            }

            $entityManager->persist($rapport);
            $entityManager->flush();

            $this->addFlash('success', 'Votre rapport HSE a été créé avec succès !');
            return $this->redirectToRoute('app_user_rapports');
        }

        return $this->render('user/nouveau_rapport.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }
}
