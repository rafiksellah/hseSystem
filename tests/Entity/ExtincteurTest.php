<?php

namespace App\Tests\Entity;

use App\Entity\Extincteur;
use App\Entity\InspectionExtincteur;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ExtincteurTest extends TestCase
{
    public function testExtincteurCreation(): void
    {
        $extincteur = new Extincteur();
        $extincteur->setNumerotation('EXT-001');
        $extincteur->setZone('SIMTIS');
        $extincteur->setEmplacement('Bureau');
        $extincteur->setAgentExtincteur('Poudre ABC');
        $extincteur->setType('Portatif');
        $extincteur->setCapacite('6kg');

        $this->assertEquals('EXT-001', $extincteur->getNumerotation());
        $this->assertEquals('SIMTIS', $extincteur->getZone());
        $this->assertEquals('Bureau', $extincteur->getEmplacement());
        $this->assertEquals('Poudre ABC', $extincteur->getAgentExtincteur());
        $this->assertEquals('Portatif', $extincteur->getType());
        $this->assertEquals('6kg', $extincteur->getCapacite());
    }

    public function testStatutConformiteSansInspection(): void
    {
        $extincteur = new Extincteur();
        
        // Sans inspection, le statut doit être "Non inspecté"
        $this->assertEquals('Non inspecté', $extincteur->getStatutConformite());
    }

    public function testNombreInspectionsSansInspection(): void
    {
        $extincteur = new Extincteur();
        
        // Sans inspection, le nombre doit être 0
        $this->assertEquals(0, $extincteur->getNombreInspections());
    }

    public function testValidation(): void
    {
        $user = new User();
        $user->setNom('Validateur');
        $user->setPrenom('Test');

        $extincteur = new Extincteur();
        
        // Par défaut, non validé
        $this->assertFalse($extincteur->isValide());
        
        // Valider
        $extincteur->setValide(true);
        $extincteur->setDateValidation(new \DateTime());
        $extincteur->setValidePar($user);
        
        $this->assertTrue($extincteur->isValide());
        $this->assertInstanceOf(\DateTimeInterface::class, $extincteur->getDateValidation());
        $this->assertEquals($user, $extincteur->getValidePar());
    }

    public function testGetZonesForUser(): void
    {
        // Super Admin
        $superAdmin = new User();
        $superAdmin->setRoles(['ROLE_SUPER_ADMIN']);
        $zones = Extincteur::getZonesForUser($superAdmin);
        $this->assertCount(2, $zones);
        
        // Admin/User avec zone
        $admin = new User();
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setZone('SIMTIS');
        $zones = Extincteur::getZonesForUser($admin);
        $this->assertCount(1, $zones);
        $this->assertArrayHasKey('SIMTIS', $zones);
    }

    public function testDatesExtincteur(): void
    {
        $extincteur = new Extincteur();
        
        $dateFab = new \DateTime('2020-01-01');
        $dateEpreuve = new \DateTime('2023-01-01');
        $dateFinVie = new \DateTime('2030-01-01');
        
        $extincteur->setDateFabrication($dateFab);
        $extincteur->setDateEpreuve($dateEpreuve);
        $extincteur->setDateFinDeVie($dateFinVie);
        
        $this->assertEquals($dateFab, $extincteur->getDateFabrication());
        $this->assertEquals($dateEpreuve, $extincteur->getDateEpreuve());
        $this->assertEquals($dateFinVie, $extincteur->getDateFinDeVie());
    }
}


