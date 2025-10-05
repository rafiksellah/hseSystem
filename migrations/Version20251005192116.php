<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251005192116 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE issue_secours CHANGE barre_antipanique barre_antipanique VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE sirene ADD type VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE issue_secours CHANGE barre_antipanique barre_antipanique TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE sirene DROP type');
    }
}
