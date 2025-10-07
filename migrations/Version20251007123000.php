<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Fix NULL values in criteres column
 */
final class Version20251007123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix NULL values in criteres column for inspection_extincteur';
    }

    public function up(Schema $schema): void
    {
        // Update NULL values in criteres column to empty array
        $this->addSql("UPDATE inspection_extincteur SET criteres = '[]' WHERE criteres IS NULL");
    }

    public function down(Schema $schema): void
    {
        // No need to revert this change
    }
}
