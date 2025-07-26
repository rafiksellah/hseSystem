<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Entity\RapportHSE;
use App\Repository\UserRepository;
use App\Repository\RapportHSERepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/super-admin')]
#[IsGranted('ROLE_SUPER_ADMIN')]
class SuperAdminController extends AbstractController
{
    #[Route('/', name: 'app_super_admin_dashboard')]
    public function dashboard(
        RapportHSERepository $rapportRepository,
        UserRepository $userRepository
    ): Response {
        // Statistiques globales pour le super admin
        $totalRapports = $rapportRepository->count([]);
        $rapportsOuverts = $rapportRepository->count(['statut' => 'En cours']);
        $rapportsClotures = $rapportRepository->count(['statut' => 'Clôturé']);
        $rapportsRecents = $rapportRepository->findBy([], ['dateCreation' => 'DESC'], 10);

        // Statistiques utilisateurs
        $totalUsers = $userRepository->count([]);
        $usersSIMTIS = $userRepository->count(['zone' => 'SIMTIS']);
        $usersSIMTISTISSAGE = $userRepository->count(['zone' => 'SIMTIS TISSAGE']);
        $admins = $userRepository->findUsersByRole('ROLE_ADMIN');
        $superAdmins = $userRepository->findUsersByRole('ROLE_SUPER_ADMIN');

        // Statistiques par zone
        $rapportsSIMTIS = $rapportRepository->count(['zoneUtilisateur' => 'SIMTIS']);
        $rapportsSIMTISTISSAGE = $rapportRepository->count(['zoneUtilisateur' => 'SIMTIS TISSAGE']);

        return $this->render('super_admin/dashboard.html.twig', [
            'total_rapports' => $totalRapports,
            'rapports_ouverts' => $rapportsOuverts,
            'rapports_clotures' => $rapportsClotures,
            'rapports_recents' => $rapportsRecents,
            'total_users' => $totalUsers,
            'users_simtis' => $usersSIMTIS,
            'users_simtis_tissage' => $usersSIMTISTISSAGE,
            'admins' => $admins,
            'super_admins' => $superAdmins,
            'rapports_simtis' => $rapportsSIMTIS,
            'rapports_simtis_tissage' => $rapportsSIMTISTISSAGE,
        ]);
    }

    #[Route('/users', name: 'app_super_admin_users')]
    public function listUsers(UserRepository $userRepository, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $zone = $request->query->get('zone', '');
        $role = $request->query->get('role', '');
        $page = $request->query->getInt('page', 1);
        $limit = 15;

        $searchParams = [
            'search' => $search,
            'zone' => $zone,
            'role' => $role
        ];

        if ($search || $zone || $role) {
            $users = $userRepository->searchUsersAdvanced($searchParams, $limit, ($page - 1) * $limit);
            $totalUsers = $userRepository->countSearchUsersAdvanced($searchParams);
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

        return $this->render('super_admin/users.html.twig', [
            'users' => $users,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'search_params' => $searchParams,
            'zones_disponibles' => User::ZONES_DISPONIBLES,
        ]);
    }

    #[Route('/user/nouveau', name: 'app_super_admin_user_nouveau')]
    public function nouveauUser(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response {
        $user = new User();

        $form = $this->createForm(UserType::class, $user, [
            'is_edit' => false,
            'is_super_admin' => true
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $roles = $user->getRoles();

            // Vérifier la zone seulement pour les non-super-admins
            if (!in_array('ROLE_SUPER_ADMIN', $roles) && !$user->getZone()) {
                $this->addFlash('error', 'La zone doit être sélectionnée pour les utilisateurs et administrateurs.');
                return $this->render('super_admin/nouveau_user.html.twig', [
                    'form' => $form,
                ]);
            }

            // Encoder le mot de passe
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $plainPassword)
            );

            // Définir la date et l'heure de création
            $user->setDateCreation(new \DateTime());
            $user->setHeureCreation(new \DateTime());

            $entityManager->persist($user);
            $entityManager->flush();

            $roleDisplay = '';
            if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
                $roleDisplay = 'Super Administrateur';
            } elseif (in_array('ROLE_ADMIN', $user->getRoles())) {
                $roleDisplay = 'Administrateur';
            } else {
                $roleDisplay = 'Utilisateur';
            }

            $zoneMessage = $user->getZone() ? 'dans la zone ' . $user->getZone() : 'avec accès global';

            $this->addFlash(
                'success',
                'L\'utilisateur ' . $user->getFullName() . ' (' . $roleDisplay . ') ' .
                    'a été créé avec succès ' . $zoneMessage . ' !'
            );

            return $this->redirectToRoute('app_super_admin_users');
        }

        return $this->render('super_admin/nouveau_user.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/user/{id}/modifier', name: 'app_super_admin_user_modifier', requirements: ['id' => '\d+'])]
    public function modifierUser(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response {
        $form = $this->createForm(UserType::class, $user, [
            'is_edit' => true,
            'is_super_admin' => true
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $roles = $user->getRoles();

            // Vérifier la zone seulement pour les non-super-admins
            if (!in_array('ROLE_SUPER_ADMIN', $roles) && !$user->getZone()) {
                $this->addFlash('error', 'La zone doit être sélectionnée pour les utilisateurs et administrateurs.');
                return $this->render('super_admin/modifier_user.html.twig', [
                    'form' => $form,
                    'user' => $user,
                ]);
            }

            // Encoder le nouveau mot de passe seulement s'il a été fourni
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword($user, $plainPassword)
                );
            }

            $entityManager->flush();

            $this->addFlash('success', 'L\'utilisateur ' . $user->getFullName() . ' a été modifié avec succès !');
            return $this->redirectToRoute('app_super_admin_users');
        }

        return $this->render('super_admin/modifier_user.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/user/{id}/details', name: 'app_super_admin_user_details', requirements: ['id' => '\d+'])]
    public function detailsUser(User $user): Response
    {
        // Afficher les détails de l'utilisateur
        return $this->render('super_admin/details_user.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/user/{id}/supprimer', name: 'app_super_admin_user_supprimer', requirements: ['id' => '\d+'])]
    public function supprimerUser(
        User $user,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        // Empêcher l'auto-suppression
        if ($user->getId() === $this->getUser()->getId()) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            return $this->redirectToRoute('app_super_admin_users');
        }

        // Vérifier s'il y a des données liées (rapports HSE)
        if ($user->getRapportsHSE()->count() > 0) {
            $this->addFlash(
                'error',
                'Impossible de supprimer cet utilisateur car il a des rapports HSE associés. ' .
                    'Vous devez d\'abord supprimer ou réassigner ses rapports.'
            );
            return $this->redirectToRoute('app_super_admin_users');
        }

        try {
            $userName = $user->getFullName();
            $userZone = $user->getZone();
            $userRole = '';

            if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
                $userRole = 'Super Administrateur';
            } elseif (in_array('ROLE_ADMIN', $user->getRoles())) {
                $userRole = 'Administrateur';
            } else {
                $userRole = 'Utilisateur';
            }

            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'L\'utilisateur ' . $userName . ' (' . $userRole . ' - Zone: ' . $userZone . ') a été supprimé avec succès.'
            );
        } catch (\Exception $e) {
            $this->addFlash(
                'error',
                'Une erreur est survenue lors de la suppression de l\'utilisateur : ' . $e->getMessage()
            );
        }

        return $this->redirectToRoute('app_super_admin_users');
    }

    #[Route('/rapports', name: 'app_super_admin_rapports')]
    public function listRapports(RapportHSERepository $rapportHSERepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 15;

        // Paramètres de recherche étendus pour super admin
        $searchParams = [
            'codeAgt' => $request->query->get('codeAgt', ''),
            'nom' => $request->query->get('nom', ''),
            'zone' => $request->query->get('zone', ''),
            'zoneUtilisateur' => $request->query->get('zoneUtilisateur', ''),
            'dateDebut' => $request->query->get('dateDebut', ''),
            'dateFin' => $request->query->get('dateFin', ''),
            'dateClotureDebut' => $request->query->get('dateClotureDebut', ''),
            'dateClotureFin' => $request->query->get('dateClotureFin', ''),
            'statut' => $request->query->get('statut', ''),
        ];

        // Recherche avec filtres (super admin voit tout)
        $rapports = $rapportHSERepository->searchRapportSuperAdmin(
            $searchParams,
            $limit,
            ($page - 1) * $limit
        );

        $totalRapports = $rapportHSERepository->countSearchRapportSuperAdmin($searchParams);
        $totalPages = ceil($totalRapports / $limit);

        return $this->render('super_admin/rapports.html.twig', [
            'rapports' => $rapports,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'search_params' => $searchParams,
            'zones_disponibles' => RapportHSE::getAllZones(),
            'zones_utilisateurs' => User::ZONES_DISPONIBLES,
        ]);
    }

    #[Route('/statistiques', name: 'app_super_admin_statistiques')]
    public function statistiques(
        RapportHSERepository $rapportRepository,
        UserRepository $userRepository
    ): Response {
        // Statistiques détaillées pour le super admin
        $stats = [
            'rapports_par_zone' => $rapportRepository->getRapportsParZoneUtilisateur(),
            'rapports_par_mois' => $rapportRepository->getRapportsParMois(12),
            'rapports_par_statut' => $rapportRepository->getRapportsParStatut(),
            'utilisateurs_par_zone' => $userRepository->getUsersParZone(),
            'evolution_utilisateurs' => $userRepository->getEvolutionUtilisateurs(12),
            'top_zones_actives' => $rapportRepository->getTopZonesActives(10),
        ];

        return $this->render('super_admin/statistiques.html.twig', [
            'stats' => $stats,
        ]);
    }

    #[Route('/logs', name: 'app_super_admin_logs')]
    public function logs(): Response
    {
        // Page pour voir les logs système (optionnel)
        return $this->render('super_admin/logs.html.twig');
    }

    #[Route('/parametres', name: 'app_super_admin_parametres')]
    public function parametres(): Response
    {
        // Page pour gérer les paramètres globaux du système (optionnel)
        return $this->render('super_admin/parametres.html.twig');
    }
}
