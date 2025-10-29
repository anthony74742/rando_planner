<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251029145024 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Supprimer le DEFAULT avant de changer le type
        $this->addSql('ALTER TABLE hike ALTER COLUMN distance DROP DEFAULT');

        // Conversion du type avec PostgreSQL
        $this->addSql('ALTER TABLE hike ALTER COLUMN distance TYPE DOUBLE PRECISION USING distance::double precision');

        // Si tu veux un DEFAULT (par exemple 0)
        $this->addSql('ALTER TABLE hike ALTER COLUMN distance SET DEFAULT 0');

        // Et si la colonne ne peut pas être NULL
        $this->addSql('ALTER TABLE hike ALTER COLUMN distance SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE hike ALTER distance TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE hike ALTER distance DROP NOT NULL');
    }
}
