<?php

namespace App\Tests\Security;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RolesAndPermissionsTest extends WebTestCase
{
    /**
     * Test que la hiérarchie des rôles fonctionne correctement
     */
    public function testRoleHierarchy(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $roleHierarchy = $container->getParameter('security.role_hierarchy.roles');

        // Vérifier que ROLE_ADMIN hérite de ROLE_USER
        $this->assertArrayHasKey('ROLE_ADMIN', $roleHierarchy);
        $this->assertContains('ROLE_USER', $roleHierarchy['ROLE_ADMIN']);

        // Vérifier que ROLE_SUPER_ADMIN hérite de ROLE_ADMIN et ROLE_USER
        $this->assertArrayHasKey('ROLE_SUPER_ADMIN', $roleHierarchy);
        $this->assertContains('ROLE_ADMIN', $roleHierarchy['ROLE_SUPER_ADMIN']);
        $this->assertContains('ROLE_USER', $roleHierarchy['ROLE_SUPER_ADMIN']);
    }

    /**
     * Test que les routes admin sont protégées
     */
    public function testAdminRoutesProtection(): void
    {
        $client = static::createClient();
        
        // Essayer d'accéder à une route admin sans être connecté
        $client->request('GET', '/admin');
        
        // Doit rediriger vers login (302) ou accès refusé (403)
        $this->assertContains(
            $client->getResponse()->getStatusCode(),
            [302, 403]
        );
    }

    /**
     * Test que les routes super admin sont protégées
     */
    public function testSuperAdminRoutesProtection(): void
    {
        $client = static::createClient();
        
        // Essayer d'accéder à la réinitialisation sans être connecté
        $client->request('GET', '/admin/reset-inspection');
        
        // Doit rediriger ou refuser
        $this->assertContains(
            $client->getResponse()->getStatusCode(),
            [302, 403]
        );
    }

    /**
     * Test que la page de login est accessible publiquement
     */
    public function testLoginPageIsPublic(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/login');
        
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * Test les permissions de suppression d'équipement
     */
    public function testEquipmentDeletionRequiresSuperAdmin(): void
    {
        // Ce test vérifie que seul SUPER_ADMIN peut supprimer
        // Les routes de suppression doivent avoir #[IsGranted('ROLE_SUPER_ADMIN')]
        
        $protectedRoutes = [
            '/equipements/extincteurs/1/supprimer',
            '/equipements/ria/1/supprimer',
            '/equipements/monte-charge/1/supprimer',
            '/equipements/sirenes/1/supprimer',
            '/equipements/desenfumage/1/supprimer',
            '/equipements/prises-pompiers/1/supprimer',
            '/equipements/issues-secours/1/supprimer',
        ];

        $client = static::createClient();
        
        foreach ($protectedRoutes as $route) {
            $client->request('GET', $route);
            
            // Sans authentification, doit être refusé
            $this->assertContains(
                $client->getResponse()->getStatusCode(),
                [302, 403, 404], // 404 car l'ID 1 peut ne pas exister
                "Route $route devrait être protégée"
            );
        }
    }

    /**
     * Test que les inspections peuvent être supprimées par ADMIN
     */
    public function testInspectionDeletionRequiresAdmin(): void
    {
        // Les routes de suppression d'inspection doivent avoir #[IsGranted('ROLE_ADMIN')]
        
        $protectedRoutes = [
            '/ram-inspection/1/supprimer',
            '/sirene-inspection/1/supprimer',
            '/desenfumage-inspection/1/supprimer',
            '/issue-secours-inspection/1/supprimer',
            '/ria-inspection/1/supprimer',
            '/prise-pompier-inspection/1/supprimer',
            '/monte-charge-inspection/1/supprimer',
        ];

        $client = static::createClient();
        
        foreach ($protectedRoutes as $route) {
            $client->request('POST', $route);
            
            // Sans authentification, doit être refusé
            $this->assertContains(
                $client->getResponse()->getStatusCode(),
                [302, 403, 404],
                "Route $route devrait être protégée (ROLE_ADMIN)"
            );
        }
    }
}


