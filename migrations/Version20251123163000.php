<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251123163000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la colonne clinique_id à veterinaire';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE veterinaire ADD clinique_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE veterinaire ADD CONSTRAINT FK_VETERINAIRE_CLINIQUE FOREIGN KEY (clinique_id) REFERENCES clinique (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE veterinaire DROP FOREIGN KEY FK_VETERINAIRE_CLINIQUE');
        $this->addSql('ALTER TABLE veterinaire DROP COLUMN clinique_id');
    }
}
