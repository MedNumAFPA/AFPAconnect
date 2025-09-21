<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250921081247 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE time_slot_user (time_slot_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_C1CC7706D62B0FA (time_slot_id), INDEX IDX_C1CC7706A76ED395 (user_id), PRIMARY KEY(time_slot_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE time_slot_user ADD CONSTRAINT FK_C1CC7706D62B0FA FOREIGN KEY (time_slot_id) REFERENCES time_slot (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE time_slot_user ADD CONSTRAINT FK_C1CC7706A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE time_slot_user DROP FOREIGN KEY FK_C1CC7706D62B0FA');
        $this->addSql('ALTER TABLE time_slot_user DROP FOREIGN KEY FK_C1CC7706A76ED395');
        $this->addSql('DROP TABLE time_slot_user');
    }
}
