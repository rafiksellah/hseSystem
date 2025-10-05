<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251005170926 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE inspection_ria (id INT AUTO_INCREMENT NOT NULL, ria_id INT NOT NULL, inspecte_par_id INT NOT NULL, criteres JSON NOT NULL, valide TINYINT(1) NOT NULL, date_inspection DATETIME NOT NULL, observations LONGTEXT DEFAULT NULL, photo_observation VARCHAR(255) DEFAULT NULL, INDEX IDX_5BF66CF779909411 (ria_id), INDEX IDX_5BF66CF78B611D24 (inspecte_par_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE inspection_ria ADD CONSTRAINT FK_5BF66CF779909411 FOREIGN KEY (ria_id) REFERENCES ria (id)');
        $this->addSql('ALTER TABLE inspection_ria ADD CONSTRAINT FK_5BF66CF78B611D24 FOREIGN KEY (inspecte_par_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inspection_ria DROP FOREIGN KEY FK_5BF66CF779909411');
        $this->addSql('ALTER TABLE inspection_ria DROP FOREIGN KEY FK_5BF66CF78B611D24');
        $this->addSql('DROP TABLE inspection_ria');
    }
}
