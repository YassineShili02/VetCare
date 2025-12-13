<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251120105927 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE medicament ADD stock INT DEFAULT NULL, ADD date_creation DATETIME NOT NULL, CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE description description VARCHAR(1000) DEFAULT NULL');
        $this->addSql('ALTER TABLE traitement ADD statut VARCHAR(20) DEFAULT NULL, ADD date_creation DATETIME NOT NULL, CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE description description VARCHAR(1000) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE medicament DROP stock, DROP date_creation, CHANGE nom nom VARCHAR(150) NOT NULL, CHANGE description description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE traitement DROP statut, DROP date_creation, CHANGE nom nom VARCHAR(150) NOT NULL, CHANGE description description LONGTEXT DEFAULT NULL');
    }
}
