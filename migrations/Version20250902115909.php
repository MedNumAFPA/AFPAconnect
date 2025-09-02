<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250902115909 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation CHANGE formation_name formation_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD nickname VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE workshop CHANGE room_number room_number INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation CHANGE formation_name formation_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE workshop CHANGE room_number room_number INT NOT NULL');
        $this->addSql('ALTER TABLE user DROP nickname');
    }
}
