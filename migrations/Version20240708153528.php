<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240708153528 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E662522531B');
        $this->addSql('DROP INDEX IDX_23A0E662522531B ON article');
        $this->addSql('ALTER TABLE article DROP article_favori_id');
        $this->addSql('ALTER TABLE article_favori ADD id_utilisateur INT NOT NULL, ADD id_article INT NOT NULL');
        $this->addSql('ALTER TABLE article_favori ADD CONSTRAINT FK_8542354C50EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE article_favori ADD CONSTRAINT FK_8542354CDCA7A716 FOREIGN KEY (id_article) REFERENCES article (id)');
        $this->addSql('CREATE INDEX IDX_8542354C50EAE44 ON article_favori (id_utilisateur)');
        $this->addSql('CREATE INDEX IDX_8542354CDCA7A716 ON article_favori (id_article)');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B32522531B');
        $this->addSql('DROP INDEX IDX_1D1C63B32522531B ON utilisateur');
        $this->addSql('ALTER TABLE utilisateur DROP article_favori_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article ADD article_favori_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E662522531B FOREIGN KEY (article_favori_id) REFERENCES article_favori (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_23A0E662522531B ON article (article_favori_id)');
        $this->addSql('ALTER TABLE article_favori DROP FOREIGN KEY FK_8542354C50EAE44');
        $this->addSql('ALTER TABLE article_favori DROP FOREIGN KEY FK_8542354CDCA7A716');
        $this->addSql('DROP INDEX IDX_8542354C50EAE44 ON article_favori');
        $this->addSql('DROP INDEX IDX_8542354CDCA7A716 ON article_favori');
        $this->addSql('ALTER TABLE article_favori DROP id_utilisateur, DROP id_article');
        $this->addSql('ALTER TABLE utilisateur ADD article_favori_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B32522531B FOREIGN KEY (article_favori_id) REFERENCES article_favori (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_1D1C63B32522531B ON utilisateur (article_favori_id)');
    }
}
