<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251202001620 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix JSON images column for MariaDB';
    }

    public function up(Schema $schema): void
    {
        // 1️⃣ Convertir les anciennes valeurs vides en JSON valide
        $this->addSql("UPDATE dossier_medical SET images = '[]' WHERE images IS NULL OR images = ''");

        // 2️⃣ Reformater proprement la colonne JSON
        $this->addSql("ALTER TABLE dossier_medical MODIFY images LONGTEXT NULL COMMENT '(DC2Type:json)'");
    }

    public function down(Schema $schema): void
    {
        // Retour en NOT NULL si besoin
        $this->addSql("ALTER TABLE dossier_medical MODIFY images LONGTEXT NOT NULL COMMENT '(DC2Type:json)'");
    }
}
