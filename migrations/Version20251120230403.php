<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251120230403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rendezvous (id INT AUTO_INCREMENT NOT NULL, veterinaire_id INT DEFAULT NULL, date_heure DATETIME NOT NULL, type VARCHAR(100) NOT NULL, statut VARCHAR(50) NOT NULL, notes_client LONGTEXT DEFAULT NULL, notes_veterinaire LONGTEXT DEFAULT NULL, statut_paiement VARCHAR(50) NOT NULL, montant_paiement NUMERIC(10, 2) DEFAULT NULL, methode_paiement VARCHAR(50) DEFAULT NULL, date_paiement DATETIME DEFAULT NULL, nom_client VARCHAR(255) NOT NULL, email_client VARCHAR(255) DEFAULT NULL, telephone_client VARCHAR(20) DEFAULT NULL, nom_animal VARCHAR(255) DEFAULT NULL, espece_animal VARCHAR(50) DEFAULT NULL, INDEX IDX_C09A9BA85C80924 (veterinaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE rendezvous ADD CONSTRAINT FK_C09A9BA85C80924 FOREIGN KEY (veterinaire_id) REFERENCES veterinaire (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rendezvous DROP FOREIGN KEY FK_C09A9BA85C80924');
        $this->addSql('DROP TABLE rendezvous');
    }
}
