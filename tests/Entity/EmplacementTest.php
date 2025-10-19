<?php

namespace App\Tests\Entity;

use App\Entity\Emplacement;
use PHPUnit\Framework\TestCase;

class EmplacementTest extends TestCase
{
    public function testEmplacementCreation(): void
    {
        $emplacement = new Emplacement();
        $emplacement->setNom('Bureau Test');
        $emplacement->setTypeEquipement('extincteur');
        $emplacement->setZone('SIMTIS');

        $this->assertEquals('Bureau Test', $emplacement->getNom());
        $this->assertEquals('extincteur', $emplacement->getTypeEquipement());
        $this->assertEquals('SIMTIS', $emplacement->getZone());
    }

    public function testEmplacementDateCreationAutomatic(): void
    {
        $emplacement = new Emplacement();
        
        // La date de création doit être automatiquement définie
        $this->assertInstanceOf(\DateTimeInterface::class, $emplacement->getDateCreation());
        
        // La date doit être récente (moins de 1 minute)
        $now = new \DateTime();
        $diff = $now->getTimestamp() - $emplacement->getDateCreation()->getTimestamp();
        $this->assertLessThan(60, $diff);
    }

    public function testEmplacementDateModification(): void
    {
        $emplacement = new Emplacement();
        
        // Par défaut, pas de date de modification
        $this->assertNull($emplacement->getDateModification());
        
        // Définir une date de modification
        $dateModif = new \DateTime();
        $emplacement->setDateModification($dateModif);
        
        $this->assertEquals($dateModif, $emplacement->getDateModification());
    }

    public function testEmplacementZoneOptional(): void
    {
        $emplacement = new Emplacement();
        $emplacement->setNom('Test Sans Zone');
        $emplacement->setTypeEquipement('ria');
        
        // La zone peut être null
        $this->assertNull($emplacement->getZone());
        
        // On peut définir une zone
        $emplacement->setZone('SIMTIS');
        $this->assertEquals('SIMTIS', $emplacement->getZone());
    }

    public function testEmplacementTypesEquipement(): void
    {
        $typesValides = [
            'extincteur',
            'ram',
            'sirene',
            'ria',
            'monte_charge',
            'desenfumage',
            'prise_pompier',
            'issue_secours'
        ];

        foreach ($typesValides as $type) {
            $emplacement = new Emplacement();
            $emplacement->setNom('Test ' . $type);
            $emplacement->setTypeEquipement($type);
            
            $this->assertEquals($type, $emplacement->getTypeEquipement());
        }
    }
}






