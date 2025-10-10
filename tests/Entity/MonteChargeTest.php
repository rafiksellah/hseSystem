<?php

namespace App\Tests\Entity;

use App\Entity\MonteCharge;
use App\Entity\InspectionMonteCharge;
use PHPUnit\Framework\TestCase;

class MonteChargeTest extends TestCase
{
    public function testMonteChargeCreation(): void
    {
        $monteCharge = new MonteCharge();
        $monteCharge->setNumeroMonteCharge('MONTE CHARGE 01');
        $monteCharge->setZone('SIMTIS');
        $monteCharge->setEmplacement('RDC TISSAGE');
        $monteCharge->setNumeroPorte('PORTE 01');

        $this->assertEquals('MONTE CHARGE 01', $monteCharge->getNumeroMonteCharge());
        $this->assertEquals('SIMTIS', $monteCharge->getZone());
        $this->assertEquals('RDC TISSAGE', $monteCharge->getEmplacement());
        $this->assertEquals('PORTE 01', $monteCharge->getNumeroPorte());
    }

    public function testStatutConformiteSansInspection(): void
    {
        $monteCharge = new MonteCharge();
        
        // Sans inspection, le statut doit être "Non inspecté"
        $this->assertEquals('Non inspecté', $monteCharge->getStatutConformite());
    }

    public function testEmplacementsConstants(): void
    {
        // Vérifier que les constantes d'emplacements sont bien définies
        $emplacementsSimtis = MonteCharge::EMPLACEMENTS_SIMTIS;
        $emplacementsTissage = MonteCharge::EMPLACEMENTS_TISSAGE;
        
        $this->assertIsArray($emplacementsSimtis);
        $this->assertIsArray($emplacementsTissage);
        $this->assertNotEmpty($emplacementsSimtis);
        $this->assertNotEmpty($emplacementsTissage);
    }

    public function testNumerosPorteConstants(): void
    {
        // Vérifier que les constantes sont bien définies
        $portes = MonteCharge::NUMEROS_PORTE;
        
        $this->assertIsArray($portes);
        $this->assertCount(4, $portes); // 4 portes max
        $this->assertArrayHasKey('PORTE 01', $portes);
        $this->assertArrayHasKey('PORTE 04', $portes);
    }

    public function testZonesConstants(): void
    {
        $zones = MonteCharge::ZONES;
        
        $this->assertIsArray($zones);
        $this->assertNotEmpty($zones);
        $this->assertArrayHasKey('SIMTIS', $zones);
        $this->assertArrayHasKey('SIMTIS TISSAGE', $zones);
    }

    public function testDateCreationAutomatic(): void
    {
        $monteCharge = new MonteCharge();
        
        // La date de création doit être automatiquement définie
        $this->assertInstanceOf(\DateTimeInterface::class, $monteCharge->getDateCreation());
        
        // La date doit être récente (moins de 1 minute)
        $now = new \DateTime();
        $diff = $now->getTimestamp() - $monteCharge->getDateCreation()->getTimestamp();
        $this->assertLessThan(60, $diff);
    }
}

