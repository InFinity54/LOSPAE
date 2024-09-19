<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240919081921 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE student_note DROP FOREIGN KEY FK_F09E81CCCB944F1A');
        $this->addSql('DROP TABLE student_note');
        $this->addSql('ALTER TABLE user ADD current_note DOUBLE PRECISION DEFAULT NULL, DROP promo_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE student_note (id INT AUTO_INCREMENT NOT NULL, student_id INT DEFAULT NULL, current_note DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_F09E81CCCB944F1A (student_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE student_note ADD CONSTRAINT FK_F09E81CCCB944F1A FOREIGN KEY (student_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE `user` ADD promo_id INT NOT NULL, DROP current_note');
    }
}
