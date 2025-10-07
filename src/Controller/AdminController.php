<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Entity\RapportHSE;
use App\Form\RapportHSEType;
use App\Service\PdfExportService;
use App\Repository\UserRepository;
use App\Service\ExcelExportService;
use App\Service\PaginationService;
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
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_dashboard')]
    public function dashboard(RapportHSERepository $rapportRepository): Response
    {
        // Vérifier que l'utilisateur est connecté
        /** @var User $user */
        $user = $this->getUser();

        // Statistiques globales selon les permissions
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            // Super admin voit tout
            $totalRapports = $rapportRepository->count([]);
            $rapportsOuverts = $rapportRepository->count(['statut' => 'En cours']);
            $rapportsClotures = $rapportRepository->count(['statut' => 'Clôturé']);
            $rapportsRecents = $rapportRepository->findBy([], ['dateCreation' => 'DESC'], 5);
        } else {
            // Admin normal voit seulement sa zone
            $totalRapports = $rapportRepository->count(['zoneUtilisateur' => $user->getZone()]);
            $rapportsOuverts = $rapportRepository->count(['statut' => 'En cours', 'zoneUtilisateur' => $user->getZone()]);
            $rapportsClotures = $rapportRepository->count(['statut' => 'Clôturé', 'zoneUtilisateur' => $user->getZone()]);
            $rapportsRecents = $rapportRepository->findBy(['zoneUtilisateur' => $user->getZone()], ['dateCreation' => 'DESC'], 5);
        }

        return $this->render('admin/dashboard.html.twig', [
            'total_rapports' => $totalRapports,
            'rapports_ouverts' => $rapportsOuverts,
            'rapports_clotures' => $rapportsClotures,
            'rapports_recents' => $rapportsRecents,
            'user_zone' => $user->getZone(),
        ]);
    }

    #[Route('/users', name: 'app_admin_users')]
    public function listUsers(UserRepository $userRepository, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $page = $request->query->getInt('page', 1);
        $limit = 10;

        $currentUser = $this->getUser();
        $currentUserZone = null;
        $isAdmin = false;
        $isSuperAdmin = false;

        // Déterminer le type d'utilisateur et sa zone
        if ($currentUser) {
            $roles = $currentUser->getRoles();
            $isSuperAdmin = in_array('ROLE_SUPER_ADMIN', $roles);
            $isAdmin = in_array('ROLE_ADMIN', $roles) && !$isSuperAdmin;

            if ($isAdmin) {
                $currentUserZone = $currentUser->getZone();
            }
        }

        if ($search) {
            if ($isAdmin && $currentUserZone) {
                // Admin normal : recherche seulement dans sa zone
                $users = $userRepository->searchUsersByZone($search, $currentUserZone, $limit, ($page - 1) * $limit);
                $totalUsers = $userRepository->countSearchUsersByZone($search, $currentUserZone);
            } else {
                // Super admin : recherche dans toutes les zones
                $users = $userRepository->searchUsers($search, $limit, ($page - 1) * $limit);
                $totalUsers = $userRepository->countSearchUsers($search);
            }
        } else {
            if ($isAdmin && $currentUserZone) {
                // Admin normal : liste seulement sa zone
                $users = $userRepository->findBy(
                    ['zone' => $currentUserZone],
                    ['dateCreation' => 'DESC'],
                    $limit,
                    ($page - 1) * $limit
                );
                $totalUsers = $userRepository->count(['zone' => $currentUserZone]);
            } else {
                // Super admin : liste tous les utilisateurs
                $users = $userRepository->findBy(
                    [],
                    ['dateCreation' => 'DESC'],
                    $limit,
                    ($page - 1) * $limit
                );
                $totalUsers = $userRepository->count([]);
            }
        }

        $totalPages = ceil($totalUsers / $limit);

        return $this->render('admin/users.html.twig', [
            'users' => $users,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'search' => $search,
            'current_user_zone' => $currentUserZone,
            'is_super_admin' => $isSuperAdmin,
            'is_admin' => $isAdmin,
        ]);
    }

    #[Route('/users/export/excel', name: 'app_admin_users_export_excel')]
    public function exportUsersExcel(UserRepository $userRepository): Response
    {
        $currentUser = $this->getUser();
        $isSuperAdmin = $currentUser && in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles());
        $isAdmin = $currentUser && in_array('ROLE_ADMIN', $currentUser->getRoles()) && !$isSuperAdmin;

        // Récupérer les utilisateurs selon les permissions
        if ($isAdmin && $currentUser->getZone()) {
            $users = $userRepository->findBy(['zone' => $currentUser->getZone()], ['dateCreation' => 'DESC']);
            $filename = 'utilisateurs_' . strtolower(str_replace(' ', '_', $currentUser->getZone())) . '_' . date('Y-m-d') . '.xlsx';
        } else {
            $users = $userRepository->findBy([], ['dateCreation' => 'DESC']);
            $filename = 'utilisateurs_tous_' . date('Y-m-d') . '.xlsx';
        }

        // Créer le fichier Excel (vous devrez installer PhpSpreadsheet)
        // composer require phpoffice/phpspreadsheet

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // En-têtes
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Nom');
        $sheet->setCellValue('C1', 'Prénom');
        $sheet->setCellValue('D1', 'Email');
        $sheet->setCellValue('E1', 'Code Agent');
        $sheet->setCellValue('F1', 'Zone');
        $sheet->setCellValue('G1', 'Date de création');

        // Style pour les en-têtes
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');

        // Données
        $row = 2;
        foreach ($users as $user) {
            $sheet->setCellValue('A' . $row, $user->getId());
            $sheet->setCellValue('B' . $row, $user->getNom());
            $sheet->setCellValue('C' . $row, $user->getPrenom());
            $sheet->setCellValue('D' . $row, $user->getEmail());
            $sheet->setCellValue('E' . $row, $user->getCodeAgent());
            $sheet->setCellValue('F' . $row, $user->getZone());
            $sheet->setCellValue('G' . $row, $user->getDateCreation()->format('d/m/Y H:i'));
            $row++;
        }

        // Auto-size des colonnes
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Création du writer
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        // Réponse HTTP
        $response = new Response();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        $response->setContent($content);
        return $response;
    }

    #[Route('/users/export/pdf', name: 'app_admin_users_export_pdf')]
    public function exportUsersPdf(UserRepository $userRepository): Response
    {
        $currentUser = $this->getUser();
        $isSuperAdmin = $currentUser && in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles());
        $isAdmin = $currentUser && in_array('ROLE_ADMIN', $currentUser->getRoles()) && !$isSuperAdmin;

        // Récupérer les utilisateurs selon les permissions
        if ($isAdmin && $currentUser->getZone()) {
            $users = $userRepository->findBy(['zone' => $currentUser->getZone()], ['dateCreation' => 'DESC']);
            $title = 'Liste des utilisateurs - Zone ' . $currentUser->getZone();
            $filename = 'utilisateurs_' . strtolower(str_replace(' ', '_', $currentUser->getZone())) . '_' . date('Y-m-d') . '.pdf';
        } else {
            $users = $userRepository->findBy([], ['dateCreation' => 'DESC']);
            $title = 'Liste de tous les utilisateurs';
            $filename = 'utilisateurs_tous_' . date('Y-m-d') . '.pdf';
        }

        // Générer le HTML pour le PDF
        $html = $this->renderView('admin/users_pdf.html.twig', [
            'users' => $users,
            'title' => $title,
            'export_date' => new \DateTime(),
            'current_user' => $currentUser
        ]);

        // Utiliser DomPDF (composer require dompdf/dompdf)
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]
        );
    }

    #[Route('/user/nouveau', name: 'app_admin_user_nouveau')]
    public function nouveauUser(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response {
        $user = new User();
        $currentUser = $this->getUser();

        // Si c'est un admin (pas super admin), pré-remplir la zone avec celle de l'admin
        if ($currentUser && in_array('ROLE_ADMIN', $currentUser->getRoles()) && !in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles())) {
            $user->setZone($currentUser->getZone());
        }

        $form = $this->createForm(UserType::class, $user, ['is_edit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Retirer le dd() pour permettre la création
            // dd('Le site est en maintenance, veuillez réessayer plus tard.');

            // Forcer la zone si c'est un admin (sécurité supplémentaire)
            if ($currentUser && in_array('ROLE_ADMIN', $currentUser->getRoles()) && !in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles())) {
                $user->setZone($currentUser->getZone());
            }

            // Vérifier que la zone est bien définie
            if (!$user->getZone()) {
                $this->addFlash('error', 'La zone doit être sélectionnée.');
                return $this->render('admin/nouveau_user.html.twig', [
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

            // Assigner le rôle par défaut si pas déjà défini
            if (empty($user->getRoles()) || $user->getRoles() === ['ROLE_USER']) {
                $user->setRoles(['ROLE_USER']);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'L\'utilisateur a été créé avec succès dans la zone ' . $user->getZone() . ' !');
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
        // Vérifier les permissions de modification
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        // Si c'est un admin (pas super admin), il ne peut modifier que les utilisateurs de sa zone
        if ($currentUser && in_array('ROLE_ADMIN', $currentUser->getRoles()) && !in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles())) {
            if ($user->getZone() !== $currentUser->getZone()) {
                throw $this->createAccessDeniedException('Vous ne pouvez modifier que les utilisateurs de votre zone.');
            }
        }

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

    #[Route('/user/{id}/supprimer', name: 'app_admin_user_supprimer', requirements: ['id' => '\d+'])]
    public function supprimerUser(
        User $user,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        // Vérifier que c'est bien un super admin (double sécurité avec access_control)
        $currentUser = $this->getUser();
        if (!$currentUser || !in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles())) {
            throw $this->createAccessDeniedException('Seuls les super administrateurs peuvent supprimer des utilisateurs.');
        }

        // Empêcher l'auto-suppression
        if ($user->getId() === $currentUser->getId()) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            return $this->redirectToRoute('app_admin_users');
        }

        // Vérifier s'il y a des données liées (rapports HSE)
        if ($user->getRapportsHSE()->count() > 0) {
            $this->addFlash(
                'error',
                'Impossible de supprimer cet utilisateur car il a des rapports HSE associés. ' .
                    'Vous devez d\'abord supprimer ou réassigner ses rapports.'
            );
            return $this->redirectToRoute('app_admin_users');
        }

        try {
            $userName = $user->getFullName();
            $userZone = $user->getZone();

            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'L\'utilisateur ' . $userName . ' (Zone: ' . $userZone . ') a été supprimé avec succès.'
            );
        } catch (\Exception $e) {
            $this->addFlash(
                'error',
                'Une erreur est survenue lors de la suppression de l\'utilisateur : ' . $e->getMessage()
            );
        }

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/admin/rapport/nouveau', name: 'app_admin_rapport_nouveau')]
    public function nouveauRapport(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $rapport = new RapportHSE();
        // Vérifier que l'utilisateur est connecté
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        // Initialiser la date et l'heure actuelles par défaut
        $rapport->setDate(new \DateTime());
        $rapport->setHeure(new \DateTime());

        // Si l'utilisateur est un admin (pas super admin), pré-remplir avec ses informations
        if (in_array('ROLE_ADMIN', $currentUser->getRoles()) && !in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles())) {
            $rapport->setUser($currentUser);
            $rapport->setCodeAgt($currentUser->getCodeAgent());
            $rapport->setNom($currentUser->getNom() . ' ' . $currentUser->getPrenom());
            $rapport->setZoneUtilisateur($currentUser->getZone());
        }

        $form = $this->createForm(RapportHSEType::class, $rapport, [
            'is_admin_self_report' => in_array('ROLE_ADMIN', $currentUser->getRoles()) && !in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles())
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->get('user')->getData();

            // Si c'est un admin (pas super admin), forcer l'utilisateur à être lui-même
            if (in_array('ROLE_ADMIN', $currentUser->getRoles()) && !in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles())) {
                if ($user->getId() !== $currentUser->getId()) {
                    $this->addFlash('error', 'Vous ne pouvez créer des rapports que pour vous-même !');
                    return $this->render('admin/nouveau_rapport.html.twig', [
                        'form' => $form,
                    ]);
                }
            }

            if (!$user) {
                $this->addFlash('error', 'Veuillez sélectionner un agent !');
                return $this->render('admin/nouveau_rapport.html.twig', [
                    'form' => $form,
                ]);
            }

            // Vérifier les permissions pour les super admins
            if (in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles())) {
                // Super admin peut créer pour n'importe qui
            } else {
                // Admin normal : vérifier que c'est bien pour lui-même et de sa zone
                if ($user->getId() !== $currentUser->getId() || $user->getZone() !== $currentUser->getZone()) {
                    $this->addFlash('error', 'Vous ne pouvez créer des rapports que pour vous-même dans votre zone !');
                    return $this->render('admin/nouveau_rapport.html.twig', [
                        'form' => $form,
                    ]);
                }
            }

            // Associer l'utilisateur au rapport
            $rapport->setUser($user);

            // Remplir automatiquement les champs depuis l'utilisateur sélectionné
            $rapport->setCodeAgt($user->getCodeAgent());
            $rapport->setNom($user->getNom() . ' ' . $user->getPrenom());
            $rapport->setZoneUtilisateur($user->getZone());

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

    #[Route('/get-user-data', name: 'app_admin_get_user_data', methods: ['POST'])]
    public function getUserData(Request $request, UserRepository $userRepository): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => false, 'message' => 'Requête non valide'], 400);
        }

        $data = json_decode($request->getContent(), true);
        $userId = $data['userId'] ?? '';

        if (empty($userId)) {
            return new JsonResponse(['success' => false, 'message' => 'ID utilisateur manquant'], 400);
        }

        $user = $userRepository->find($userId);

        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Utilisateur introuvable'], 404);
        }

        // Vérifier les permissions de l'admin
        $currentUser = $this->getUser();
        if (
            !in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles()) &&
            $user->getZone() !== $currentUser->getZone()
        ) {
            return new JsonResponse(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        // Récupérer les zones disponibles pour cet utilisateur
        $zones = RapportHSE::getZonesForUserZone($user->getZone());

        return new JsonResponse([
            'success' => true,
            'codeAgent' => $user->getCodeAgent(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'nomComplet' => $user->getNom() . ' ' . $user->getPrenom(),
            'zone' => $user->getZone(),
            'zones' => $zones,
            'dateCreation' => $user->getDateCreation() ? $user->getDateCreation()->format('Y-m-d') : null,
            'heureCreation' => $user->getHeureCreation() ? $user->getHeureCreation()->format('H:i:s') : null
        ]);
    }

    #[Route('/rapports', name: 'app_admin_rapports')]
    public function listRapports(RapportHSERepository $rapportHSERepository, PaginationService $paginationService, Request $request): Response
    {
        // Récupérer les paramètres de pagination
        $paginationParams = $paginationService->getPaginationFromRequest($request->query->all());
        $page = $paginationParams['page'];
        $limit = $paginationParams['limit'];
        $currentUser = $this->getUser();

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

        // Ajouter le filtre de zone selon les permissions
        if (!in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles())) {
            $searchParams['zoneUtilisateur'] = $currentUser->getZone();
        }

        // Recherche avec filtres
        $rapports = $rapportHSERepository->searchRapports(
            $searchParams,
            $limit,
            ($page - 1) * $limit
        );

        $totalRapports = $rapportHSERepository->countSearchRapports($searchParams);
        $totalPages = ceil($totalRapports / $limit);

        // Créer l'objet de pagination
        $pagination = [
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalRapports,
            'itemsPerPage' => $limit,
            'hasNextPage' => $page < $totalPages,
            'hasPreviousPage' => $page > 1,
            'startItem' => ($page - 1) * $limit + 1,
            'endItem' => min($page * $limit, $totalRapports)
        ];

        // Obtenir les zones disponibles pour les filtres
        $zonesDisponibles = [];
        if (in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles())) {
            $zonesDisponibles = RapportHSE::getAllZones();
        } else {
            $zonesDisponibles = RapportHSE::getZonesForUserZone($currentUser->getZone());
        }

        return $this->render('admin/rapports.html.twig', [
            'rapports' => $rapports,
            'pagination' => $pagination,
            'search_params' => $searchParams,
            'user_zone' => $currentUser->getZone(),
            'zones_disponibles' => $zonesDisponibles,
        ]);
    }

    #[Route('/rapport/{id}', name: 'app_admin_rapport_detail', requirements: ['id' => '\d+'])]
    public function detailRapport(RapportHSE $rapport): Response
    {
        // Vérifier que l'utilisateur peut accéder à ce rapport
        if (!$rapport->canBeAccessedByUser($this->getUser())) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce rapport.');
        }

        return $this->render('admin/detail_rapport.html.twig', [
            'rapport' => $rapport,
        ]);
    }

    #[Route('/admin/rapport/{id}/modifier', name: 'app_admin_rapport_modifier')]
    public function modifierRapport(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $rapport = $entityManager->getRepository(RapportHSE::class)->find($id);

        if (!$rapport) {
            throw $this->createNotFoundException('Rapport introuvable');
        }

        $currentUser = $this->getUser();

        // Vérifier les permissions d'accès au rapport
        if (!$rapport->canBeModifiedByUser($currentUser)) {
            $this->addFlash('error', 'Vous n\'avez pas l\'autorisation de modifier ce rapport');
            return $this->redirectToRoute('app_admin_rapports');
        }

        // Déterminer si c'est un admin qui modifie son propre rapport
        $isAdminSelfReport = (
            in_array('ROLE_ADMIN', $currentUser->getRoles()) &&
            !in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles()) &&
            $rapport->getUser() &&
            $rapport->getUser()->getId() === $currentUser->getId()
        );

        $form = $this->createForm(RapportHSEType::class, $rapport, [
            'is_admin_self_report' => $isAdminSelfReport
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->get('user')->getData();

            // Vérifications de sécurité selon le rôle
            if (in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles())) {
                // Super admin peut modifier pour n'importe qui
            } else if (in_array('ROLE_ADMIN', $currentUser->getRoles())) {
                // Admin normal : peut seulement modifier ses propres rapports
                if ($user->getId() !== $currentUser->getId()) {
                    $this->addFlash('error', 'Vous ne pouvez modifier que vos propres rapports !');
                    return $this->render('admin/modifier_rapport.html.twig', [
                        'form' => $form,
                        'rapport' => $rapport
                    ]);
                }

                // Vérifier que c'est dans sa zone
                if ($user->getZone() !== $currentUser->getZone()) {
                    $this->addFlash('error', 'Vous ne pouvez modifier que les rapports de votre zone !');
                    return $this->render('admin/modifier_rapport.html.twig', [
                        'form' => $form,
                        'rapport' => $rapport
                    ]);
                }
            } else {
                // Utilisateur normal : peut seulement modifier ses propres rapports
                if ($user->getId() !== $currentUser->getId()) {
                    $this->addFlash('error', 'Vous ne pouvez modifier que vos propres rapports !');
                    return $this->render('admin/modifier_rapport.html.twig', [
                        'form' => $form,
                        'rapport' => $rapport
                    ]);
                }
            }

            // Associer l'utilisateur au rapport
            $rapport->setUser($user);

            // Remplir automatiquement les champs depuis l'utilisateur sélectionné
            $rapport->setCodeAgt($user->getCodeAgent());
            $rapport->setNom($user->getNom() . ' ' . $user->getPrenom());
            $rapport->setZoneUtilisateur($user->getZone());

            // Gérer l'upload de la photo du constat
            $photoConstatFile = $form->get('photoConstatFile')->getData();
            if ($photoConstatFile) {
                // Supprimer l'ancienne photo si elle existe
                if ($rapport->getPhotoConstat()) {
                    $oldPhotoPath = $this->getParameter('uploads_directory') . '/' . $rapport->getPhotoConstat();
                    if (file_exists($oldPhotoPath)) {
                        unlink($oldPhotoPath);
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
                    $oldPhotoPath = $this->getParameter('uploads_directory') . '/' . $rapport->getPhotoActionCloturee();
                    if (file_exists($oldPhotoPath)) {
                        unlink($oldPhotoPath);
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

            $this->addFlash('success', 'Le rapport HSE a été modifié avec succès !');
            return $this->redirectToRoute('app_admin_rapport_detail', ['id' => $rapport->getId()]);
        }

        return $this->render('admin/modifier_rapport.html.twig', [
            'form' => $form,
            'rapport' => $rapport
        ]);
    }

    #[Route('/rapport/{id}/supprimer', name: 'app_admin_rapport_supprimer', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function supprimerRapport(
        RapportHSE $rapport,
        EntityManagerInterface $entityManager
    ): Response {
        $entityManager->remove($rapport);
        $entityManager->flush();

        $this->addFlash('success', 'Le rapport HSE a été supprimé avec succès !');
        return $this->redirectToRoute('app_admin_rapports');
    }

    #[Route('/rapport/{id}/export-pdf', name: 'app_admin_export_rapport_pdf', requirements: ['id' => '\d+'])]
    public function exportRapportPdf(
        RapportHSE $rapport,
        PdfExportService $pdfExportService
    ): Response {
        // Vérifier que l'utilisateur peut accéder à ce rapport
        if (!$rapport->canBeAccessedByUser($this->getUser())) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce rapport.');
        }

        return $pdfExportService->exportSingleRapportHSE($rapport);
    }

    #[Route('/rapport/{id}/export-excel', name: 'app_admin_export_rapport_excel', requirements: ['id' => '\d+'])]
    public function exportRapportExcel(
        RapportHSE $rapport,
        ExcelExportService $excelExportService
    ): Response {
        // Vérifier que l'utilisateur peut accéder à ce rapport
        if (!$rapport->canBeAccessedByUser($this->getUser())) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce rapport.');
        }

        return $excelExportService->exportSingleRapportHSE($rapport);
    }

    // Modifier les méthodes d'export existantes pour tenir compte des zones

    #[Route('/excel', name: 'app_admin_export_excel')]
    public function exportExcel(
        RapportHSERepository $rapportRepository,
        ExcelExportService $excelExportService,
        Request $request
    ): Response {
        $currentUser = $this->getUser();

        // Récupérer les paramètres de recherche depuis la session ou la requête
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

        // Ajouter le filtre de zone selon les permissions
        if (!in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles())) {
            $searchParams['zoneUtilisateur'] = $currentUser->getZone();
        }

        // Récupérer tous les rapports correspondant aux filtres (sans limite)
        $rapports = $rapportRepository->searchRapports($searchParams, 1000, 0);

        // Déterminer le titre
        $title = 'Rapports HSE';
        if (in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles())) {
            $title = 'Tous les Rapports HSE';
        } else {
            $title = 'Rapports HSE - ' . $currentUser->getZone();
        }

        // Vérifier qu'il y a des rapports à exporter
        if (empty($rapports)) {
            $this->addFlash('warning', 'Aucun rapport trouvé pour l\'export.');
            return $this->redirectToRoute('app_admin_rapports');
        }

        return $excelExportService->exportRapportsHSE($rapports, $title);
    }

    #[Route('/pdf', name: 'app_admin_export_pdf')]
    public function exportPdf(
        RapportHSERepository $rapportRepository,
        PdfExportService $pdfExportService,
        Request $request
    ): Response {
        $currentUser = $this->getUser();

        // Récupérer les paramètres de recherche depuis la session ou la requête
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

        // Ajouter le filtre de zone selon les permissions
        if (!in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles())) {
            $searchParams['zoneUtilisateur'] = $currentUser->getZone();
        }

        // Récupérer tous les rapports correspondant aux filtres (sans limite)
        $rapports = $rapportRepository->searchRapports($searchParams, 1000, 0);

        // Déterminer le titre
        $title = 'Rapports HSE';
        if (in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles())) {
            $title = 'Tous les Rapports HSE';
        } else {
            $title = 'Rapports HSE - ' . $currentUser->getZone();
        }

        // Vérifier qu'il y a des rapports à exporter
        if (empty($rapports)) {
            $this->addFlash('warning', 'Aucun rapport trouvé pour l\'export.');
            return $this->redirectToRoute('app_admin_rapports');
        }

        return $pdfExportService->exportRapportsHSE($rapports, $title);
    }

    #[Route('/statistiques', name: 'app_admin_statistiques')]
    public function statistiques(
        RapportHSERepository $rapportRepository,
        UserRepository $userRepository
    ): Response {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $userZone = $currentUser->getZone();

        // Vérifier que l'utilisateur a bien une zone définie
        if (!$userZone) {
            $this->addFlash('error', 'Votre zone n\'est pas définie. Contactez un administrateur.');
            return $this->redirectToRoute('app_admin_dashboard');
        }

        // Si c'est un super admin, rediriger vers les statistiques globales
        if (in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles())) {
            return $this->redirectToRoute('app_super_admin_statistiques');
        }

        // Statistiques pour la zone de l'admin
        $stats = [
            'zone' => $userZone,
            'rapports' => $rapportRepository->getStatsParZone($userZone),
            'utilisateurs' => $userRepository->getStatsParZone($userZone),
            'rapports_par_mois' => $rapportRepository->getRapportsParMoisZone($userZone, 12),
            'rapports_par_statut' => $rapportRepository->getRapportsParStatutZone($userZone),
            'top_zones_travail' => $rapportRepository->getTopZonesActivesParZone($userZone, 10),
            'rapports_par_utilisateur' => $rapportRepository->getRapportsParUtilisateurZone($userZone),
            'evolution_utilisateurs' => $userRepository->getEvolutionUtilisateursZone($userZone, 12),
            'top_utilisateurs' => $userRepository->getTopUtilisateursActifs($userZone, 5),
            'performance' => $rapportRepository->getPerformanceZone($userZone, 6)
        ];

        return $this->render('admin/statistiques.html.twig', [
            'stats' => $stats,
            'zone_title' => $userZone,
            'zone_color' => $userZone === 'SIMTIS' ? 'info' : 'purple'
        ]);
    }
}
