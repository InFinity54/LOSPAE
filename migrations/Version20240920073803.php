<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240920073803 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE promo (id INT AUTO_INCREMENT NOT NULL, school_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_B0139AFBC32A47EE (school_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE school (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(500) NOT NULL, address VARCHAR(500) NOT NULL, address_extension VARCHAR(255) DEFAULT NULL, postal_code INT NOT NULL, city VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE promo ADD CONSTRAINT FK_B0139AFBC32A47EE FOREIGN KEY (school_id) REFERENCES school (id)');
        $this->addSql('ALTER TABLE student_note DROP FOREIGN KEY FK_F09E81CCCB944F1A');
        $this->addSql('DROP TABLE student_note');
        $this->addSql('ALTER TABLE user ADD promo_id INT DEFAULT NULL, ADD current_note DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649D0C07AFF FOREIGN KEY (promo_id) REFERENCES promo (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649D0C07AFF ON user (promo_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649D0C07AFF');
        $this->addSql('CREATE TABLE student_note (id INT AUTO_INCREMENT NOT NULL, student_id INT DEFAULT NULL, current_note DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_F09E81CCCB944F1A (student_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE student_note ADD CONSTRAINT FK_F09E81CCCB944F1A FOREIGN KEY (student_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE promo DROP FOREIGN KEY FK_B0139AFBC32A47EE');
        $this->addSql('DROP TABLE promo');
        $this->addSql('DROP TABLE school');
        $this->addSql('DROP INDEX IDX_8D93D649D0C07AFF ON `user`');
        $this->addSql('ALTER TABLE `user` DROP promo_id, DROP current_note');
    }
}
