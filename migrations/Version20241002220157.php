<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241002220157 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE teacher_promotion (id INT AUTO_INCREMENT NOT NULL, teacher_id INT NOT NULL, promotion_id INT NOT NULL, course_name VARCHAR(255) NOT NULL, INDEX IDX_BB7631CA41807E1D (teacher_id), INDEX IDX_BB7631CA139DF194 (promotion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE teacher_promotion ADD CONSTRAINT FK_BB7631CA41807E1D FOREIGN KEY (teacher_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE teacher_promotion ADD CONSTRAINT FK_BB7631CA139DF194 FOREIGN KEY (promotion_id) REFERENCES promotion (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE teacher_promotion DROP FOREIGN KEY FK_BB7631CA41807E1D');
        $this->addSql('ALTER TABLE teacher_promotion DROP FOREIGN KEY FK_BB7631CA139DF194');
        $this->addSql('DROP TABLE teacher_promotion');
    }
}
