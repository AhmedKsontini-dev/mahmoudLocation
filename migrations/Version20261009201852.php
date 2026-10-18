<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261009201852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE blog_article_category ADD CONSTRAINT FK_27A7C64C9452A475 FOREIGN KEY (blog_article_id) REFERENCES blog_article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blog_article_category ADD CONSTRAINT FK_27A7C64CCB76011C FOREIGN KEY (blog_category_id) REFERENCES blog_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blog_article_image ADD CONSTRAINT FK_C10CBC227294869C FOREIGN KEY (article_id) REFERENCES blog_article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blog_article_image RENAME INDEX idx_4c18a6b57294869c TO IDX_C10CBC227294869C');
        $this->addSql('ALTER TABLE blog_article_translation ADD CONSTRAINT FK_6CFC2FBD7294869C FOREIGN KEY (article_id) REFERENCES blog_article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blog_article_video ADD CONSTRAINT FK_78F662517294869C FOREIGN KEY (article_id) REFERENCES blog_article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blog_article_video RENAME INDEX idx_637a53537294869c TO IDX_78F662517294869C');
        $this->addSql('ALTER TABLE blog_category_translation ADD CONSTRAINT FK_B8BB560712469DE2 FOREIGN KEY (category_id) REFERENCES blog_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE transfere ADD image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE blog_article_category DROP FOREIGN KEY FK_27A7C64C9452A475');
        $this->addSql('ALTER TABLE blog_article_category DROP FOREIGN KEY FK_27A7C64CCB76011C');
        $this->addSql('ALTER TABLE blog_article_image DROP FOREIGN KEY FK_C10CBC227294869C');
        $this->addSql('ALTER TABLE blog_article_image RENAME INDEX idx_c10cbc227294869c TO IDX_4C18A6B57294869C');
        $this->addSql('ALTER TABLE blog_article_translation DROP FOREIGN KEY FK_6CFC2FBD7294869C');
        $this->addSql('ALTER TABLE blog_article_video DROP FOREIGN KEY FK_78F662517294869C');
        $this->addSql('ALTER TABLE blog_article_video RENAME INDEX idx_78f662517294869c TO IDX_637A53537294869C');
        $this->addSql('ALTER TABLE blog_category_translation DROP FOREIGN KEY FK_B8BB560712469DE2');
        $this->addSql('ALTER TABLE transfere DROP image');
    }
}
