<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251008110832 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inspection_extincteur CHANGE is_active is_active TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE inspection_extinction_ram CHANGE is_active is_active TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE inspection_monte_charge CHANGE is_active is_active TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE inspection_sirene CHANGE is_active is_active TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE monte_charge ADD nombre_portes INT DEFAULT NULL');
        
        // Nettoyer les doublons avant d'ajouter la contrainte unique
        // Garder uniquement le premier enregistrement pour chaque numéro
        $this->addSql('
            DELETE mc1 FROM monte_charge mc1
            INNER JOIN monte_charge mc2 
            WHERE mc1.id > mc2.id 
            AND mc1.numero_monte_charge = mc2.numero_monte_charge
        ');
        
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C55A5D7BCECE3D55 ON monte_charge (numero_monte_charge)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inspection_extincteur CHANGE is_active is_active TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE inspection_extinction_ram CHANGE is_active is_active TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE inspection_monte_charge CHANGE is_active is_active TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE inspection_sirene CHANGE is_active is_active TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('DROP INDEX UNIQ_C55A5D7BCECE3D55 ON monte_charge');
        $this->addSql('ALTER TABLE monte_charge DROP nombre_portes');
    }
}
