<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Fix user.updated_at column type: it was created as VARCHAR(255) because the
 * EntityTimeTrait mapped the property as \DateTimeInterface (Doctrine can't
 * infer a column type from an interface and fell back to "string"). The
 * property is now \DateTimeImmutable, so align the column.
 */
final class Version20260826193000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change user.updated_at from VARCHAR(255) to DATETIME (datetime_immutable)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE user CHANGE updated_at updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user CHANGE updated_at updated_at VARCHAR(255) DEFAULT NULL');
    }
}
