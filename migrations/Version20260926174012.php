<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260926174012 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, icon VARCHAR(50) DEFAULT NULL, position INT NOT NULL, is_active TINYINT NOT NULL, slug VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, parent_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_CATEGORY_SLUG (slug), INDEX IDX_64C19C1727ACA70 (parent_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE category_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(5) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, category_id INT NOT NULL, UNIQUE INDEX UNIQ_CATEGORY_TRANSLATION_LOCALE (category_id, locale), INDEX IDX_3F2070412469DE2 (category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE destination (id INT AUTO_INCREMENT NOT NULL, region VARCHAR(255) DEFAULT NULL, latitude NUMERIC(10, 8) DEFAULT NULL, longitude NUMERIC(11, 8) DEFAULT NULL, is_active TINYINT NOT NULL, slug VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_DESTINATION_SLUG (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE destination_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(5) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, destination_id INT NOT NULL, UNIQUE INDEX UNIQ_DESTINATION_TRANSLATION_LOCALE (destination_id, locale), INDEX IDX_915BAE54816C6140 (destination_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE excursion (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(20) NOT NULL, status VARCHAR(20) NOT NULL, is_best_seller TINYINT NOT NULL, is_featured TINYINT NOT NULL, duration_hours INT DEFAULT NULL, duration_minutes INT DEFAULT NULL, start_time TIME DEFAULT NULL, end_time TIME DEFAULT NULL, price_adult NUMERIC(10, 2) NOT NULL, price_child NUMERIC(10, 2) DEFAULT NULL, price_converted NUMERIC(10, 2) DEFAULT NULL, min_participants INT NOT NULL, max_participants INT NOT NULL, cancellation_hours INT NOT NULL, rating_average NUMERIC(3, 2) DEFAULT NULL, rating_count INT NOT NULL, booking_count INT NOT NULL, slug VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, category_id INT NOT NULL, destination_id INT NOT NULL, UNIQUE INDEX UNIQ_EXCURSION_SLUG (slug), INDEX IDX_9B08E72F12469DE2 (category_id), INDEX IDX_9B08E72F816C6140 (destination_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE excursion_badge (id INT AUTO_INCREMENT NOT NULL, badge_type VARCHAR(20) NOT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, excursion_id INT NOT NULL, INDEX IDX_2C3477534AB4296F (excursion_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE excursion_badge_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(5) NOT NULL, text VARCHAR(100) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, excursion_badge_id INT NOT NULL, UNIQUE INDEX UNIQ_EXCURSION_BADGE_TRANSLATION_LOCALE (excursion_badge_id, locale), INDEX IDX_73F56BE849714E71 (excursion_badge_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE excursion_faq (id INT AUTO_INCREMENT NOT NULL, position INT NOT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, excursion_id INT NOT NULL, INDEX IDX_5E6CD5BF4AB4296F (excursion_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE excursion_faq_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(5) NOT NULL, question VARCHAR(500) NOT NULL, answer LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, excursion_faq_id INT NOT NULL, UNIQUE INDEX UNIQ_EXCURSION_FAQ_TRANSLATION_LOCALE (excursion_faq_id, locale), INDEX IDX_E3ED0BE71008606A (excursion_faq_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE excursion_feature (id INT AUTO_INCREMENT NOT NULL, icon VARCHAR(50) NOT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, excursion_id INT NOT NULL, INDEX IDX_C74BE8144AB4296F (excursion_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE excursion_feature_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(5) NOT NULL, title VARCHAR(255) NOT NULL, subtitle VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, excursion_feature_id INT NOT NULL, UNIQUE INDEX UNIQ_EXCURSION_FEATURE_TRANSLATION_LOCALE (excursion_feature_id, locale), INDEX IDX_C2C0F126AC72FA7 (excursion_feature_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE excursion_image (id INT AUTO_INCREMENT NOT NULL, image_path VARCHAR(255) NOT NULL, image_alt VARCHAR(255) DEFAULT NULL, position INT NOT NULL, is_main TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, excursion_id INT NOT NULL, INDEX IDX_17F93B114AB4296F (excursion_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE excursion_image_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(5) NOT NULL, caption VARCHAR(255) DEFAULT NULL, alt_text VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, excursion_image_id INT NOT NULL, UNIQUE INDEX UNIQ_EXCURSION_IMAGE_TRANSLATION_LOCALE (excursion_image_id, locale), INDEX IDX_52DA12B18376A9E0 (excursion_image_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE excursion_inclusion (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(20) NOT NULL, icon VARCHAR(50) DEFAULT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, excursion_id INT NOT NULL, INDEX IDX_C2A68FC54AB4296F (excursion_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE excursion_inclusion_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(5) NOT NULL, text VARCHAR(500) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, excursion_inclusion_id INT NOT NULL, UNIQUE INDEX UNIQ_EXCURSION_INCLUSION_TRANSLATION_LOCALE (excursion_inclusion_id, locale), INDEX IDX_F4BDC890728B1300 (excursion_inclusion_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE excursion_itinerary (id INT AUTO_INCREMENT NOT NULL, step_number INT NOT NULL, time TIME DEFAULT NULL, duration INT DEFAULT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, excursion_id INT NOT NULL, INDEX IDX_6C9514E74AB4296F (excursion_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE excursion_itinerary_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(5) NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, excursion_itinerary_id INT NOT NULL, UNIQUE INDEX UNIQ_EXCURSION_ITINERARY_TRANSLATION_LOCALE (excursion_itinerary_id, locale), INDEX IDX_55239BA26427B3E7 (excursion_itinerary_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE excursion_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(5) NOT NULL, title VARCHAR(255) NOT NULL, subtitle VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, meta_title VARCHAR(255) DEFAULT NULL, meta_description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, excursion_id INT NOT NULL, UNIQUE INDEX UNIQ_EXCURSION_TRANSLATION_LOCALE (excursion_id, locale), INDEX IDX_D568992B4AB4296F (excursion_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE pickup_region (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(255) NOT NULL, is_active TINYINT NOT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_9D13BDA0989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE pickup_region_translation (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(5) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, pickup_region_id INT NOT NULL, UNIQUE INDEX UNIQ_PICKUP_REGION_TRANSLATION_LOCALE (pickup_region_id, locale), INDEX IDX_F17EEF8F6AEC09E6 (pickup_region_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, date DATE NOT NULL, adults_count INT NOT NULL, children_count INT NOT NULL, pickup_region VARCHAR(255) DEFAULT NULL, pickup_address LONGTEXT DEFAULT NULL, contact_name VARCHAR(255) NOT NULL, contact_phone VARCHAR(50) NOT NULL, contact_email VARCHAR(255) NOT NULL, total_price NUMERIC(10, 2) NOT NULL, status VARCHAR(20) NOT NULL, payment_status VARCHAR(20) NOT NULL, payment_method VARCHAR(20) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, excursion_id INT NOT NULL, INDEX IDX_42C849554AB4296F (excursion_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, customer_name VARCHAR(255) NOT NULL, customer_location VARCHAR(255) DEFAULT NULL, rating INT NOT NULL, comment LONGTEXT DEFAULT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, excursion_id INT NOT NULL, INDEX IDX_794381C64AB4296F (excursion_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1727ACA70 FOREIGN KEY (parent_id) REFERENCES category (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE category_translation ADD CONSTRAINT FK_3F2070412469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE destination_translation ADD CONSTRAINT FK_915BAE54816C6140 FOREIGN KEY (destination_id) REFERENCES destination (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE excursion ADD CONSTRAINT FK_9B08E72F12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE excursion ADD CONSTRAINT FK_9B08E72F816C6140 FOREIGN KEY (destination_id) REFERENCES destination (id)');
        $this->addSql('ALTER TABLE excursion_badge ADD CONSTRAINT FK_2C3477534AB4296F FOREIGN KEY (excursion_id) REFERENCES excursion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE excursion_badge_translation ADD CONSTRAINT FK_73F56BE849714E71 FOREIGN KEY (excursion_badge_id) REFERENCES excursion_badge (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE excursion_faq ADD CONSTRAINT FK_5E6CD5BF4AB4296F FOREIGN KEY (excursion_id) REFERENCES excursion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE excursion_faq_translation ADD CONSTRAINT FK_E3ED0BE71008606A FOREIGN KEY (excursion_faq_id) REFERENCES excursion_faq (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE excursion_feature ADD CONSTRAINT FK_C74BE8144AB4296F FOREIGN KEY (excursion_id) REFERENCES excursion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE excursion_feature_translation ADD CONSTRAINT FK_C2C0F126AC72FA7 FOREIGN KEY (excursion_feature_id) REFERENCES excursion_feature (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE excursion_image ADD CONSTRAINT FK_17F93B114AB4296F FOREIGN KEY (excursion_id) REFERENCES excursion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE excursion_image_translation ADD CONSTRAINT FK_52DA12B18376A9E0 FOREIGN KEY (excursion_image_id) REFERENCES excursion_image (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE excursion_inclusion ADD CONSTRAINT FK_C2A68FC54AB4296F FOREIGN KEY (excursion_id) REFERENCES excursion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE excursion_inclusion_translation ADD CONSTRAINT FK_F4BDC890728B1300 FOREIGN KEY (excursion_inclusion_id) REFERENCES excursion_inclusion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE excursion_itinerary ADD CONSTRAINT FK_6C9514E74AB4296F FOREIGN KEY (excursion_id) REFERENCES excursion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE excursion_itinerary_translation ADD CONSTRAINT FK_55239BA26427B3E7 FOREIGN KEY (excursion_itinerary_id) REFERENCES excursion_itinerary (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE excursion_translation ADD CONSTRAINT FK_D568992B4AB4296F FOREIGN KEY (excursion_id) REFERENCES excursion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pickup_region_translation ADD CONSTRAINT FK_F17EEF8F6AEC09E6 FOREIGN KEY (pickup_region_id) REFERENCES pickup_region (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849554AB4296F FOREIGN KEY (excursion_id) REFERENCES excursion (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C64AB4296F FOREIGN KEY (excursion_id) REFERENCES excursion (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1727ACA70');
        $this->addSql('ALTER TABLE category_translation DROP FOREIGN KEY FK_3F2070412469DE2');
        $this->addSql('ALTER TABLE destination_translation DROP FOREIGN KEY FK_915BAE54816C6140');
        $this->addSql('ALTER TABLE excursion DROP FOREIGN KEY FK_9B08E72F12469DE2');
        $this->addSql('ALTER TABLE excursion DROP FOREIGN KEY FK_9B08E72F816C6140');
        $this->addSql('ALTER TABLE excursion_badge DROP FOREIGN KEY FK_2C3477534AB4296F');
        $this->addSql('ALTER TABLE excursion_badge_translation DROP FOREIGN KEY FK_73F56BE849714E71');
        $this->addSql('ALTER TABLE excursion_faq DROP FOREIGN KEY FK_5E6CD5BF4AB4296F');
        $this->addSql('ALTER TABLE excursion_faq_translation DROP FOREIGN KEY FK_E3ED0BE71008606A');
        $this->addSql('ALTER TABLE excursion_feature DROP FOREIGN KEY FK_C74BE8144AB4296F');
        $this->addSql('ALTER TABLE excursion_feature_translation DROP FOREIGN KEY FK_C2C0F126AC72FA7');
        $this->addSql('ALTER TABLE excursion_image DROP FOREIGN KEY FK_17F93B114AB4296F');
        $this->addSql('ALTER TABLE excursion_image_translation DROP FOREIGN KEY FK_52DA12B18376A9E0');
        $this->addSql('ALTER TABLE excursion_inclusion DROP FOREIGN KEY FK_C2A68FC54AB4296F');
        $this->addSql('ALTER TABLE excursion_inclusion_translation DROP FOREIGN KEY FK_F4BDC890728B1300');
        $this->addSql('ALTER TABLE excursion_itinerary DROP FOREIGN KEY FK_6C9514E74AB4296F');
        $this->addSql('ALTER TABLE excursion_itinerary_translation DROP FOREIGN KEY FK_55239BA26427B3E7');
        $this->addSql('ALTER TABLE excursion_translation DROP FOREIGN KEY FK_D568992B4AB4296F');
        $this->addSql('ALTER TABLE pickup_region_translation DROP FOREIGN KEY FK_F17EEF8F6AEC09E6');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849554AB4296F');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C64AB4296F');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE category_translation');
        $this->addSql('DROP TABLE destination');
        $this->addSql('DROP TABLE destination_translation');
        $this->addSql('DROP TABLE excursion');
        $this->addSql('DROP TABLE excursion_badge');
        $this->addSql('DROP TABLE excursion_badge_translation');
        $this->addSql('DROP TABLE excursion_faq');
        $this->addSql('DROP TABLE excursion_faq_translation');
        $this->addSql('DROP TABLE excursion_feature');
        $this->addSql('DROP TABLE excursion_feature_translation');
        $this->addSql('DROP TABLE excursion_image');
        $this->addSql('DROP TABLE excursion_image_translation');
        $this->addSql('DROP TABLE excursion_inclusion');
        $this->addSql('DROP TABLE excursion_inclusion_translation');
        $this->addSql('DROP TABLE excursion_itinerary');
        $this->addSql('DROP TABLE excursion_itinerary_translation');
        $this->addSql('DROP TABLE excursion_translation');
        $this->addSql('DROP TABLE pickup_region');
        $this->addSql('DROP TABLE pickup_region_translation');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE review');
    }
}
