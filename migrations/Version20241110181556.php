<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241110181556 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, articles_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_C53D045F1EBAF6CC (articles_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F1EBAF6CC FOREIGN KEY (articles_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE article DROP image_name');
        $this->addSql('ALTER TABLE panier ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F1EBAF6CC');
        $this->addSql('DROP TABLE image');
        $this->addSql('ALTER TABLE article ADD image_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE panier DROP created_at');
    }
}
