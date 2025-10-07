<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251007102243 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inspection_monte_charge ADD consignes_respectees VARCHAR(10) NOT NULL, ADD fins_courses_fonctionnent VARCHAR(10) NOT NULL, ADD essai_vide_realise VARCHAR(10) NOT NULL, CHANGE conformite portes_fermees VARCHAR(10) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inspection_monte_charge ADD conformite VARCHAR(10) NOT NULL, DROP portes_fermees, DROP consignes_respectees, DROP fins_courses_fonctionnent, DROP essai_vide_realise');
    }
}
