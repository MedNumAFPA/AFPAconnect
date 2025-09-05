<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250905064450 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation DROP id_afpa');
        $this->addSql('ALTER TABLE user ADD id_afpa VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE workshop CHANGE room_number room_number VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE workshop CHANGE room_number room_number INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation ADD id_afpa INT NOT NULL');
        $this->addSql('ALTER TABLE user DROP id_afpa');
    }
}
