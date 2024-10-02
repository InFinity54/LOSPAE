<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241002225256 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE current_note (id INT AUTO_INCREMENT NOT NULL, teacher_id INT NOT NULL, student_id INT NOT NULL, note DOUBLE PRECISION NOT NULL, INDEX IDX_E937241807E1D (teacher_id), INDEX IDX_E9372CB944F1A (student_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE note_change (id INT AUTO_INCREMENT NOT NULL, teacher_id INT NOT NULL, student_id INT NOT NULL, criteria_id INT NOT NULL, impact DOUBLE PRECISION NOT NULL, occured_at DATETIME NOT NULL, INDEX IDX_5885A9F441807E1D (teacher_id), INDEX IDX_5885A9F4CB944F1A (student_id), INDEX IDX_5885A9F4990BEA15 (criteria_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE current_note ADD CONSTRAINT FK_E937241807E1D FOREIGN KEY (teacher_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE current_note ADD CONSTRAINT FK_E9372CB944F1A FOREIGN KEY (student_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE note_change ADD CONSTRAINT FK_5885A9F441807E1D FOREIGN KEY (teacher_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE note_change ADD CONSTRAINT FK_5885A9F4CB944F1A FOREIGN KEY (student_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE note_change ADD CONSTRAINT FK_5885A9F4990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE current_note DROP FOREIGN KEY FK_E937241807E1D');
        $this->addSql('ALTER TABLE current_note DROP FOREIGN KEY FK_E9372CB944F1A');
        $this->addSql('ALTER TABLE note_change DROP FOREIGN KEY FK_5885A9F441807E1D');
        $this->addSql('ALTER TABLE note_change DROP FOREIGN KEY FK_5885A9F4CB944F1A');
        $this->addSql('ALTER TABLE note_change DROP FOREIGN KEY FK_5885A9F4990BEA15');
        $this->addSql('DROP TABLE current_note');
        $this->addSql('DROP TABLE note_change');
    }
}
