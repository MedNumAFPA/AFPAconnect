<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250921094843 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE workshop_user DROP FOREIGN KEY FK_B714FF0E1FDCE57C');
        $this->addSql('ALTER TABLE workshop_user DROP FOREIGN KEY FK_B714FF0EA76ED395');
        $this->addSql('DROP TABLE workshop_user');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE workshop_user (workshop_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_B714FF0E1FDCE57C (workshop_id), INDEX IDX_B714FF0EA76ED395 (user_id), PRIMARY KEY(workshop_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE workshop_user ADD CONSTRAINT FK_B714FF0E1FDCE57C FOREIGN KEY (workshop_id) REFERENCES workshop (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE workshop_user ADD CONSTRAINT FK_B714FF0EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }
}
