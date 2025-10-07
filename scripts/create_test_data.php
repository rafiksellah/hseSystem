<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

// Charger les variables d'environnement
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../.env');

// Créer l'application Symfony
$kernel = new \App\Kernel('dev', true);
$kernel->boot();

$container = $kernel->getContainer();
$entityManager = $container->get('doctrine.orm.entity_manager');

echo "Création de données de test pour la pagination...\n";

// Créer des extincteurs de test
for ($i = 1; $i <= 50; $i++) {
    $extincteur = new \App\Entity\Extincteur();
    $extincteur->setNumerotation("EXT-TEST-" . str_pad($i, 3, '0', STR_PAD_LEFT));
    $extincteur->setZone($i % 2 == 0 ? 'SIMTIS' : 'SIMTIS TISSAGE');
    $extincteur->setEmplacement($i % 2 == 0 ? 'Bâtiment confection' : 'RDC TISSAGE');
    $extincteur->setType('Poudre ABC 6kg');
    $extincteur->setCapacite('6 kg');
    $extincteur->setAgentExtincteur('Agent ' . $i);
    $extincteur->setNumeroSerie('SN' . str_pad($i, 6, '0', STR_PAD_LEFT));
    
    $entityManager->persist($extincteur);
}

// Créer des rapports de test
for ($i = 1; $i <= 30; $i++) {
    $rapport = new \App\Entity\RapportHSE();
    $rapport->setCodeAgt('AGT' . str_pad($i, 3, '0', STR_PAD_LEFT));
    $rapport->setNom('Utilisateur Test ' . $i);
    $rapport->setZone($i % 2 == 0 ? 'SIMTIS' : 'SIMTIS TISSAGE');
    $rapport->setDateCreation(new \DateTime());
    $rapport->setHeure(new \DateTime());
    $rapport->setStatut('En cours');
    
    $entityManager->persist($rapport);
}

$entityManager->flush();

echo "Données de test créées avec succès !\n";
echo "- 50 extincteurs de test\n";
echo "- 30 rapports de test\n";
