<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251011154135 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convert numero_porte from VARCHAR to JSON to support multiple doors per monte_charge';
    }

    public function up(Schema $schema): void
    {
        // Étape 1: Créer une colonne temporaire pour stocker les nouvelles valeurs JSON
        $this->addSql('ALTER TABLE monte_charge ADD numero_porte_temp JSON DEFAULT NULL');
        
        // Étape 2: Convertir les valeurs existantes en tableau JSON
        $this->addSql('UPDATE monte_charge SET numero_porte_temp = JSON_ARRAY(numero_porte) WHERE numero_porte IS NOT NULL');
        
        // Étape 3: Supprimer l'ancienne colonne
        $this->addSql('ALTER TABLE monte_charge DROP numero_porte');
        
        // Étape 4: Renommer la colonne temporaire
        $this->addSql('ALTER TABLE monte_charge CHANGE numero_porte_temp numero_porte JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // Étape 1: Créer une colonne temporaire VARCHAR
        $this->addSql('ALTER TABLE monte_charge ADD numero_porte_temp VARCHAR(50) DEFAULT NULL');
        
        // Étape 2: Récupérer la première porte du tableau JSON
        $this->addSql('UPDATE monte_charge SET numero_porte_temp = JSON_UNQUOTE(JSON_EXTRACT(numero_porte, "$[0]")) WHERE numero_porte IS NOT NULL');
        
        // Étape 3: Supprimer l'ancienne colonne JSON
        $this->addSql('ALTER TABLE monte_charge DROP numero_porte');
        
        // Étape 4: Renommer la colonne temporaire
        $this->addSql('ALTER TABLE monte_charge CHANGE numero_porte_temp numero_porte VARCHAR(50) NOT NULL');
    }
}
