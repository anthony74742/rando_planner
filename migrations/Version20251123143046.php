<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251123143046 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hike ADD gpx_filename VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE hike ADD gpx_track JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE hike ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN hike.updated_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE hike DROP gpx_filename');
        $this->addSql('ALTER TABLE hike DROP gpx_track');
        $this->addSql('ALTER TABLE hike DROP updated_at');
    }
}
