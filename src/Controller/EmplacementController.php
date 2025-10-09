<?php

namespace App\Controller;

use App\Entity\Emplacement;
use App\Repository\EmplacementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/emplacements')]
#[IsGranted('ROLE_ADMIN')]
class EmplacementController extends AbstractController
{
    #[Route('/ajax/add', name: 'app_emplacement_ajax_add', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function ajaxAdd(
        Request $request,
        EntityManagerInterface $entityManager,
        EmplacementRepository $emplacementRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        $nom = trim($data['nom'] ?? '');
        $typeEquipement = trim($data['typeEquipement'] ?? '');
        $zone = trim($data['zone'] ?? '');
        
        if (empty($nom) || empty($typeEquipement)) {
            return new JsonResponse(['success' => false, 'message' => 'Nom et type d\'équipement requis'], 400);
        }
        
        // Créer le nouvel emplacement
        $emplacement = new Emplacement();
        $emplacement->setNom($nom);
        $emplacement->setTypeEquipement($typeEquipement);
        $emplacement->setZone($zone);
        
        try {
            $entityManager->persist($emplacement);
            $entityManager->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Emplacement ajouté avec succès',
                'emplacement' => [
                    'id' => $emplacement->getId(),
                    'nom' => $emplacement->getNom(),
                    'typeEquipement' => $emplacement->getTypeEquipement(),
                    'zone' => $emplacement->getZone()
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => 'Erreur lors de l\'ajout: ' . $e->getMessage()], 500);
        }
    }
    
    #[Route('/ajax/search', name: 'app_emplacement_ajax_search', methods: ['GET'])]
    public function ajaxSearch(Request $request, EmplacementRepository $emplacementRepository): JsonResponse
    {
        $search = $request->query->get('q', '');
        $zone = $request->query->get('zone', '');
        
        if (empty($search)) {
            // Retourner tous les emplacements de la zone si pas de recherche
            if ($zone) {
                $emplacements = $emplacementRepository->findByZone($zone);
            } else {
                $emplacements = $emplacementRepository->findAll();
            }
        } else {
            $emplacements = $emplacementRepository->searchByName($search);
        }
        
        $results = [];
        foreach ($emplacements as $emplacement) {
            $results[] = [
                'id' => $emplacement->getId(),
                'nom' => $emplacement->getNom(),
                'zone' => $emplacement->getZone()
            ];
        }
        
        return new JsonResponse($results);
    }
    
    #[Route('/ajax/by-zone/{zone}', name: 'app_emplacement_ajax_by_zone', methods: ['GET'])]
    public function ajaxByZone(string $zone, EmplacementRepository $emplacementRepository): JsonResponse
    {
        $emplacements = $emplacementRepository->findByZone($zone);
        
        $results = [];
        foreach ($emplacements as $emplacement) {
            $results[] = [
                'id' => $emplacement->getId(),
                'nom' => $emplacement->getNom(),
                'zone' => $emplacement->getZone()
            ];
        }
        
        return new JsonResponse($results);
    }
    
    #[Route('/ajax/by-type/{typeEquipement}', name: 'app_emplacement_ajax_by_type', methods: ['GET'])]
    public function ajaxByType(string $typeEquipement, EmplacementRepository $emplacementRepository): JsonResponse
    {
        $emplacements = $emplacementRepository->findByTypeEquipement($typeEquipement);
        
        $results = [];
        foreach ($emplacements as $emplacement) {
            $results[] = [
                'id' => $emplacement->getId(),
                'nom' => $emplacement->getNom(),
                'typeEquipement' => $emplacement->getTypeEquipement(),
                'zone' => $emplacement->getZone()
            ];
        }
        
        return new JsonResponse($results);
    }
    
    #[Route('/ajax/all', name: 'app_emplacement_ajax_all', methods: ['GET'])]
    public function ajaxAll(EmplacementRepository $emplacementRepository): JsonResponse
    {
        $emplacements = $emplacementRepository->findBy([], ['typeEquipement' => 'ASC', 'nom' => 'ASC']);
        
        $results = [];
        foreach ($emplacements as $emplacement) {
            $results[] = [
                'id' => $emplacement->getId(),
                'nom' => $emplacement->getNom(),
                'typeEquipement' => $emplacement->getTypeEquipement(),
                'zone' => $emplacement->getZone()
            ];
        }
        
        return new JsonResponse($results);
    }
}
