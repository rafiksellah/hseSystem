<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

// Charger les variables d'environnement
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../.env');

use Doctrine\DBAL\DriverManager;

// Configuration de la base de données
$connectionParams = [
    'dbname' => $_ENV['DATABASE_NAME'],
    'user' => $_ENV['DATABASE_USER'],
    'password' => $_ENV['DATABASE_PASSWORD'],
    'host' => $_ENV['DATABASE_HOST'],
    'port' => $_ENV['DATABASE_PORT'],
    'driver' => 'pdo_mysql',
];

try {
    $connection = DriverManager::getConnection($connectionParams);
    
    echo "🔧 Correction des zones et emplacements des extincteurs...\n\n";
    
    // Récupérer tous les extincteurs
    $sql = "SELECT id, zone, emplacement FROM extincteur";
    $extincteurs = $connection->fetchAllAssociative($sql);
    
    $corrections = 0;
    
    foreach ($extincteurs as $extincteur) {
        $id = $extincteur['id'];
        $zoneActuelle = $extincteur['zone'];
        $emplacementActuel = $extincteur['emplacement'];
        
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
            $updateSql = "UPDATE extincteur SET zone = ?, emplacement = ? WHERE id = ?";
            $connection->executeStatement($updateSql, [$nouvelleZone, $nouvelEmplacement, $id]);
            
            echo "✅ Extincteur ID {$id}: Zone '{$zoneActuelle}' → '{$nouvelleZone}', Emplacement '{$emplacementActuel}' → '{$nouvelEmplacement}'\n";
            $corrections++;
        } else {
            echo "⚠️  Extincteur ID {$id}: Pas de correction nécessaire (Zone: '{$zoneActuelle}', Emplacement: '{$emplacementActuel}')\n";
        }
    }
    
    echo "\n🎉 Correction terminée ! {$corrections} extincteurs corrigés.\n";
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
