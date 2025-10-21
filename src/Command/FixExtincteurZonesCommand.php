<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Entity\Extincteur;

#[AsCommand(
    name: 'app:fix-extincteur-zones',
    description: 'Corrige les zones et emplacements des extincteurs',
)]
class FixExtincteurZonesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('🔧 Correction des zones et emplacements des extincteurs');
        
        // Récupérer tous les extincteurs
        $extincteurs = $this->entityManager->getRepository(Extincteur::class)->findAll();
        
        $corrections = 0;
        
        foreach ($extincteurs as $extincteur) {
            $zoneActuelle = $extincteur->getZone();
            $emplacementActuel = $extincteur->getEmplacement();
            
            // Déterminer la nouvelle zone et le nouvel emplacement
            $nouvelleZone = null;
            $nouvelEmplacement = null;
            
            // Si la zone actuelle contient des emplacements spécifiques, c'est qu'il faut inverser
            if (in_array($zoneActuelle, [
                'ADMINISTRATION', 'BRODERIE', 'BUREAUX D\'ETUDES', 'CALANDRAGE', 
                'CHALES ET FOULARDS.BC', 'CHAUFFERIE', 'CONFECTION', 'EMBALLAGE',
                'ENTREPOT', 'FINITION', 'LAVAGE', 'MAINTENANCE', 'PREPARATION',
                'TEINTURE', 'TISSAGE', 'TRI', 'ZONE DECATHLON'
            ])) {
                // La zone actuelle est en fait un emplacement
                $nouvelEmplacement = $zoneActuelle;
                
                // Déterminer la zone selon l'emplacement
                if (in_array($zoneActuelle, ['BRODERIE', 'TISSAGE', 'PREPARATION', 'TEINTURE'])) {
                    $nouvelleZone = 'SIMTIS TISSAGE';
                } else {
                    $nouvelleZone = 'SIMTIS';
                }
            }
            
            // Si l'emplacement actuel contient des zones, c'est qu'il faut inverser
            if (in_array($emplacementActuel, ['SIMTIS', 'SIMTIS TISSAGE'])) {
                // L'emplacement actuel est en fait une zone
                $nouvelleZone = $emplacementActuel;
                $nouvelEmplacement = $zoneActuelle; // L'ancienne zone devient l'emplacement
            }
            
            // Si on a déterminé les corrections, les appliquer
            if ($nouvelleZone && $nouvelEmplacement) {
                $extincteur->setZone($nouvelleZone);
                $extincteur->setEmplacement($nouvelEmplacement);
                
                $io->success("Extincteur ID {$extincteur->getId()}: Zone '{$zoneActuelle}' → '{$nouvelleZone}', Emplacement '{$emplacementActuel}' → '{$nouvelEmplacement}'");
                $corrections++;
            } else {
                $io->note("Extincteur ID {$extincteur->getId()}: Pas de correction nécessaire (Zone: '{$zoneActuelle}', Emplacement: '{$emplacementActuel}')");
            }
        }
        
        // Sauvegarder les modifications
        if ($corrections > 0) {
            $this->entityManager->flush();
            $io->success("🎉 Correction terminée ! {$corrections} extincteurs corrigés.");
        } else {
            $io->info("Aucune correction nécessaire.");
        }
        
        return Command::SUCCESS;
    }
}
