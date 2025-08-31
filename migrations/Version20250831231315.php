<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250831231315 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, timeslot_id INT NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone_number VARCHAR(255) NOT NULL, formation_name VARCHAR(255) NOT NULL, is_reminder_active TINYINT(1) NOT NULL, reminder_sent_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_42C84955F920B9E9 (timeslot_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE time_slot (id INT AUTO_INCREMENT NOT NULL, workshop_id INT NOT NULL, date DATE NOT NULL, from_time TIME NOT NULL, to_time TIME NOT NULL, INDEX IDX_1B3294A1FDCE57C (workshop_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE workshop (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, max_slots INT NOT NULL, room_number INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955F920B9E9 FOREIGN KEY (timeslot_id) REFERENCES time_slot (id)');
        $this->addSql('ALTER TABLE time_slot ADD CONSTRAINT FK_1B3294A1FDCE57C FOREIGN KEY (workshop_id) REFERENCES workshop (id)');
        $this->addSql('ALTER TABLE time_slots DROP FOREIGN KEY FK_8D06D4AC1FDCE57C');
        $this->addSql('ALTER TABLE reservations DROP FOREIGN KEY FK_4DA239F920B9E9');
        $this->addSql('DROP TABLE workshops');
        $this->addSql('DROP TABLE time_slots');
        $this->addSql('DROP TABLE reservations');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE workshops (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, max_slots INT NOT NULL, room_number INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE time_slots (id INT AUTO_INCREMENT NOT NULL, workshop_id INT NOT NULL, date DATE NOT NULL, from_time TIME NOT NULL, to_time TIME NOT NULL, INDEX IDX_8D06D4AC1FDCE57C (workshop_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reservations (id INT AUTO_INCREMENT NOT NULL, timeslot_id INT NOT NULL, firstname VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, lastname VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, phone_number VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, formation_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, is_reminder_active TINYINT(1) NOT NULL, reminder_sent_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_4DA239F920B9E9 (timeslot_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE time_slots ADD CONSTRAINT FK_8D06D4AC1FDCE57C FOREIGN KEY (workshop_id) REFERENCES workshops (id)');
        $this->addSql('ALTER TABLE reservations ADD CONSTRAINT FK_4DA239F920B9E9 FOREIGN KEY (timeslot_id) REFERENCES time_slots (id)');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955F920B9E9');
        $this->addSql('ALTER TABLE time_slot DROP FOREIGN KEY FK_1B3294A1FDCE57C');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE time_slot');
        $this->addSql('DROP TABLE workshop');
    }
}
