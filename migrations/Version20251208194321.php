<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251208194321 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE animal (id_animal INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, type_animal VARCHAR(255) NOT NULL, date_naissance DATE NOT NULL, sexe VARCHAR(255) NOT NULL, poids DOUBLE PRECISION NOT NULL, couleur VARCHAR(255) NOT NULL, date_enregistrement DATETIME NOT NULL, PRIMARY KEY(id_animal)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dossier_medical (id_dossier INT AUTO_INCREMENT NOT NULL, animal_id INT DEFAULT NULL, numero_dossier VARCHAR(255) NOT NULL, date_creation DATETIME NOT NULL, poids DOUBLE PRECISION DEFAULT NULL, etat VARCHAR(255) NOT NULL, images JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', notes_veterinaire VARCHAR(255) DEFAULT NULL, allergies VARCHAR(255) DEFAULT NULL, vaccinations VARCHAR(255) DEFAULT NULL, antecedents_medicaux VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_3581EE628E962C16 (animal_id), PRIMARY KEY(id_dossier)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE medicament (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description VARCHAR(1000) DEFAULT NULL, stock INT DEFAULT NULL, statut VARCHAR(20) DEFAULT NULL, date_creation DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE traitement (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description VARCHAR(1000) DEFAULT NULL, statut VARCHAR(20) DEFAULT NULL, date_creation DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE traitement_medicament (traitement_id INT NOT NULL, medicament_id INT NOT NULL, INDEX IDX_7E796CD5DDA344B6 (traitement_id), INDEX IDX_7E796CD5AB0D61F7 (medicament_id), PRIMARY KEY(traitement_id, medicament_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, address VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', phone VARCHAR(20) DEFAULT NULL, profile_photo VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dossier_medical ADD CONSTRAINT FK_3581EE628E962C16 FOREIGN KEY (animal_id) REFERENCES animal (id_animal)');
        $this->addSql('ALTER TABLE traitement_medicament ADD CONSTRAINT FK_7E796CD5DDA344B6 FOREIGN KEY (traitement_id) REFERENCES traitement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE traitement_medicament ADD CONSTRAINT FK_7E796CD5AB0D61F7 FOREIGN KEY (medicament_id) REFERENCES medicament (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dossier_medical DROP FOREIGN KEY FK_3581EE628E962C16');
        $this->addSql('ALTER TABLE traitement_medicament DROP FOREIGN KEY FK_7E796CD5DDA344B6');
        $this->addSql('ALTER TABLE traitement_medicament DROP FOREIGN KEY FK_7E796CD5AB0D61F7');
        $this->addSql('DROP TABLE animal');
        $this->addSql('DROP TABLE dossier_medical');
        $this->addSql('DROP TABLE medicament');
        $this->addSql('DROP TABLE traitement');
        $this->addSql('DROP TABLE traitement_medicament');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
