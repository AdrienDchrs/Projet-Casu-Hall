<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240910153408 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE panier (id INT AUTO_INCREMENT NOT NULL, id_utilisateur INT NOT NULL, id_article INT NOT NULL, INDEX IDX_24CC0DF250EAE44 (id_utilisateur), INDEX IDX_24CC0DF2DCA7A716 (id_article), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE panier ADD CONSTRAINT FK_24CC0DF250EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE panier ADD CONSTRAINT FK_24CC0DF2DCA7A716 FOREIGN KEY (id_article) REFERENCES article (id)');
        $this->addSql('ALTER TABLE marque ADD image_marque VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE panier DROP FOREIGN KEY FK_24CC0DF250EAE44');
        $this->addSql('ALTER TABLE panier DROP FOREIGN KEY FK_24CC0DF2DCA7A716');
        $this->addSql('DROP TABLE panier');
        $this->addSql('ALTER TABLE marque DROP image_marque');
    }
}
