<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260412211853 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE animal (id_animal INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, nom VARCHAR(255) NOT NULL, type_animal VARCHAR(255) NOT NULL, date_naissance DATE NOT NULL, sexe VARCHAR(255) NOT NULL, poids DOUBLE PRECISION NOT NULL, couleur VARCHAR(255) NOT NULL, date_enregistrement DATETIME NOT NULL, INDEX IDX_6AAB231F7E3C61F9 (owner_id), PRIMARY KEY(id_animal)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE clinique (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, code_postal VARCHAR(10) DEFAULT NULL, ville VARCHAR(100) DEFAULT NULL, telephone VARCHAR(20) NOT NULL, email VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, horaires_ouverture JSON DEFAULT NULL, site_web VARCHAR(255) DEFAULT NULL, actif TINYINT(1) NOT NULL, logo VARCHAR(255) DEFAULT NULL, services JSON DEFAULT NULL, tarif_consultation NUMERIC(10, 2) DEFAULT NULL, note_globale DOUBLE PRECISION DEFAULT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dossier_medical (id_dossier INT AUTO_INCREMENT NOT NULL, animal_id INT DEFAULT NULL, numero_dossier VARCHAR(255) NOT NULL, date_creation DATETIME NOT NULL, poids DOUBLE PRECISION DEFAULT NULL, etat VARCHAR(255) NOT NULL, images JSON DEFAULT NULL, notes_veterinaire VARCHAR(255) DEFAULT NULL, allergies VARCHAR(255) DEFAULT NULL, vaccinations VARCHAR(255) DEFAULT NULL, antecedents_medicaux VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_3581EE628E962C16 (animal_id), PRIMARY KEY(id_dossier)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE medicament (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description VARCHAR(1000) DEFAULT NULL, stock INT DEFAULT NULL, statut VARCHAR(20) DEFAULT NULL, date_creation DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE password_reset_tokens (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, verification_code VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_used TINYINT(1) DEFAULT 0 NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, user_agent VARCHAR(255) DEFAULT NULL, used_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', attempts INT DEFAULT 0 NOT NULL, token_type VARCHAR(20) DEFAULT \'verification\' NOT NULL, metadata JSON DEFAULT NULL, INDEX IDX_3967A216A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rendezvous (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, animal_id INT DEFAULT NULL, clinique_id INT DEFAULT NULL, date_heure DATETIME NOT NULL, type VARCHAR(100) NOT NULL, statut VARCHAR(50) NOT NULL, notes_client LONGTEXT DEFAULT NULL, notes_veterinaire LONGTEXT DEFAULT NULL, statut_paiement VARCHAR(50) NOT NULL, montant_paiement NUMERIC(10, 2) DEFAULT NULL, methode_paiement VARCHAR(50) DEFAULT NULL, date_paiement DATETIME DEFAULT NULL, nom_client VARCHAR(255) NOT NULL, email_client VARCHAR(255) DEFAULT NULL, telephone_client VARCHAR(20) DEFAULT NULL, nom_animal VARCHAR(255) DEFAULT NULL, espece_animal VARCHAR(50) DEFAULT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME DEFAULT NULL, INDEX IDX_C09A9BA819EB6921 (client_id), INDEX IDX_C09A9BA88E962C16 (animal_id), INDEX IDX_C09A9BA8265183A3 (clinique_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE traitement (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description VARCHAR(1000) DEFAULT NULL, statut VARCHAR(20) DEFAULT NULL, date_creation DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE traitement_medicament (traitement_id INT NOT NULL, medicament_id INT NOT NULL, INDEX IDX_7E796CD5DDA344B6 (traitement_id), INDEX IDX_7E796CD5AB0D61F7 (medicament_id), PRIMARY KEY(traitement_id, medicament_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, address VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', phone VARCHAR(20) DEFAULT NULL, profile_photo VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE animal ADD CONSTRAINT FK_6AAB231F7E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE dossier_medical ADD CONSTRAINT FK_3581EE628E962C16 FOREIGN KEY (animal_id) REFERENCES animal (id_animal)');
        $this->addSql('ALTER TABLE password_reset_tokens ADD CONSTRAINT FK_3967A216A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rendezvous ADD CONSTRAINT FK_C09A9BA819EB6921 FOREIGN KEY (client_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE rendezvous ADD CONSTRAINT FK_C09A9BA88E962C16 FOREIGN KEY (animal_id) REFERENCES animal (id_animal) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE rendezvous ADD CONSTRAINT FK_C09A9BA8265183A3 FOREIGN KEY (clinique_id) REFERENCES clinique (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE traitement_medicament ADD CONSTRAINT FK_7E796CD5DDA344B6 FOREIGN KEY (traitement_id) REFERENCES traitement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE traitement_medicament ADD CONSTRAINT FK_7E796CD5AB0D61F7 FOREIGN KEY (medicament_id) REFERENCES medicament (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE animal DROP FOREIGN KEY FK_6AAB231F7E3C61F9');
        $this->addSql('ALTER TABLE dossier_medical DROP FOREIGN KEY FK_3581EE628E962C16');
        $this->addSql('ALTER TABLE password_reset_tokens DROP FOREIGN KEY FK_3967A216A76ED395');
        $this->addSql('ALTER TABLE rendezvous DROP FOREIGN KEY FK_C09A9BA819EB6921');
        $this->addSql('ALTER TABLE rendezvous DROP FOREIGN KEY FK_C09A9BA88E962C16');
        $this->addSql('ALTER TABLE rendezvous DROP FOREIGN KEY FK_C09A9BA8265183A3');
        $this->addSql('ALTER TABLE traitement_medicament DROP FOREIGN KEY FK_7E796CD5DDA344B6');
        $this->addSql('ALTER TABLE traitement_medicament DROP FOREIGN KEY FK_7E796CD5AB0D61F7');
        $this->addSql('DROP TABLE animal');
        $this->addSql('DROP TABLE clinique');
        $this->addSql('DROP TABLE dossier_medical');
        $this->addSql('DROP TABLE medicament');
        $this->addSql('DROP TABLE password_reset_tokens');
        $this->addSql('DROP TABLE rendezvous');
        $this->addSql('DROP TABLE traitement');
        $this->addSql('DROP TABLE traitement_medicament');
        $this->addSql('DROP TABLE users');
    }
}
