<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250305061125 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entretient_chambre CHANGE details details VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE planning CHANGE temps_reserver temps_reserver JSON DEFAULT NULL, CHANGE dates_non_disponibles dates_non_disponibles JSON NOT NULL');
        $this->addSql('ALTER TABLE rendez_vous CHANGE rendez_vous_status rendez_vous_status VARCHAR(50) DEFAULT NULL, CHANGE heure heure VARCHAR(5) DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur CHANGE grade grade VARCHAR(255) DEFAULT NULL, CHANGE medecin_specilaite medecin_specilaite VARCHAR(255) DEFAULT NULL, CHANGE password password VARCHAR(255) DEFAULT NULL, CHANGE reset_token reset_token VARCHAR(255) DEFAULT NULL, CHANGE reset_token_expires_at reset_token_expires_at DATETIME DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entretient_chambre CHANGE details details VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\' COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE planning CHANGE dates_non_disponibles dates_non_disponibles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`, CHANGE temps_reserver temps_reserver LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE rendez_vous CHANGE rendez_vous_status rendez_vous_status VARCHAR(50) DEFAULT \'NULL\', CHANGE heure heure VARCHAR(5) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE utilisateur CHANGE grade grade VARCHAR(255) DEFAULT \'NULL\', CHANGE medecin_specilaite medecin_specilaite VARCHAR(255) DEFAULT \'NULL\', CHANGE password password VARCHAR(255) DEFAULT \'NULL\', CHANGE reset_token reset_token VARCHAR(255) DEFAULT \'NULL\', CHANGE reset_token_expires_at reset_token_expires_at DATETIME DEFAULT \'NULL\', CHANGE image image VARCHAR(255) DEFAULT \'NULL\'');
    }
}
