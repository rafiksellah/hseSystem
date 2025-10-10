<?php

namespace App\Tests\Service;

use App\Entity\Extincteur;
use App\Entity\InspectionExtincteur;
use App\Entity\User;
use App\Service\ResetInspectionService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ResetInspectionServiceTest extends TestCase
{
    private $entityManager;
    private $logger;
    private ResetInspectionService $service;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->service = new ResetInspectionService($this->entityManager, $this->logger);
    }

    public function testServiceInstantiation(): void
    {
        $this->assertInstanceOf(ResetInspectionService::class, $this->service);
    }

    public function testResetInspectionsByTypeWithInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Type d\'équipement non supporté');
        
        $this->service->resetInspectionsByType('invalid_type');
    }

    public function testResetInspectionsByTypeValidTypes(): void
    {
        $validTypes = [
            'extincteur',
            'sirene',
            'extinction_ram',
            'monte_charge',
            'ria',
            'desenfumage',
            'issue_secours',
            'prise_pompier'
        ];

        foreach ($validTypes as $type) {
            // Mock repository
            $repository = $this->createMock(\Doctrine\ORM\EntityRepository::class);
            $queryBuilder = $this->createMock(\Doctrine\ORM\QueryBuilder::class);
            $query = $this->createMock(\Doctrine\ORM\AbstractQuery::class);
            
            $this->entityManager
                ->method('getRepository')
                ->willReturn($repository);
            
            $repository
                ->method('createQueryBuilder')
                ->willReturn($queryBuilder);
                
            $queryBuilder
                ->method('where')
                ->willReturnSelf();
                
            $queryBuilder
                ->method('setParameter')
                ->willReturnSelf();
                
            $queryBuilder
                ->method('getQuery')
                ->willReturn($query);
                
            $query
                ->method('getResult')
                ->willReturn([]);
            
            $repository
                ->method('findAll')
                ->willReturn([]);

            $result = $this->service->resetInspectionsByType($type);
            
            $this->assertIsArray($result);
            $this->assertArrayHasKey('deleted', $result);
            $this->assertArrayHasKey('errors', $result);
        }
    }

    public function testResetAllInspectionsCallsAllTypes(): void
    {
        // Mock pour simuler le comportement
        $repository = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $queryBuilder = $this->createMock(\Doctrine\ORM\QueryBuilder::class);
        $query = $this->createMock(\Doctrine\ORM\AbstractQuery::class);
        
        $this->entityManager
            ->method('getRepository')
            ->willReturn($repository);
        
        $repository
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);
            
        $queryBuilder
            ->method('where')
            ->willReturnSelf();
            
        $queryBuilder
            ->method('setParameter')
            ->willReturnSelf();
            
        $queryBuilder
            ->method('getQuery')
            ->willReturn($query);
            
        $query
            ->method('getResult')
            ->willReturn([]);
        
        $repository
            ->method('findAll')
            ->willReturn([]);

        $results = $this->service->resetAllInspections();
        
        $this->assertIsArray($results);
        $this->assertCount(8, $results); // 8 types d'équipements
        $this->assertArrayHasKey('extincteur', $results);
        $this->assertArrayHasKey('sirene', $results);
        $this->assertArrayHasKey('extinction_ram', $results);
        $this->assertArrayHasKey('monte_charge', $results);
        $this->assertArrayHasKey('ria', $results);
        $this->assertArrayHasKey('desenfumage', $results);
        $this->assertArrayHasKey('issue_secours', $results);
        $this->assertArrayHasKey('prise_pompier', $results);
    }
}


