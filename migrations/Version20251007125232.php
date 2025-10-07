<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251007125232 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add reset inspection system with archive table and reset fields to all inspection entities';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reset_inspection (id INT AUTO_INCREMENT NOT NULL, reset_by_id INT DEFAULT NULL, equipment_type VARCHAR(50) NOT NULL, equipment_id INT NOT NULL, equipment_name VARCHAR(100) NOT NULL, inspection_data JSON NOT NULL, reset_date DATETIME NOT NULL, reset_type VARCHAR(20) NOT NULL, reset_reason VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_9667160E22185D26 (reset_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reset_inspection ADD CONSTRAINT FK_9667160E22185D26 FOREIGN KEY (reset_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE inspection_extincteur ADD is_active TINYINT(1) NOT NULL DEFAULT 1, ADD reset_date DATETIME DEFAULT NULL, ADD reset_reason VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE inspection_extinction_ram ADD is_active TINYINT(1) NOT NULL DEFAULT 1, ADD reset_date DATETIME DEFAULT NULL, ADD reset_reason VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE inspection_monte_charge ADD is_active TINYINT(1) NOT NULL DEFAULT 1, ADD reset_date DATETIME DEFAULT NULL, ADD reset_reason VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE inspection_sirene ADD is_active TINYINT(1) NOT NULL DEFAULT 1, ADD reset_date DATETIME DEFAULT NULL, ADD reset_reason VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reset_inspection DROP FOREIGN KEY FK_9667160E22185D26');
        $this->addSql('DROP TABLE reset_inspection');
        $this->addSql('ALTER TABLE inspection_extincteur DROP is_active, DROP reset_date, DROP reset_reason');
        $this->addSql('ALTER TABLE inspection_extinction_ram DROP is_active, DROP reset_date, DROP reset_reason');
        $this->addSql('ALTER TABLE inspection_monte_charge DROP is_active, DROP reset_date, DROP reset_reason');
        $this->addSql('ALTER TABLE inspection_sirene DROP is_active, DROP reset_date, DROP reset_reason');
    }
}
