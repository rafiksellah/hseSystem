<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251005153722 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE desenfumage (id INT AUTO_INCREMENT NOT NULL, numerotation VARCHAR(50) NOT NULL, zone VARCHAR(100) NOT NULL, emplacement VARCHAR(255) DEFAULT NULL, type VARCHAR(100) DEFAULT NULL, date_creation DATETIME NOT NULL, UNIQUE INDEX UNIQ_4E4A98934B708B7B (numerotation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE extinction_localisee_ram (id INT AUTO_INCREMENT NOT NULL, numerotation VARCHAR(50) NOT NULL, zone VARCHAR(100) NOT NULL, emplacement VARCHAR(255) DEFAULT NULL, type VARCHAR(100) DEFAULT NULL, date_creation DATETIME NOT NULL, UNIQUE INDEX UNIQ_AFBC1EC34B708B7B (numerotation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inspection_desenfumage (id INT AUTO_INCREMENT NOT NULL, desenfumage_id INT NOT NULL, inspecte_par_id INT NOT NULL, criteres JSON NOT NULL, valide TINYINT(1) NOT NULL, date_inspection DATETIME NOT NULL, observations LONGTEXT DEFAULT NULL, photo_observation VARCHAR(255) DEFAULT NULL, INDEX IDX_B5FB9865B6856B4B (desenfumage_id), INDEX IDX_B5FB98658B611D24 (inspecte_par_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inspection_extinction_ram (id INT AUTO_INCREMENT NOT NULL, extinction_localisee_ram_id INT NOT NULL, inspecte_par_id INT NOT NULL, criteres JSON NOT NULL, valide TINYINT(1) NOT NULL, date_inspection DATETIME NOT NULL, observations LONGTEXT DEFAULT NULL, photo_observation VARCHAR(255) DEFAULT NULL, INDEX IDX_F062264BE33E2DFE (extinction_localisee_ram_id), INDEX IDX_F062264B8B611D24 (inspecte_par_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inspection_issue_secours (id INT AUTO_INCREMENT NOT NULL, issue_secours_id INT NOT NULL, inspecte_par_id INT NOT NULL, criteres JSON NOT NULL, valide TINYINT(1) NOT NULL, date_inspection DATETIME NOT NULL, observations LONGTEXT DEFAULT NULL, photo_observation VARCHAR(255) DEFAULT NULL, INDEX IDX_C42308CB65A54E83 (issue_secours_id), INDEX IDX_C42308CB8B611D24 (inspecte_par_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inspection_prise_pompier (id INT AUTO_INCREMENT NOT NULL, prise_pompier_id INT NOT NULL, inspecte_par_id INT NOT NULL, criteres JSON NOT NULL, valide TINYINT(1) NOT NULL, date_inspection DATETIME NOT NULL, observations LONGTEXT DEFAULT NULL, photo_observation VARCHAR(255) DEFAULT NULL, INDEX IDX_6DD22AD9170FBD9E (prise_pompier_id), INDEX IDX_6DD22AD98B611D24 (inspecte_par_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inspection_sirene (id INT AUTO_INCREMENT NOT NULL, sirene_id INT NOT NULL, inspecte_par_id INT NOT NULL, criteres JSON NOT NULL, valide TINYINT(1) NOT NULL, date_inspection DATETIME NOT NULL, observations LONGTEXT DEFAULT NULL, photo_observation VARCHAR(255) DEFAULT NULL, INDEX IDX_D3163E7F70B7DDFA (sirene_id), INDEX IDX_D3163E7F8B611D24 (inspecte_par_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE issue_secours (id INT AUTO_INCREMENT NOT NULL, numerotation VARCHAR(50) NOT NULL, zone VARCHAR(100) NOT NULL, emplacement VARCHAR(255) DEFAULT NULL, date_creation DATETIME NOT NULL, UNIQUE INDEX UNIQ_86C5B5414B708B7B (numerotation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE prise_pompier (id INT AUTO_INCREMENT NOT NULL, numerotation VARCHAR(50) NOT NULL, zone VARCHAR(255) NOT NULL, date_creation DATETIME NOT NULL, UNIQUE INDEX UNIQ_2F3497534B708B7B (numerotation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sirene (id INT AUTO_INCREMENT NOT NULL, numerotation VARCHAR(50) NOT NULL, zone VARCHAR(100) NOT NULL, emplacement VARCHAR(255) DEFAULT NULL, date_creation DATETIME NOT NULL, UNIQUE INDEX UNIQ_E1DA79D24B708B7B (numerotation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE inspection_desenfumage ADD CONSTRAINT FK_B5FB9865B6856B4B FOREIGN KEY (desenfumage_id) REFERENCES desenfumage (id)');
        $this->addSql('ALTER TABLE inspection_desenfumage ADD CONSTRAINT FK_B5FB98658B611D24 FOREIGN KEY (inspecte_par_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE inspection_extinction_ram ADD CONSTRAINT FK_F062264BE33E2DFE FOREIGN KEY (extinction_localisee_ram_id) REFERENCES extinction_localisee_ram (id)');
        $this->addSql('ALTER TABLE inspection_extinction_ram ADD CONSTRAINT FK_F062264B8B611D24 FOREIGN KEY (inspecte_par_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE inspection_issue_secours ADD CONSTRAINT FK_C42308CB65A54E83 FOREIGN KEY (issue_secours_id) REFERENCES issue_secours (id)');
        $this->addSql('ALTER TABLE inspection_issue_secours ADD CONSTRAINT FK_C42308CB8B611D24 FOREIGN KEY (inspecte_par_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE inspection_prise_pompier ADD CONSTRAINT FK_6DD22AD9170FBD9E FOREIGN KEY (prise_pompier_id) REFERENCES prise_pompier (id)');
        $this->addSql('ALTER TABLE inspection_prise_pompier ADD CONSTRAINT FK_6DD22AD98B611D24 FOREIGN KEY (inspecte_par_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE inspection_sirene ADD CONSTRAINT FK_D3163E7F70B7DDFA FOREIGN KEY (sirene_id) REFERENCES sirene (id)');
        $this->addSql('ALTER TABLE inspection_sirene ADD CONSTRAINT FK_D3163E7F8B611D24 FOREIGN KEY (inspecte_par_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inspection_desenfumage DROP FOREIGN KEY FK_B5FB9865B6856B4B');
        $this->addSql('ALTER TABLE inspection_desenfumage DROP FOREIGN KEY FK_B5FB98658B611D24');
        $this->addSql('ALTER TABLE inspection_extinction_ram DROP FOREIGN KEY FK_F062264BE33E2DFE');
        $this->addSql('ALTER TABLE inspection_extinction_ram DROP FOREIGN KEY FK_F062264B8B611D24');
        $this->addSql('ALTER TABLE inspection_issue_secours DROP FOREIGN KEY FK_C42308CB65A54E83');
        $this->addSql('ALTER TABLE inspection_issue_secours DROP FOREIGN KEY FK_C42308CB8B611D24');
        $this->addSql('ALTER TABLE inspection_prise_pompier DROP FOREIGN KEY FK_6DD22AD9170FBD9E');
        $this->addSql('ALTER TABLE inspection_prise_pompier DROP FOREIGN KEY FK_6DD22AD98B611D24');
        $this->addSql('ALTER TABLE inspection_sirene DROP FOREIGN KEY FK_D3163E7F70B7DDFA');
        $this->addSql('ALTER TABLE inspection_sirene DROP FOREIGN KEY FK_D3163E7F8B611D24');
        $this->addSql('DROP TABLE desenfumage');
        $this->addSql('DROP TABLE extinction_localisee_ram');
        $this->addSql('DROP TABLE inspection_desenfumage');
        $this->addSql('DROP TABLE inspection_extinction_ram');
        $this->addSql('DROP TABLE inspection_issue_secours');
        $this->addSql('DROP TABLE inspection_prise_pompier');
        $this->addSql('DROP TABLE inspection_sirene');
        $this->addSql('DROP TABLE issue_secours');
        $this->addSql('DROP TABLE prise_pompier');
        $this->addSql('DROP TABLE sirene');
    }
}
