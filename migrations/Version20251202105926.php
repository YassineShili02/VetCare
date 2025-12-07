<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251202105926 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE animal (id_animal INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, type_animal VARCHAR(255) NOT NULL, date_naissance DATE NOT NULL, sexe VARCHAR(255) NOT NULL, poids DOUBLE PRECISION NOT NULL, couleur VARCHAR(255) NOT NULL, date_enregistrement DATETIME NOT NULL, PRIMARY KEY(id_animal)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE clinique (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, code_postal VARCHAR(10) DEFAULT NULL, ville VARCHAR(100) DEFAULT NULL, telephone VARCHAR(20) NOT NULL, email VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, horaires_ouverture JSON DEFAULT NULL, site_web VARCHAR(255) DEFAULT NULL, actif TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE disponibilite_veterinaire (id INT AUTO_INCREMENT NOT NULL, veterinaire_id INT NOT NULL, jour_semaine INT NOT NULL, heure_debut TIME NOT NULL, heure_fin TIME NOT NULL, INDEX IDX_D30FF9FE5C80924 (veterinaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dossier_medical (id_dossier INT AUTO_INCREMENT NOT NULL, animal_id INT DEFAULT NULL, numero_dossier VARCHAR(255) NOT NULL, date_creation DATETIME NOT NULL, poids DOUBLE PRECISION DEFAULT NULL, etat VARCHAR(255) NOT NULL, images JSON DEFAULT NULL, notes_veterinaire VARCHAR(255) DEFAULT NULL, allergies VARCHAR(255) DEFAULT NULL, vaccinations VARCHAR(255) DEFAULT NULL, antecedents_medicaux VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_3581EE628E962C16 (animal_id), PRIMARY KEY(id_dossier)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rendezvous (id INT AUTO_INCREMENT NOT NULL, veterinaire_id INT DEFAULT NULL, date_heure DATETIME NOT NULL, type VARCHAR(100) NOT NULL, statut VARCHAR(50) NOT NULL, notes_client LONGTEXT DEFAULT NULL, notes_veterinaire LONGTEXT DEFAULT NULL, statut_paiement VARCHAR(50) NOT NULL, montant_paiement NUMERIC(10, 2) DEFAULT NULL, methode_paiement VARCHAR(50) DEFAULT NULL, date_paiement DATETIME DEFAULT NULL, nom_client VARCHAR(255) NOT NULL, email_client VARCHAR(255) DEFAULT NULL, telephone_client VARCHAR(20) DEFAULT NULL, nom_animal VARCHAR(255) DEFAULT NULL, espece_animal VARCHAR(50) DEFAULT NULL, INDEX IDX_C09A9BA85C80924 (veterinaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE veterinaire (id INT AUTO_INCREMENT NOT NULL, clinique_id INT DEFAULT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, email VARCHAR(255) NOT NULL, telephone VARCHAR(20) DEFAULT NULL, specialite VARCHAR(255) DEFAULT NULL, actif TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_E9D962B8E7927C74 (email), INDEX IDX_E9D962B8265183A3 (clinique_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE disponibilite_veterinaire ADD CONSTRAINT FK_D30FF9FE5C80924 FOREIGN KEY (veterinaire_id) REFERENCES veterinaire (id)');
        $this->addSql('ALTER TABLE dossier_medical ADD CONSTRAINT FK_3581EE628E962C16 FOREIGN KEY (animal_id) REFERENCES animal (id_animal)');
        $this->addSql('ALTER TABLE rendezvous ADD CONSTRAINT FK_C09A9BA85C80924 FOREIGN KEY (veterinaire_id) REFERENCES veterinaire (id)');
        $this->addSql('ALTER TABLE veterinaire ADD CONSTRAINT FK_E9D962B8265183A3 FOREIGN KEY (clinique_id) REFERENCES clinique (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE disponibilite_veterinaire DROP FOREIGN KEY FK_D30FF9FE5C80924');
        $this->addSql('ALTER TABLE dossier_medical DROP FOREIGN KEY FK_3581EE628E962C16');
        $this->addSql('ALTER TABLE rendezvous DROP FOREIGN KEY FK_C09A9BA85C80924');
        $this->addSql('ALTER TABLE veterinaire DROP FOREIGN KEY FK_E9D962B8265183A3');
        $this->addSql('DROP TABLE animal');
        $this->addSql('DROP TABLE clinique');
        $this->addSql('DROP TABLE disponibilite_veterinaire');
        $this->addSql('DROP TABLE dossier_medical');
        $this->addSql('DROP TABLE rendezvous');
        $this->addSql('DROP TABLE veterinaire');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
