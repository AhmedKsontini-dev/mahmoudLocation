<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261009170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add blog gallery images and videos tables for article media management';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE blog_article_image (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, image_path VARCHAR(255) NOT NULL, image_alt VARCHAR(255) DEFAULT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_4C18A6B57294869C (article_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE blog_article_video (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, title VARCHAR(255) DEFAULT NULL, video_path VARCHAR(255) DEFAULT NULL, external_url VARCHAR(500) DEFAULT NULL, thumbnail VARCHAR(255) DEFAULT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_637A53537294869C (article_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE blog_article_image ADD CONSTRAINT FK_4C18A6B57294869C FOREIGN KEY (article_id) REFERENCES blog_article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blog_article_video ADD CONSTRAINT FK_637A53537294869C FOREIGN KEY (article_id) REFERENCES blog_article (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE blog_article_image DROP FOREIGN KEY FK_4C18A6B57294869C');
        $this->addSql('ALTER TABLE blog_article_video DROP FOREIGN KEY FK_637A53537294869C');
        $this->addSql('DROP TABLE blog_article_image');
        $this->addSql('DROP TABLE blog_article_video');
    }
}
