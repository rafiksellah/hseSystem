<?php

namespace App\Tests\Functional;

use App\Entity\Extincteur;
use App\Entity\InspectionExtincteur;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Tests fonctionnels pour le système d'inspection
 */
class InspectionTest extends KernelTestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    /**
     * Test qu'un extincteur peut avoir une inspection
     */
    public function testExtincteurCanHaveInspection(): void
    {
        $extincteur = new Extincteur();
        $extincteur->setNumerotation('TEST-EXT-001');
        $extincteur->setZone('SIMTIS');

        $user = new User();
        $user->setEmail('test@test.com');
        $user->setNom('Test');
        $user->setPrenom('User');
        $user->setCodeAgent('TEST001');
        $user->setZone('SIMTIS');
        $user->setPassword('test');

        $inspection = new InspectionExtincteur();
        $inspection->setExtincteur($extincteur);
        $inspection->setInspectePar($user);
        $inspection->setCriteres([
            'fixation' => true,
            'signalisation' => true,
            'acces' => true,
            'pression' => true,
            'scelle' => true,
            'etat_general' => true,
        ]);
        $inspection->setValide(true);

        $extincteur->addInspection($inspection);

        $this->assertCount(1, $extincteur->getInspections());
        $this->assertEquals($inspection, $extincteur->getDerniereInspection());
        $this->assertEquals('Conforme', $extincteur->getStatutConformite());
    }

    /**
     * Test qu'une inspection non conforme change le statut
     */
    public function testNonConformeInspectionChangesStatus(): void
    {
        $extincteur = new Extincteur();
        $extincteur->setNumerotation('TEST-EXT-002');
        $extincteur->setZone('SIMTIS');

        $user = new User();
        $user->setEmail('test2@test.com');
        $user->setNom('Test');
        $user->setPrenom('User');
        $user->setCodeAgent('TEST002');
        $user->setZone('SIMTIS');
        $user->setPassword('test');

        $inspection = new InspectionExtincteur();
        $inspection->setExtincteur($extincteur);
        $inspection->setInspectePar($user);
        $inspection->setCriteres([
            'fixation' => true,
            'signalisation' => false, // Non conforme
            'acces' => true,
            'pression' => true,
            'scelle' => true,
            'etat_general' => true,
        ]);
        $inspection->setValide(false);
        $inspection->setIsActive(true);

        $extincteur->addInspection($inspection);

        $this->assertEquals('Non conforme', $extincteur->getStatutConformite());
        $this->assertFalse($inspection->isValide());
    }

    /**
     * Test que getDerniereInspection retourne seulement les inspections actives
     */
    public function testGetDerniereInspectionOnlyReturnsActive(): void
    {
        $extincteur = new Extincteur();
        $extincteur->setNumerotation('TEST-EXT-003');
        $extincteur->setZone('SIMTIS');

        $user = new User();
        $user->setEmail('test3@test.com');
        $user->setNom('Test');
        $user->setPrenom('User');
        $user->setCodeAgent('TEST003');
        $user->setZone('SIMTIS');
        $user->setPassword('test');

        // Créer une inspection inactive
        $oldInspection = new InspectionExtincteur();
        $oldInspection->setExtincteur($extincteur);
        $oldInspection->setInspectePar($user);
        $oldInspection->setCriteres(['fixation' => true, 'signalisation' => true, 'acces' => true, 'pression' => true, 'scelle' => true, 'etat_general' => true]);
        $oldInspection->setValide(true);
        $oldInspection->setIsActive(false); // Inactive
        $oldInspection->setDateInspection(new \DateTime('-1 month'));

        // Créer une inspection active
        $newInspection = new InspectionExtincteur();
        $newInspection->setExtincteur($extincteur);
        $newInspection->setInspectePar($user);
        $newInspection->setCriteres(['fixation' => true, 'signalisation' => true, 'acces' => true, 'pression' => true, 'scelle' => true, 'etat_general' => true]);
        $newInspection->setValide(true);
        $newInspection->setIsActive(true); // Active
        $newInspection->setDateInspection(new \DateTime());

        $extincteur->addInspection($oldInspection);
        $extincteur->addInspection($newInspection);

        // getDerniereInspection doit retourner seulement l'inspection active
        $derniere = $extincteur->getDerniereInspection();
        $this->assertEquals($newInspection, $derniere);
        $this->assertTrue($derniere->isActive());
    }

    /**
     * Test le calcul du nombre d'inspections
     */
    public function testNombreInspections(): void
    {
        $extincteur = new Extincteur();
        $extincteur->setNumerotation('TEST-EXT-004');
        $extincteur->setZone('SIMTIS');

        $this->assertEquals(0, $extincteur->getNombreInspections());

        $user = new User();
        $user->setEmail('test4@test.com');
        $user->setNom('Test');
        $user->setPrenom('User');
        $user->setCodeAgent('TEST004');
        $user->setZone('SIMTIS');
        $user->setPassword('test');

        // Ajouter 3 inspections
        for ($i = 1; $i <= 3; $i++) {
            $inspection = new InspectionExtincteur();
            $inspection->setExtincteur($extincteur);
            $inspection->setInspectePar($user);
            $inspection->setCriteres(['fixation' => true, 'signalisation' => true, 'acces' => true, 'pression' => true, 'scelle' => true, 'etat_general' => true]);
            $inspection->setValide(true);
            $extincteur->addInspection($inspection);
        }

        $this->assertEquals(3, $extincteur->getNombreInspections());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}


