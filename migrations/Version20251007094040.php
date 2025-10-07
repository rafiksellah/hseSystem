<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251007094040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inspection_monte_charge DROP FOREIGN KEY FK_917FBB528B611D24');
        $this->addSql('DROP INDEX IDX_917FBB528B611D24 ON inspection_monte_charge');
        $this->addSql('ALTER TABLE inspection_monte_charge ADD conformite VARCHAR(10) NOT NULL, DROP numero_porte, DROP photo_observation, DROP valide, DROP reponses, CHANGE inspecte_par_id inspecteur_id INT NOT NULL');
        $this->addSql('ALTER TABLE inspection_monte_charge ADD CONSTRAINT FK_917FBB52B7728AA0 FOREIGN KEY (inspecteur_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_917FBB52B7728AA0 ON inspection_monte_charge (inspecteur_id)');
        $this->addSql('ALTER TABLE monte_charge ADD numero_monte_charge VARCHAR(50) NOT NULL, ADD numero_porte VARCHAR(50) NOT NULL, CHANGE zone zone VARCHAR(50) NOT NULL, CHANGE type emplacement VARCHAR(100) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inspection_monte_charge DROP FOREIGN KEY FK_917FBB52B7728AA0');
        $this->addSql('DROP INDEX IDX_917FBB52B7728AA0 ON inspection_monte_charge');
        $this->addSql('ALTER TABLE inspection_monte_charge ADD numero_porte VARCHAR(50) NOT NULL, ADD photo_observation VARCHAR(255) DEFAULT NULL, ADD valide TINYINT(1) NOT NULL, ADD reponses JSON NOT NULL, DROP conformite, CHANGE inspecteur_id inspecte_par_id INT NOT NULL');
        $this->addSql('ALTER TABLE inspection_monte_charge ADD CONSTRAINT FK_917FBB528B611D24 FOREIGN KEY (inspecte_par_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_917FBB528B611D24 ON inspection_monte_charge (inspecte_par_id)');
        $this->addSql('ALTER TABLE monte_charge DROP numero_monte_charge, DROP numero_porte, CHANGE zone zone VARCHAR(100) NOT NULL, CHANGE emplacement type VARCHAR(100) NOT NULL');
    }
}
