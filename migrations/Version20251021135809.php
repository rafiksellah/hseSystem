<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251021135809 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_EMPLACEMENT_NOM_TYPE ON emplacement');
        $this->addSql('ALTER TABLE monte_charge DROP nombre_portes');
        $this->addSql('ALTER TABLE ria ADD emplacement VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EMPLACEMENT_NOM_TYPE ON emplacement (nom, type_equipement)');
        $this->addSql('ALTER TABLE monte_charge ADD nombre_portes INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ria DROP emplacement');
    }
}
