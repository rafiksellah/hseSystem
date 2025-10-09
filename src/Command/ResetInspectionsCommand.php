<?php

namespace App\Command;

use App\Service\ResetInspectionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:reset-inspections',
    description: 'Réinitialise les inspections selon la périodicité définie',
)]
class ResetInspectionsCommand extends Command
{
    public function __construct(
        private ResetInspectionService $resetService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('type', InputArgument::OPTIONAL, 'Type d\'équipement (extincteur, sirene, extinction_ram, monte_charge, ria, desenfumage, issue_secours, prise_pompier, all)', 'all')            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Forcer la réinitialisation même si pas nécessaire')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Simuler sans effectuer les changements')
            ->addOption('reason', 'r', InputOption::VALUE_REQUIRED, 'Raison de la réinitialisation', 'Réinitialisation automatique');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $type = $input->getArgument('type');
        $force = $input->getOption('force');
        $dryRun = $input->getOption('dry-run');
        $reason = $input->getOption('reason');

        $io->title('🔄 Réinitialisation des Inspections');

        if ($dryRun) {
            $io->note('Mode simulation activé - Aucun changement ne sera effectué');
        }

        $equipmentTypes = $type === 'all' 
            ? ['extincteur', 'sirene', 'extinction_ram', 'monte_charge', 'ria', 'desenfumage', 'issue_secours', 'prise_pompier']            : [$type];

        $totalResults = [
            'deleted' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        foreach ($equipmentTypes as $equipmentType) {
            $io->section("📋 Traitement des {$equipmentType}s");

            // Vérifier si une réinitialisation est nécessaire
            if (!$force && !$this->resetService->needsReset($equipmentType)) {
                $io->text("✅ {$equipmentType}: Réinitialisation non nécessaire");
                $totalResults['skipped']++;
                continue;
            }

            if ($dryRun) {
                $io->text("🔍 {$equipmentType}: Simulation de réinitialisation");
                continue;
            }

            try {
                // Déterminer le type de réinitialisation
                $resetType = ($equipmentType === 'monte_charge') ? 'daily' : 'monthly';
                
                $results = $this->resetService->resetInspectionsByType(
                    $equipmentType, 
                    $resetType, 
                    null, // Pas d'utilisateur pour les réinitialisations automatiques
                    $reason
                );

                // Afficher les résultats
                $io->table(
                    ['Métrique', 'Valeur'],
                    [
                        ['Supprimées', $results['deleted']],
                        ['Erreurs', count($results['errors'])]
                    ]
                );

                if (!empty($results['errors'])) {
                    $io->warning('Erreurs rencontrées:');
                    foreach ($results['errors'] as $error) {
                        $io->text("  • {$error}");
                    }
                }

                // Mettre à jour les totaux
                $totalResults['deleted'] += $results['deleted'];
                $totalResults['errors'] = array_merge($totalResults['errors'], $results['errors']);

                $io->success("✅ {$equipmentType}: Réinitialisation terminée");

            } catch (\Exception $e) {
                $io->error("❌ Erreur lors de la réinitialisation {$equipmentType}: " . $e->getMessage());
                $totalResults['errors'][] = "{$equipmentType}: " . $e->getMessage();
            }
        }

        // Résumé final
        $io->section('📊 Résumé Final');
        $io->table(
            ['Métrique', 'Total'],
            [
                ['Inspections supprimées', $totalResults['deleted']],
                ['Types ignorés', $totalResults['skipped']],
                ['Erreurs', count($totalResults['errors'])]
            ]
        );

        if (!empty($totalResults['errors'])) {
            $io->error('Erreurs rencontrées:');
            foreach ($totalResults['errors'] as $error) {
                $io->text("  • {$error}");
            }
            return Command::FAILURE;
        }

        $io->success('🎉 Réinitialisation terminée avec succès !');
        return Command::SUCCESS;
    }
}
