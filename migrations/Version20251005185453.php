<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251005185453 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE desenfumage ADD etat_commande VARCHAR(100) DEFAULT NULL, ADD etat_skydome VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE extinction_localisee_ram ADD vanne VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE issue_secours ADD type VARCHAR(100) DEFAULT NULL, ADD barre_antipanique TINYINT(1) DEFAULT NULL, DROP emplacement');
        $this->addSql('ALTER TABLE prise_pompier ADD emplacement VARCHAR(255) DEFAULT NULL, ADD dimatere INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE desenfumage DROP etat_commande, DROP etat_skydome');
        $this->addSql('ALTER TABLE extinction_localisee_ram DROP vanne');
        $this->addSql('ALTER TABLE issue_secours ADD emplacement VARCHAR(255) DEFAULT NULL, DROP type, DROP barre_antipanique');
        $this->addSql('ALTER TABLE prise_pompier DROP emplacement, DROP dimatere');
    }
}
