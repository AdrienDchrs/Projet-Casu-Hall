<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240708152633 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article_favori (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE article ADD article_favori_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E662522531B FOREIGN KEY (article_favori_id) REFERENCES article_favori (id)');
        $this->addSql('CREATE INDEX IDX_23A0E662522531B ON article (article_favori_id)');
        $this->addSql('ALTER TABLE utilisateur ADD article_favori_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B32522531B FOREIGN KEY (article_favori_id) REFERENCES article_favori (id)');
        $this->addSql('CREATE INDEX IDX_1D1C63B32522531B ON utilisateur (article_favori_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E662522531B');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B32522531B');
        $this->addSql('DROP TABLE article_favori');
        $this->addSql('DROP INDEX IDX_23A0E662522531B ON article');
        $this->addSql('ALTER TABLE article DROP article_favori_id');
        $this->addSql('DROP INDEX IDX_1D1C63B32522531B ON utilisateur');
        $this->addSql('ALTER TABLE utilisateur DROP article_favori_id');
    }
}
