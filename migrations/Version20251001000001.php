<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251001XXXXXX extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la colonne photo_observation si elle n’existe pas';
    }

    public function up(Schema $schema): void
    {
        $sm = $this->connection->createSchemaManager();

        // ⚠️ Remplace 'inspection_extincteur' par le vrai nom de ta table
        $table = $sm->introspectTable('inspection_extincteur');

        if (!$table->hasColumn('photo_observation')) {
            $this->addSql("ALTER TABLE inspection_extincteur ADD COLUMN photo_observation VARCHAR(255) DEFAULT NULL");
        }
    }

    public function down(Schema $schema): void
    {
        $sm = $this->connection->createSchemaManager();
        $table = $sm->introspectTable('inspection_extincteur');

        if ($table->hasColumn('photo_observation')) {
            $this->addSql("ALTER TABLE inspection_extincteur DROP COLUMN photo_observation");
        }
    }
}
