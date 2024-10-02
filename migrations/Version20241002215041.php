<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241002215041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE note_change DROP FOREIGN KEY FK_5885A9F4990BEA15');
        $this->addSql('ALTER TABLE note_change DROP FOREIGN KEY FK_5885A9F4CB944F1A');
        $this->addSql('ALTER TABLE student_note DROP FOREIGN KEY FK_F09E81CCCB944F1A');
        $this->addSql('DROP TABLE criteria');
        $this->addSql('DROP TABLE note_change');
        $this->addSql('DROP TABLE student_note');
        $this->addSql('DROP TABLE user');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE criteria (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, impact DOUBLE PRECISION NOT NULL, modality LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE note_change (id INT AUTO_INCREMENT NOT NULL, student_id INT NOT NULL, criteria_id INT NOT NULL, impact DOUBLE PRECISION NOT NULL, occured_at DATETIME NOT NULL, INDEX IDX_5885A9F4CB944F1A (student_id), INDEX IDX_5885A9F4990BEA15 (criteria_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE student_note (id INT AUTO_INCREMENT NOT NULL, student_id INT DEFAULT NULL, current_note DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_F09E81CCCB944F1A (student_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, roles JSON NOT NULL, password VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, last_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, first_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, avatar VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, is_activated TINYINT(1) NOT NULL, recovery_code VARCHAR(6) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, recovery_code_expire_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE note_change ADD CONSTRAINT FK_5885A9F4990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id)');
        $this->addSql('ALTER TABLE note_change ADD CONSTRAINT FK_5885A9F4CB944F1A FOREIGN KEY (student_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE student_note ADD CONSTRAINT FK_F09E81CCCB944F1A FOREIGN KEY (student_id) REFERENCES user (id)');
    }
}
