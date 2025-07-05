<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Entity\RapportHSE;
use App\Form\RapportHSEType;
use App\Repository\UserRepository;
use App\Repository\RapportHSERepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_dashboard')]
    public function dashboard(
        UserRepository $userRepository,
        RapportHSERepository $rapportHSERepository
    ): Response {
        $totalUsers = $userRepository->count([]);
        $totalRapports = $rapportHSERepository->count([]);
        $rapportsEnCours = $rapportHSERepository->count(['statut' => 'En cours']);
        $rapportsClotures = $rapportHSERepository->count(['statut' => 'Clôturé']);

        // Derniers rapports créés
        $derniers_rapports = $rapportHSERepository->findBy(
            [],
            ['dateCreation' => 'DESC'],
            5
        );

        return $this->render('admin/dashboard.html.twig', [
            'total_users' => $totalUsers,
            'total_rapports' => $totalRapports,
            'rapports_en_cours' => $rapportsEnCours,
            'rapports_clotures' => $rapportsClotures,
            'derniers_rapports' => $derniers_rapports,
        ]);
    }

    #[Route('/users', name: 'app_admin_users')]
    public function listUsers(UserRepository $userRepository, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $page = $request->query->getInt('page', 1);
        $limit = 10;

        if ($search) {
            $users = $userRepository->searchUsers($search, $limit, ($page - 1) * $limit);
            $totalUsers = $userRepository->countSearchUsers($search);
        } else {
            $users = $userRepository->findBy(
                [],
                ['dateCreation' => 'DESC'],
                $limit,
                ($page - 1) * $limit
            );
            $totalUsers = $userRepository->count([]);
        }

        $totalPages = ceil($totalUsers / $limit);

        return $this->render('admin/users.html.twig', [
            'users' => $users,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'search' => $search,
        ]);
    }

    #[Route('/user/nouveau', name: 'app_admin_user_nouveau')]
    public function nouveauUser(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encoder le mot de passe
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'L\'utilisateur a été créé avec succès !');
            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('admin/nouveau_user.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/user/{id}/modifier', name: 'app_admin_user_modifier', requirements: ['id' => '\d+'])]
    public function modifierUser(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response {
        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encoder le nouveau mot de passe seulement s'il a été fourni
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword($user, $plainPassword)
                );
            }

            $entityManager->flush();

            $this->addFlash('success', 'L\'utilisateur a été modifié avec succès !');
            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('admin/modifier_user.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/rapport/nouveau', name: 'app_admin_rapport_nouveau')]
    public function nouveauRapport(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $rapport = new RapportHSE();

        // Initialiser la date et l'heure actuelles par défaut
        $rapport->setDate(new \DateTime());
        $rapport->setHeure(new \DateTime());

        $form = $this->createForm(RapportHSEType::class, $rapport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer l'utilisateur sélectionné
            $user = $form->get('user')->getData();
            if (!$user) {
                $this->addFlash('error', 'Veuillez sélectionner un agent !');
                return $this->render('admin/nouveau_rapport.html.twig', [
                    'form' => $form,
                ]);
            }

            // Associer l'utilisateur au rapport
            $rapport->setUser($user);

            // Remplir automatiquement les champs depuis l'utilisateur sélectionné
            $rapport->setCodeAgt($user->getCodeAgent());
            $rapport->setNom($user->getNom() . ' ' . $user->getPrenom());

            // Utiliser la date et l'heure de création de l'utilisateur
            if ($user->getDateCreation()) {
                $rapport->setDate($user->getDateCreation());
            }
            if ($user->getHeureCreation()) {
                $rapport->setHeure($user->getHeureCreation());
            }

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

            $this->addFlash('success', 'Le rapport HSE a été créé avec succès !');
            return $this->redirectToRoute('app_admin_rapports');
        }

        return $this->render('admin/nouveau_rapport.html.twig', [
            'form' => $form,
        ]);
    }

    // Votre méthode getUserData reste inchangée
    #[Route('/get-user-data', name: 'app_admin_get_user_data', methods: ['POST'])]
    public function getUserData(Request $request, UserRepository $userRepository): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => false, 'message' => 'Requête non valide'], 400);
        }

        $data = json_decode($request->getContent(), true);

        // Accepter soit userId soit codeAgent
        $userId = $data['userId'] ?? '';
        $codeAgent = $data['codeAgent'] ?? '';

        if (empty($userId) && empty($codeAgent)) {
            return new JsonResponse(['success' => false, 'message' => 'ID utilisateur ou code agent manquant'], 400);
        }

        // Rechercher par ID ou par code agent
        if (!empty($userId)) {
            $user = $userRepository->find($userId);
        } else {
            $user = $userRepository->findOneBy(['codeAgent' => $codeAgent]);
        }

        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Utilisateur introuvable'], 404);
        }

        return new JsonResponse([
            'success' => true,
            'codeAgent' => $user->getCodeAgent(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'nomComplet' => $user->getNom() . ' ' . $user->getPrenom(),
            'dateCreation' => $user->getDateCreation() ? $user->getDateCreation()->format('Y-m-d') : null,
            'heureCreation' => $user->getHeureCreation() ? $user->getHeureCreation()->format('H:i:s') : null
        ]);
    }

    #[Route('/rapports', name: 'app_admin_rapports')]
    public function listRapports(RapportHSERepository $rapportHSERepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 10;

        // Paramètres de recherche
        $searchParams = [
            'codeAgt' => $request->query->get('codeAgt', ''),
            'nom' => $request->query->get('nom', ''),
            'zone' => $request->query->get('zone', ''),
            'dateDebut' => $request->query->get('dateDebut', ''),
            'dateFin' => $request->query->get('dateFin', ''),
            'dateClotureDebut' => $request->query->get('dateClotureDebut', ''),
            'dateClotureFin' => $request->query->get('dateClotureFin', ''),
            'statut' => $request->query->get('statut', ''),
        ];

        // Recherche avec filtres
        $rapports = $rapportHSERepository->searchRapports(
            $searchParams,
            $limit,
            ($page - 1) * $limit
        );

        $totalRapports = $rapportHSERepository->countSearchRapports($searchParams);
        $totalPages = ceil($totalRapports / $limit);

        return $this->render('admin/rapports.html.twig', [
            'rapports' => $rapports,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'search_params' => $searchParams,
        ]);
    }

    #[Route('/rapport/{id}', name: 'app_admin_rapport_detail', requirements: ['id' => '\d+'])]
    public function detailRapport(RapportHSE $rapport): Response
    {
        return $this->render('admin/detail_rapport.html.twig', [
            'rapport' => $rapport,
        ]);
    }

    #[Route('/rapport/{id}/modifier', name: 'app_admin_rapport_modifier', requirements: ['id' => '\d+'])]
    public function modifierRapport(
        RapportHSE $rapport,
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        UserRepository $userRepository
    ): Response {
        $form = $this->createForm(RapportHSEType::class, $rapport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer l'utilisateur par son code agent
            $codeAgent = $form->get('codeAgt')->getData();
            $user = $userRepository->findOneBy(['codeAgent' => $codeAgent]);

            if (!$user) {
                $this->addFlash('error', 'Code agent introuvable !');
                return $this->render('admin/modifier_rapport.html.twig', [
                    'form' => $form,
                    'rapport' => $rapport,
                ]);
            }

            // Associer l'utilisateur au rapport
            $rapport->setUser($user);

            // Remplir automatiquement le nom depuis l'utilisateur
            $rapport->setNom($user->getNom() . ' ' . $user->getPrenom());

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

            // Mettre à jour le statut
            if ($rapport->getActionCloturee() === 'oui') {
                $rapport->setStatut('Clôturé');
            } else {
                $rapport->setStatut('En cours');
            }

            $entityManager->flush();

            $this->addFlash('success', 'Le rapport HSE a été modifié avec succès !');
            return $this->redirectToRoute('app_admin_rapport_detail', ['id' => $rapport->getId()]);
        }

        return $this->render('admin/modifier_rapport.html.twig', [
            'form' => $form,
            'rapport' => $rapport,
        ]);
    }

    #[Route('/rapport/{id}/supprimer', name: 'app_admin_rapport_supprimer', requirements: ['id' => '\d+'])]
    public function supprimerRapport(
        RapportHSE $rapport,
        EntityManagerInterface $entityManager
    ): Response {
        $entityManager->remove($rapport);
        $entityManager->flush();

        $this->addFlash('success', 'Le rapport HSE a été supprimé avec succès !');
        return $this->redirectToRoute('app_admin_rapports');
    }

    #[Route('/user/{id}/supprimer', name: 'app_admin_user_supprimer', requirements: ['id' => '\d+'])]
    public function supprimerUser(
        User $user,
        EntityManagerInterface $entityManager
    ): Response {
        try {
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'L\'utilisateur a été supprimé avec succès !');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Impossible de supprimer cet utilisateur car il a des rapports associés.');
        }

        return $this->redirectToRoute('app_admin_users');
    }
}
