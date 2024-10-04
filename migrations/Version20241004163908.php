<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241004163908 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE academy (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE school ADD academy_id INT NOT NULL, DROP academy');
        $this->addSql('ALTER TABLE school ADD CONSTRAINT FK_F99EDABB6D55ACAB FOREIGN KEY (academy_id) REFERENCES academy (id)');
        $this->addSql('CREATE INDEX IDX_F99EDABB6D55ACAB ON school (academy_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE school DROP FOREIGN KEY FK_F99EDABB6D55ACAB');
        $this->addSql('DROP TABLE academy');
        $this->addSql('DROP INDEX IDX_F99EDABB6D55ACAB ON school');
        $this->addSql('ALTER TABLE school ADD academy VARCHAR(255) NOT NULL, DROP academy_id');
    }
}
