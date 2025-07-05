<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250702212617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rapport_hse (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, code_agt VARCHAR(20) NOT NULL, nom VARCHAR(100) NOT NULL, date DATE NOT NULL, heure TIME NOT NULL, zone VARCHAR(100) DEFAULT NULL, emplacement VARCHAR(200) DEFAULT NULL, equipement_produit_concerne VARCHAR(200) DEFAULT NULL, description_anomalie LONGTEXT DEFAULT NULL, cause_probable VARCHAR(200) DEFAULT NULL, photo_constat VARCHAR(255) DEFAULT NULL, action_cloturee VARCHAR(200) DEFAULT NULL, date_cloture DATE DEFAULT NULL, heure_action TIME DEFAULT NULL, photo_action_cloturee VARCHAR(255) DEFAULT NULL, date_creation DATETIME NOT NULL, statut VARCHAR(50) NOT NULL, INDEX IDX_74398B92A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, code_agent VARCHAR(20) NOT NULL, date_creation DATETIME NOT NULL, heure_creation TIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649CADE85D1 (code_agent), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE rapport_hse ADD CONSTRAINT FK_74398B92A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rapport_hse DROP FOREIGN KEY FK_74398B92A76ED395');
        $this->addSql('DROP TABLE rapport_hse');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
