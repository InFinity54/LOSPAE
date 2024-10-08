<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241008182307 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE criteria ADD teacher_id INT NOT NULL');
        $this->addSql('ALTER TABLE criteria ADD CONSTRAINT FK_B61F9B8141807E1D FOREIGN KEY (teacher_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_B61F9B8141807E1D ON criteria (teacher_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE criteria DROP FOREIGN KEY FK_B61F9B8141807E1D');
        $this->addSql('DROP INDEX IDX_B61F9B8141807E1D ON criteria');
        $this->addSql('ALTER TABLE criteria DROP teacher_id');
    }
}
