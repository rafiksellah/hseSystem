<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251017092822 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inspection_monte_charge ADD numero_porte VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE monte_charge CHANGE numero_porte numero_porte JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inspection_monte_charge DROP numero_porte');
        $this->addSql('ALTER TABLE monte_charge CHANGE numero_porte numero_porte VARCHAR(50) NOT NULL');
    }
}
