<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250925194925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE extincteur DROP FOREIGN KEY FK_36590C7553B2554');
        $this->addSql('ALTER TABLE extincteur DROP FOREIGN KEY FK_36590C7FC29C013');
        $this->addSql('DROP INDEX IDX_36590C7553B2554 ON extincteur');
        $this->addSql('DROP INDEX IDX_36590C7FC29C013 ON extincteur');
        $this->addSql('ALTER TABLE extincteur ADD agent_extincteur VARCHAR(100) DEFAULT NULL, ADD date_fabrication DATE DEFAULT NULL, ADD date_epreuve DATE DEFAULT NULL, ADD date_fin_de_vie DATE DEFAULT NULL, ADD numero_serie VARCHAR(100) DEFAULT NULL, DROP cree_par_id, DROP modifie_par_id, DROP date_installation, DROP date_revision, DROP statut, DROP observations, DROP date_modification, DROP etat_validation, DROP commentaire_validation, CHANGE emplacement emplacement VARCHAR(255) DEFAULT NULL, CHANGE type type VARCHAR(50) DEFAULT NULL, CHANGE capacite capacite VARCHAR(50) DEFAULT NULL, CHANGE numero numerotation VARCHAR(50) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_36590C74B708B7B ON extincteur (numerotation)');
        $this->addSql('ALTER TABLE inspection_extincteur ADD criteres JSON NOT NULL, DROP accessibilite, DROP signalisation, DROP etat_general, DROP manometre, DROP scelles, DROP etiquetage, DROP support_fixation, DROP bouchon_securite, DROP goupille, DROP poids, DROP corrosion, DROP date_revision, DROP resultat_global, DROP date_creation, CHANGE terminee valide TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE inspection_monte_charge ADD reponses JSON NOT NULL, DROP porte_fonctionne, DROP securite_ouverture, DROP arret_urgence, DROP eclairage_interieur, DROP boutons_commande, DROP alarme_sonore, DROP ventilation, DROP detection_surcharge, DROP nivelage, DROP vibrations_anormales, DROP bruit_anormal, DROP cables_etat, DROP resultat_global, DROP date_creation, CHANGE terminee valide TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE monte_charge DROP FOREIGN KEY FK_C55A5D7B553B2554');
        $this->addSql('ALTER TABLE monte_charge DROP FOREIGN KEY FK_C55A5D7BFC29C013');
        $this->addSql('DROP INDEX IDX_C55A5D7B553B2554 ON monte_charge');
        $this->addSql('DROP INDEX IDX_C55A5D7BFC29C013 ON monte_charge');
        $this->addSql('ALTER TABLE monte_charge ADD type VARCHAR(100) NOT NULL, DROP cree_par_id, DROP modifie_par_id, DROP nom, DROP emplacement, DROP modele, DROP date_mise_service, DROP capacite_charge, DROP statut, DROP observations, DROP date_modification');
        $this->addSql('ALTER TABLE ria DROP FOREIGN KEY FK_26BEE40C553B2554');
        $this->addSql('ALTER TABLE ria DROP FOREIGN KEY FK_26BEE40CFC29C013');
        $this->addSql('DROP INDEX IDX_26BEE40C553B2554 ON ria');
        $this->addSql('DROP INDEX IDX_26BEE40CFC29C013 ON ria');
        $this->addSql('ALTER TABLE ria ADD longueur INT DEFAULT NULL, DROP cree_par_id, DROP emplacement, DROP etage, DROP diametre, DROP date_installation, DROP date_derniere_verification, DROP observations, DROP date_modification, DROP etat_validation, DROP commentaire_validation, DROP presence_eau, DROP pression_adequate, CHANGE numero numerotation VARCHAR(50) NOT NULL, CHANGE statut agent_extincteur VARCHAR(50) DEFAULT NULL, CHANGE modifie_par_id dimatere INT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_26BEE40C4B708B7B ON ria (numerotation)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_36590C74B708B7B ON extincteur');
        $this->addSql('ALTER TABLE extincteur ADD cree_par_id INT NOT NULL, ADD modifie_par_id INT DEFAULT NULL, ADD date_installation DATE DEFAULT NULL, ADD date_revision DATE DEFAULT NULL, ADD statut VARCHAR(50) DEFAULT NULL, ADD observations LONGTEXT DEFAULT NULL, ADD date_modification DATETIME DEFAULT NULL, ADD etat_validation VARCHAR(20) DEFAULT NULL, ADD commentaire_validation LONGTEXT DEFAULT NULL, DROP agent_extincteur, DROP date_fabrication, DROP date_epreuve, DROP date_fin_de_vie, DROP numero_serie, CHANGE emplacement emplacement VARCHAR(200) DEFAULT NULL, CHANGE type type VARCHAR(100) DEFAULT NULL, CHANGE capacite capacite VARCHAR(100) DEFAULT NULL, CHANGE numerotation numero VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE extincteur ADD CONSTRAINT FK_36590C7553B2554 FOREIGN KEY (modifie_par_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE extincteur ADD CONSTRAINT FK_36590C7FC29C013 FOREIGN KEY (cree_par_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_36590C7553B2554 ON extincteur (modifie_par_id)');
        $this->addSql('CREATE INDEX IDX_36590C7FC29C013 ON extincteur (cree_par_id)');
        $this->addSql('ALTER TABLE inspection_extincteur ADD accessibilite TINYINT(1) DEFAULT NULL, ADD signalisation TINYINT(1) DEFAULT NULL, ADD etat_general TINYINT(1) DEFAULT NULL, ADD manometre TINYINT(1) DEFAULT NULL, ADD scelles TINYINT(1) DEFAULT NULL, ADD etiquetage TINYINT(1) DEFAULT NULL, ADD support_fixation TINYINT(1) DEFAULT NULL, ADD bouchon_securite TINYINT(1) DEFAULT NULL, ADD goupille TINYINT(1) DEFAULT NULL, ADD poids TINYINT(1) DEFAULT NULL, ADD corrosion TINYINT(1) DEFAULT NULL, ADD date_revision TINYINT(1) DEFAULT NULL, ADD resultat_global VARCHAR(50) NOT NULL, ADD date_creation DATETIME NOT NULL, DROP criteres, CHANGE valide terminee TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE inspection_monte_charge ADD porte_fonctionne TINYINT(1) DEFAULT NULL, ADD securite_ouverture TINYINT(1) DEFAULT NULL, ADD arret_urgence TINYINT(1) DEFAULT NULL, ADD eclairage_interieur TINYINT(1) DEFAULT NULL, ADD boutons_commande TINYINT(1) DEFAULT NULL, ADD alarme_sonore TINYINT(1) DEFAULT NULL, ADD ventilation TINYINT(1) DEFAULT NULL, ADD detection_surcharge TINYINT(1) DEFAULT NULL, ADD nivelage TINYINT(1) DEFAULT NULL, ADD vibrations_anormales TINYINT(1) DEFAULT NULL, ADD bruit_anormal TINYINT(1) DEFAULT NULL, ADD cables_etat TINYINT(1) DEFAULT NULL, ADD resultat_global VARCHAR(50) NOT NULL, ADD date_creation DATETIME NOT NULL, DROP reponses, CHANGE valide terminee TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE monte_charge ADD cree_par_id INT NOT NULL, ADD modifie_par_id INT DEFAULT NULL, ADD nom VARCHAR(50) NOT NULL, ADD emplacement VARCHAR(200) DEFAULT NULL, ADD modele VARCHAR(50) DEFAULT NULL, ADD date_mise_service DATE DEFAULT NULL, ADD capacite_charge VARCHAR(50) DEFAULT NULL, ADD statut VARCHAR(50) DEFAULT NULL, ADD observations LONGTEXT DEFAULT NULL, ADD date_modification DATETIME DEFAULT NULL, DROP type');
        $this->addSql('ALTER TABLE monte_charge ADD CONSTRAINT FK_C55A5D7B553B2554 FOREIGN KEY (modifie_par_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE monte_charge ADD CONSTRAINT FK_C55A5D7BFC29C013 FOREIGN KEY (cree_par_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_C55A5D7B553B2554 ON monte_charge (modifie_par_id)');
        $this->addSql('CREATE INDEX IDX_C55A5D7BFC29C013 ON monte_charge (cree_par_id)');
        $this->addSql('DROP INDEX UNIQ_26BEE40C4B708B7B ON ria');
        $this->addSql('ALTER TABLE ria ADD cree_par_id INT NOT NULL, ADD modifie_par_id INT DEFAULT NULL, ADD emplacement VARCHAR(200) DEFAULT NULL, ADD etage VARCHAR(100) DEFAULT NULL, ADD diametre VARCHAR(100) DEFAULT NULL, ADD date_installation DATE DEFAULT NULL, ADD date_derniere_verification DATE DEFAULT NULL, ADD observations LONGTEXT DEFAULT NULL, ADD date_modification DATETIME DEFAULT NULL, ADD etat_validation VARCHAR(20) DEFAULT NULL, ADD commentaire_validation LONGTEXT DEFAULT NULL, ADD presence_eau TINYINT(1) DEFAULT NULL, ADD pression_adequate TINYINT(1) DEFAULT NULL, DROP dimatere, DROP longueur, CHANGE numerotation numero VARCHAR(50) NOT NULL, CHANGE agent_extincteur statut VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE ria ADD CONSTRAINT FK_26BEE40C553B2554 FOREIGN KEY (modifie_par_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE ria ADD CONSTRAINT FK_26BEE40CFC29C013 FOREIGN KEY (cree_par_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_26BEE40C553B2554 ON ria (modifie_par_id)');
        $this->addSql('CREATE INDEX IDX_26BEE40CFC29C013 ON ria (cree_par_id)');
    }
}
