<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\RapportHSE;
use App\Form\UserProfileType;
use App\Repository\RapportHSERepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
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
}
