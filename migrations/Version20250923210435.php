<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250923210435 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE extincteur (id INT AUTO_INCREMENT NOT NULL, cree_par_id INT NOT NULL, modifie_par_id INT DEFAULT NULL, valide_par_id INT DEFAULT NULL, numero VARCHAR(50) NOT NULL, zone VARCHAR(100) NOT NULL, emplacement VARCHAR(200) DEFAULT NULL, type VARCHAR(100) DEFAULT NULL, date_installation DATE DEFAULT NULL, date_revision DATE DEFAULT NULL, capacite VARCHAR(100) DEFAULT NULL, statut VARCHAR(50) DEFAULT NULL, observations LONGTEXT DEFAULT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME DEFAULT NULL, valide TINYINT(1) NOT NULL, date_validation DATETIME DEFAULT NULL, etat_validation VARCHAR(20) DEFAULT NULL, commentaire_validation LONGTEXT DEFAULT NULL, INDEX IDX_36590C7FC29C013 (cree_par_id), INDEX IDX_36590C7553B2554 (modifie_par_id), INDEX IDX_36590C76AF12ED9 (valide_par_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inspection_extincteur (id INT AUTO_INCREMENT NOT NULL, extincteur_id INT NOT NULL, inspecte_par_id INT NOT NULL, date_inspection DATETIME NOT NULL, accessibilite TINYINT(1) DEFAULT NULL, signalisation TINYINT(1) DEFAULT NULL, etat_general TINYINT(1) DEFAULT NULL, manometre TINYINT(1) DEFAULT NULL, scelles TINYINT(1) DEFAULT NULL, etiquetage TINYINT(1) DEFAULT NULL, support_fixation TINYINT(1) DEFAULT NULL, bouchon_securite TINYINT(1) DEFAULT NULL, goupille TINYINT(1) DEFAULT NULL, poids TINYINT(1) DEFAULT NULL, corrosion TINYINT(1) DEFAULT NULL, date_revision TINYINT(1) DEFAULT NULL, observations LONGTEXT DEFAULT NULL, resultat_global VARCHAR(50) NOT NULL, terminee TINYINT(1) NOT NULL, date_creation DATETIME NOT NULL, INDEX IDX_66290398BE385A3B (extincteur_id), INDEX IDX_662903988B611D24 (inspecte_par_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inspection_monte_charge (id INT AUTO_INCREMENT NOT NULL, monte_charge_id INT NOT NULL, inspecte_par_id INT NOT NULL, date_inspection DATETIME NOT NULL, numero_porte VARCHAR(50) NOT NULL, porte_fonctionne TINYINT(1) DEFAULT NULL, securite_ouverture TINYINT(1) DEFAULT NULL, arret_urgence TINYINT(1) DEFAULT NULL, eclairage_interieur TINYINT(1) DEFAULT NULL, boutons_commande TINYINT(1) DEFAULT NULL, alarme_sonore TINYINT(1) DEFAULT NULL, ventilation TINYINT(1) DEFAULT NULL, detection_surcharge TINYINT(1) DEFAULT NULL, nivelage TINYINT(1) DEFAULT NULL, vibrations_anormales TINYINT(1) DEFAULT NULL, bruit_anormal TINYINT(1) DEFAULT NULL, cables_etat TINYINT(1) DEFAULT NULL, observations LONGTEXT DEFAULT NULL, photo_observation VARCHAR(255) DEFAULT NULL, resultat_global VARCHAR(50) NOT NULL, terminee TINYINT(1) NOT NULL, date_creation DATETIME NOT NULL, INDEX IDX_917FBB52BA4ED9B7 (monte_charge_id), INDEX IDX_917FBB528B611D24 (inspecte_par_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE monte_charge (id INT AUTO_INCREMENT NOT NULL, cree_par_id INT NOT NULL, modifie_par_id INT DEFAULT NULL, nom VARCHAR(50) NOT NULL, zone VARCHAR(100) NOT NULL, emplacement VARCHAR(200) DEFAULT NULL, modele VARCHAR(50) DEFAULT NULL, date_mise_service DATE DEFAULT NULL, capacite_charge VARCHAR(50) DEFAULT NULL, statut VARCHAR(50) DEFAULT NULL, observations LONGTEXT DEFAULT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME DEFAULT NULL, INDEX IDX_C55A5D7BFC29C013 (cree_par_id), INDEX IDX_C55A5D7B553B2554 (modifie_par_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ria (id INT AUTO_INCREMENT NOT NULL, cree_par_id INT NOT NULL, modifie_par_id INT DEFAULT NULL, valide_par_id INT DEFAULT NULL, numero VARCHAR(50) NOT NULL, zone VARCHAR(100) NOT NULL, emplacement VARCHAR(200) DEFAULT NULL, etage VARCHAR(100) DEFAULT NULL, diametre VARCHAR(100) DEFAULT NULL, date_installation DATE DEFAULT NULL, date_derniere_verification DATE DEFAULT NULL, statut VARCHAR(50) DEFAULT NULL, observations LONGTEXT DEFAULT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME DEFAULT NULL, valide TINYINT(1) NOT NULL, date_validation DATETIME DEFAULT NULL, etat_validation VARCHAR(20) DEFAULT NULL, commentaire_validation LONGTEXT DEFAULT NULL, presence_eau TINYINT(1) DEFAULT NULL, pression_adequate TINYINT(1) DEFAULT NULL, INDEX IDX_26BEE40CFC29C013 (cree_par_id), INDEX IDX_26BEE40C553B2554 (modifie_par_id), INDEX IDX_26BEE40C6AF12ED9 (valide_par_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE extincteur ADD CONSTRAINT FK_36590C7FC29C013 FOREIGN KEY (cree_par_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE extincteur ADD CONSTRAINT FK_36590C7553B2554 FOREIGN KEY (modifie_par_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE extincteur ADD CONSTRAINT FK_36590C76AF12ED9 FOREIGN KEY (valide_par_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE inspection_extincteur ADD CONSTRAINT FK_66290398BE385A3B FOREIGN KEY (extincteur_id) REFERENCES extincteur (id)');
        $this->addSql('ALTER TABLE inspection_extincteur ADD CONSTRAINT FK_662903988B611D24 FOREIGN KEY (inspecte_par_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE inspection_monte_charge ADD CONSTRAINT FK_917FBB52BA4ED9B7 FOREIGN KEY (monte_charge_id) REFERENCES monte_charge (id)');
        $this->addSql('ALTER TABLE inspection_monte_charge ADD CONSTRAINT FK_917FBB528B611D24 FOREIGN KEY (inspecte_par_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE monte_charge ADD CONSTRAINT FK_C55A5D7BFC29C013 FOREIGN KEY (cree_par_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE monte_charge ADD CONSTRAINT FK_C55A5D7B553B2554 FOREIGN KEY (modifie_par_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE ria ADD CONSTRAINT FK_26BEE40CFC29C013 FOREIGN KEY (cree_par_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE ria ADD CONSTRAINT FK_26BEE40C553B2554 FOREIGN KEY (modifie_par_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE ria ADD CONSTRAINT FK_26BEE40C6AF12ED9 FOREIGN KEY (valide_par_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE extincteur DROP FOREIGN KEY FK_36590C7FC29C013');
        $this->addSql('ALTER TABLE extincteur DROP FOREIGN KEY FK_36590C7553B2554');
        $this->addSql('ALTER TABLE extincteur DROP FOREIGN KEY FK_36590C76AF12ED9');
        $this->addSql('ALTER TABLE inspection_extincteur DROP FOREIGN KEY FK_66290398BE385A3B');
        $this->addSql('ALTER TABLE inspection_extincteur DROP FOREIGN KEY FK_662903988B611D24');
        $this->addSql('ALTER TABLE inspection_monte_charge DROP FOREIGN KEY FK_917FBB52BA4ED9B7');
        $this->addSql('ALTER TABLE inspection_monte_charge DROP FOREIGN KEY FK_917FBB528B611D24');
        $this->addSql('ALTER TABLE monte_charge DROP FOREIGN KEY FK_C55A5D7BFC29C013');
        $this->addSql('ALTER TABLE monte_charge DROP FOREIGN KEY FK_C55A5D7B553B2554');
        $this->addSql('ALTER TABLE ria DROP FOREIGN KEY FK_26BEE40CFC29C013');
        $this->addSql('ALTER TABLE ria DROP FOREIGN KEY FK_26BEE40C553B2554');
        $this->addSql('ALTER TABLE ria DROP FOREIGN KEY FK_26BEE40C6AF12ED9');
        $this->addSql('DROP TABLE extincteur');
        $this->addSql('DROP TABLE inspection_extincteur');
        $this->addSql('DROP TABLE inspection_monte_charge');
        $this->addSql('DROP TABLE monte_charge');
        $this->addSql('DROP TABLE ria');
    }
}
