<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250513045436 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE blog CHANGE category category VARCHAR(255) DEFAULT NULL, CHANGE deleted_at deleted_at DATETIME DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE sentiment sentiment VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE blog ADD CONSTRAINT FK_C0155143F675F31B FOREIGN KEY (author_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE chambre DROP FOREIGN KEY FK_C509E4FFDD842E46');
        $this->addSql('ALTER TABLE chambre ADD CONSTRAINT FK_C509E4FFDD842E46 FOREIGN KEY (position_id) REFERENCES service (id)');
        $this->addSql('ALTER TABLE entretient_chambre DROP FOREIGN KEY FK_A32FBA8571F4B3BF');
        $this->addSql('ALTER TABLE entretient_chambre DROP FOREIGN KEY FK_A32FBA859B177F54');
        $this->addSql('ALTER TABLE entretient_chambre CHANGE details details VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE entretient_chambre ADD CONSTRAINT FK_A32FBA8571F4B3BF FOREIGN KEY (femmedemenage_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE entretient_chambre ADD CONSTRAINT FK_A32FBA859B177F54 FOREIGN KEY (chambre_id) REFERENCES chambre (id)');
        $this->addSql('ALTER TABLE lit DROP FOREIGN KEY FK_5DDB8E9D6B899279');
        $this->addSql('ALTER TABLE lit DROP FOREIGN KEY FK_5DDB8E9D9B177F54');
        $this->addSql('ALTER TABLE lit ADD CONSTRAINT FK_5DDB8E9D6B899279 FOREIGN KEY (patient_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE lit ADD CONSTRAINT FK_5DDB8E9D9B177F54 FOREIGN KEY (chambre_id) REFERENCES chambre (id)');
        $this->addSql('ALTER TABLE message CHANGE status status VARCHAR(255) DEFAULT \'unread\' NOT NULL');
        $this->addSql('ALTER TABLE planning CHANGE temps_reserver temps_reserver JSON DEFAULT NULL, CHANGE dates_non_disponibles dates_non_disponibles JSON NOT NULL');
        $this->addSql('ALTER TABLE rendez_vous CHANGE heure heure VARCHAR(5) DEFAULT NULL, CHANGE rendez_vous_status rendez_vous_status VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE stock_pharmacie CHANGE image image VARCHAR(255) DEFAULT NULL, CHANGE statu statu VARCHAR(255) DEFAULT NULL, CHANGE date_expiration date_expiration DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE traitement_reclamation CHANGE rating rating DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur DROP reset_code, DROP lockout_timestamp, DROP failed_attempts, CHANGE grade grade VARCHAR(255) DEFAULT NULL, CHANGE password password VARCHAR(255) DEFAULT NULL, CHANGE reset_token reset_token VARCHAR(255) DEFAULT NULL, CHANGE reset_token_expires_at reset_token_expires_at DATETIME DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL, CHANGE locked_until locked_until DATETIME DEFAULT NULL, CHANGE face_encoding face_encoding JSON DEFAULT NULL, CHANGE medecin_specilaite medecin_specilaite VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE blog DROP FOREIGN KEY FK_C0155143F675F31B');
        $this->addSql('ALTER TABLE blog CHANGE category category VARCHAR(255) DEFAULT \'NULL\', CHANGE deleted_at deleted_at DATETIME DEFAULT \'NULL\', CHANGE created_at created_at DATETIME DEFAULT \'NULL\', CHANGE sentiment sentiment VARCHAR(50) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE chambre DROP FOREIGN KEY FK_C509E4FFDD842E46');
        $this->addSql('ALTER TABLE chambre ADD CONSTRAINT FK_C509E4FFDD842E46 FOREIGN KEY (position_id) REFERENCES service (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE entretient_chambre DROP FOREIGN KEY FK_A32FBA859B177F54');
        $this->addSql('ALTER TABLE entretient_chambre DROP FOREIGN KEY FK_A32FBA8571F4B3BF');
        $this->addSql('ALTER TABLE entretient_chambre CHANGE details details VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE entretient_chambre ADD CONSTRAINT FK_A32FBA859B177F54 FOREIGN KEY (chambre_id) REFERENCES chambre (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE entretient_chambre ADD CONSTRAINT FK_A32FBA8571F4B3BF FOREIGN KEY (femmedemenage_id) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lit DROP FOREIGN KEY FK_5DDB8E9D9B177F54');
        $this->addSql('ALTER TABLE lit DROP FOREIGN KEY FK_5DDB8E9D6B899279');
        $this->addSql('ALTER TABLE lit ADD CONSTRAINT FK_5DDB8E9D9B177F54 FOREIGN KEY (chambre_id) REFERENCES chambre (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lit ADD CONSTRAINT FK_5DDB8E9D6B899279 FOREIGN KEY (patient_id) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message CHANGE status status VARCHAR(255) DEFAULT \'\'\'unread\'\'\' NOT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\' COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE planning CHANGE dates_non_disponibles dates_non_disponibles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`, CHANGE temps_reserver temps_reserver LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE rendez_vous CHANGE rendez_vous_status rendez_vous_status VARCHAR(50) DEFAULT \'NULL\', CHANGE heure heure VARCHAR(5) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE stock_pharmacie CHANGE image image VARCHAR(255) DEFAULT \'NULL\', CHANGE statu statu VARCHAR(255) DEFAULT \'NULL\', CHANGE date_expiration date_expiration DATE DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE traitement_reclamation CHANGE rating rating DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE utilisateur ADD reset_code VARCHAR(255) DEFAULT \'NULL\', ADD lockout_timestamp DATETIME DEFAULT \'NULL\', ADD failed_attempts INT DEFAULT 0, CHANGE grade grade VARCHAR(255) DEFAULT \'NULL\', CHANGE medecin_specilaite medecin_specilaite VARCHAR(255) DEFAULT \'NULL\', CHANGE password password VARCHAR(255) DEFAULT \'NULL\', CHANGE reset_token reset_token VARCHAR(255) DEFAULT \'NULL\', CHANGE reset_token_expires_at reset_token_expires_at DATETIME DEFAULT \'NULL\', CHANGE image image VARCHAR(255) DEFAULT \'NULL\', CHANGE locked_until locked_until DATETIME DEFAULT \'NULL\', CHANGE face_encoding face_encoding LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`');
    }
}
