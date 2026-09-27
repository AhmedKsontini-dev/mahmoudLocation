<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260926174219 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add foreign key constraints';
    }

    public function up(Schema $schema): void
    {
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
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C64AB4296F');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849554AB4296F');
        $this->addSql('ALTER TABLE pickup_region_translation DROP FOREIGN KEY FK_F17EEF8F6AEC09E6');
        $this->addSql('ALTER TABLE excursion_translation DROP FOREIGN KEY FK_D568992B4AB4296F');
        $this->addSql('ALTER TABLE excursion_itinerary_translation DROP FOREIGN KEY FK_55239BA26427B3E7');
        $this->addSql('ALTER TABLE excursion_itinerary DROP FOREIGN KEY FK_6C9514E74AB4296F');
        $this->addSql('ALTER TABLE excursion_inclusion_translation DROP FOREIGN KEY FK_F4BDC890728B1300');
        $this->addSql('ALTER TABLE excursion_inclusion DROP FOREIGN KEY FK_C2A68FC54AB4296F');
        $this->addSql('ALTER TABLE excursion_image_translation DROP FOREIGN KEY FK_52DA12B18376A9E0');
        $this->addSql('ALTER TABLE excursion_image DROP FOREIGN KEY FK_17F93B114AB4296F');
        $this->addSql('ALTER TABLE excursion_feature_translation DROP FOREIGN KEY FK_C2C0F126AC72FA7');
        $this->addSql('ALTER TABLE excursion_feature DROP FOREIGN KEY FK_C74BE8144AB4296F');
        $this->addSql('ALTER TABLE excursion_faq_translation DROP FOREIGN KEY FK_E3ED0BE71008606A');
        $this->addSql('ALTER TABLE excursion_faq DROP FOREIGN KEY FK_5E6CD5BF4AB4296F');
        $this->addSql('ALTER TABLE excursion_badge_translation DROP FOREIGN KEY FK_73F56BE849714E71');
        $this->addSql('ALTER TABLE excursion_badge DROP FOREIGN KEY FK_2C3477534AB4296F');
        $this->addSql('ALTER TABLE excursion DROP FOREIGN KEY FK_9B08E72F816C6140');
        $this->addSql('ALTER TABLE excursion DROP FOREIGN KEY FK_9B08E72F12469DE2');
        $this->addSql('ALTER TABLE destination_translation DROP FOREIGN KEY FK_915BAE54816C6140');
        $this->addSql('ALTER TABLE category_translation DROP FOREIGN KEY FK_3F2070412469DE2');
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1727ACA70');
    }
}
