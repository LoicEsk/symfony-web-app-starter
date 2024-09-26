<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240926124411 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout du statut actif et de la date de dernière connexion des utilisateurs';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD is_actif TINYINT(1) NOT NULL, ADD last_success_login_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP updated_at');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD updated_at VARCHAR(255) DEFAULT NULL, DROP is_actif, DROP last_success_login_at');
    }
}
