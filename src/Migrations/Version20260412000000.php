<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class  extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Safe migration placeholder - no schema changes';
    }

    public function up(Schema $schema): void
    {
        // No-op: database already in correct state
    }

    public function down(Schema $schema): void
    {
        // No-op
    }
}