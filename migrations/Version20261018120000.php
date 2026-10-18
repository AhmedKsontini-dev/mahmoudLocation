<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Le champ « icon » de la table category stocke des emojis (🏖️, 🏛️, 🌴 …).
 * En utf8mb3 (3 octets), tout emoji de 4 octets est tronqué à la sauvegarde :
 * la colonne passe donc en utf8mb4 pour que les icônes soient réellement
 * enregistrées et affichées dans le champ « Catégorie » du formulaire d'excursion.
 */
final class Version20261018120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convert category.icon to utf8mb4 so emoji icons can be stored.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE category CHANGE icon icon VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE category CHANGE icon icon VARCHAR(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL');
    }
}
