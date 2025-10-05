<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251005201612 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_2F3497534B708B7B ON prise_pompier');
        $this->addSql('ALTER TABLE prise_pompier DROP numerotation, CHANGE dimatere dimatere VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE prise_pompier ADD numerotation VARCHAR(50) NOT NULL, CHANGE dimatere dimatere INT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2F3497534B708B7B ON prise_pompier (numerotation)');
    }
}
