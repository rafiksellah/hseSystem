<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserCreation(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setNom('Dupont');
        $user->setPrenom('Jean');
        $user->setCodeAgent('AG001');
        $user->setZone('SIMTIS');

        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('Dupont', $user->getNom());
        $this->assertEquals('Jean', $user->getPrenom());
        $this->assertEquals('Jean Dupont', $user->getFullName());
        $this->assertEquals('AG001', $user->getCodeAgent());
        $this->assertEquals('SIMTIS', $user->getZone());
    }

    public function testUserRoles(): void
    {
        $user = new User();
        
        // Par défaut, un utilisateur a ROLE_USER
        $this->assertContains('ROLE_USER', $user->getRoles());
        
        // Ajouter ROLE_ADMIN
        $user->setRoles(['ROLE_ADMIN']);
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertContains('ROLE_USER', $user->getRoles()); // Toujours présent
        
        // Ajouter ROLE_SUPER_ADMIN
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $this->assertContains('ROLE_SUPER_ADMIN', $user->getRoles());
    }

    public function testIsSuperAdmin(): void
    {
        $user = new User();
        
        // Utilisateur normal
        $user->setRoles(['ROLE_USER']);
        $this->assertFalse($user->isSuperAdmin());
        
        // Admin
        $user->setRoles(['ROLE_ADMIN']);
        $this->assertFalse($user->isSuperAdmin());
        
        // Super Admin
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $this->assertTrue($user->isSuperAdmin());
    }

    public function testIsAdmin(): void
    {
        $user = new User();
        
        // Utilisateur normal
        $user->setRoles(['ROLE_USER']);
        $this->assertFalse($user->isAdmin());
        
        // Admin (pas super admin)
        $user->setRoles(['ROLE_ADMIN']);
        $this->assertTrue($user->isAdmin());
        
        // Super Admin (n'est PAS considéré comme admin simple)
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $this->assertFalse($user->isAdmin());
    }

    public function testNeedsZone(): void
    {
        $user = new User();
        
        // Utilisateur normal a besoin d'une zone
        $user->setRoles(['ROLE_USER']);
        $this->assertTrue($user->needsZone());
        
        // Admin a besoin d'une zone
        $user->setRoles(['ROLE_ADMIN']);
        $this->assertTrue($user->needsZone());
        
        // Super Admin n'a PAS besoin de zone
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $this->assertFalse($user->needsZone());
    }

    public function testGetDisplayZone(): void
    {
        $user = new User();
        
        // Utilisateur avec zone
        $user->setZone('SIMTIS');
        $this->assertEquals('SIMTIS', $user->getDisplayZone());
        
        // Super Admin sans zone
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $user->setZone(null);
        $this->assertEquals('Toutes les zones', $user->getDisplayZone());
    }

    public function testCanManageUser(): void
    {
        $superAdmin = new User();
        $superAdmin->setRoles(['ROLE_SUPER_ADMIN']);
        
        $adminSimtis = new User();
        $adminSimtis->setRoles(['ROLE_ADMIN']);
        $adminSimtis->setZone('SIMTIS');
        
        $adminTissage = new User();
        $adminTissage->setRoles(['ROLE_ADMIN']);
        $adminTissage->setZone('SIMTIS TISSAGE');
        
        $userSimtis = new User();
        $userSimtis->setRoles(['ROLE_USER']);
        $userSimtis->setZone('SIMTIS');
        
        $userTissage = new User();
        $userTissage->setRoles(['ROLE_USER']);
        $userTissage->setZone('SIMTIS TISSAGE');
        
        // Super Admin peut gérer tout le monde
        $this->assertTrue($superAdmin->canManageUser($adminSimtis));
        $this->assertTrue($superAdmin->canManageUser($adminTissage));
        $this->assertTrue($superAdmin->canManageUser($userSimtis));
        $this->assertTrue($superAdmin->canManageUser($userTissage));
        
        // Admin SIMTIS peut gérer uniquement les users de SIMTIS
        $this->assertTrue($adminSimtis->canManageUser($userSimtis));
        $this->assertFalse($adminSimtis->canManageUser($userTissage));
        $this->assertFalse($adminSimtis->canManageUser($adminTissage));
        
        // User ne peut gérer personne
        $this->assertFalse($userSimtis->canManageUser($userSimtis));
        $this->assertFalse($userSimtis->canManageUser($adminSimtis));
    }

    public function testGetManagedZones(): void
    {
        // Super Admin
        $superAdmin = new User();
        $superAdmin->setRoles(['ROLE_SUPER_ADMIN']);
        $managedZones = $superAdmin->getManagedZones();
        $this->assertCount(2, $managedZones);
        $this->assertArrayHasKey('SIMTIS', $managedZones);
        $this->assertArrayHasKey('SIMTIS TISSAGE', $managedZones);
        
        // Admin avec zone
        $admin = new User();
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setZone('SIMTIS');
        $managedZones = $admin->getManagedZones();
        $this->assertCount(1, $managedZones);
        $this->assertArrayHasKey('SIMTIS', $managedZones);
        
        // User
        $user = new User();
        $user->setRoles(['ROLE_USER']);
        $user->setZone('SIMTIS');
        $this->assertEmpty($user->getManagedZones());
    }

    public function testUserIdentifier(): void
    {
        $user = new User();
        $user->setEmail('admin@hse.com');
        
        $this->assertEquals('admin@hse.com', $user->getUserIdentifier());
    }
}


