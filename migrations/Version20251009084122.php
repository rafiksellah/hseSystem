<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251009084122 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Supprimer les emplacements existants car ils n'ont pas de type_equipement
        $this->addSql('TRUNCATE TABLE emplacement');
        
        $this->addSql('DROP INDEX UNIQ_C0CF65F66C6E55B5 ON emplacement');
        $this->addSql('ALTER TABLE emplacement ADD type_equipement VARCHAR(50) NOT NULL, CHANGE zone zone VARCHAR(50) DEFAULT NULL');
        
        // Créer un index composé unique sur (nom, type_equipement)
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EMPLACEMENT_NOM_TYPE ON emplacement (nom, type_equipement)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE emplacement DROP type_equipement, CHANGE zone zone VARCHAR(50) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C0CF65F66C6E55B5 ON emplacement (nom)');
    }
}
