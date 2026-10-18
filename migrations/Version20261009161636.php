<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261009161636 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE blog_article (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, image_alt VARCHAR(255) DEFAULT NULL, author_name VARCHAR(40) DEFAULT NULL, read_time INT NOT NULL, status VARCHAR(20) NOT NULL, is_featured TINYINT NOT NULL, published_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_BLOG_ARTICLE_SLUG (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE blog_article_category (blog_article_id INT NOT NULL, blog_category_id INT NOT NULL, INDEX IDX_27A7C64C9452A475 (blog_article_id), INDEX IDX_27A7C64CCB76011C (blog_category_id), PRIMARY KEY (blog_article_id, blog_category_id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE blog_article_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(5) NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, excerpt LONGTEXT DEFAULT NULL, content LONGTEXT NOT NULL, seo_title VARCHAR(255) DEFAULT NULL, seo_description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, article_id INT NOT NULL, UNIQUE INDEX UNIQ_BLOG_ARTICLE_TRANSLATION_LOCALE (article_id, locale), INDEX IDX_6CFC2FBD7294869C (article_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE blog_category (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(255) NOT NULL, is_active TINYINT NOT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_BLOG_CATEGORY_SLUG (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE blog_category_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(5) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, category_id INT NOT NULL, UNIQUE INDEX UNIQ_BLOG_CATEGORY_TRANSLATION_LOCALE (category_id, locale), INDEX IDX_B8BB560712469DE2 (category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE blog_article_category ADD CONSTRAINT FK_27A7C64C9452A475 FOREIGN KEY (blog_article_id) REFERENCES blog_article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blog_article_category ADD CONSTRAINT FK_27A7C64CCB76011C FOREIGN KEY (blog_category_id) REFERENCES blog_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blog_article_translation ADD CONSTRAINT FK_6CFC2FBD7294869C FOREIGN KEY (article_id) REFERENCES blog_article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blog_category_translation ADD CONSTRAINT FK_B8BB560712469DE2 FOREIGN KEY (category_id) REFERENCES blog_category (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE blog_article_category DROP FOREIGN KEY FK_27A7C64C9452A475');
        $this->addSql('ALTER TABLE blog_article_category DROP FOREIGN KEY FK_27A7C64CCB76011C');
        $this->addSql('ALTER TABLE blog_article_translation DROP FOREIGN KEY FK_6CFC2FBD7294869C');
        $this->addSql('ALTER TABLE blog_category_translation DROP FOREIGN KEY FK_B8BB560712469DE2');
        $this->addSql('DROP TABLE blog_article');
        $this->addSql('DROP TABLE blog_article_category');
        $this->addSql('DROP TABLE blog_article_translation');
        $this->addSql('DROP TABLE blog_category');
        $this->addSql('DROP TABLE blog_category_translation');
    }
}
