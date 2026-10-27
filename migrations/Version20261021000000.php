<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Les saisons fortes sont désormais définies de façon récurrente par un simple
 * couple jour + mois (valable chaque année), sans année à saisir.
 *
 * Modification non destructive :
 *  - on ajoute quatre colonnes numériques (date_debut_jour, date_debut_mois,
 *    date_fin_jour, date_fin_mois) ;
 *  - on convertit les anciennes dates complètes existantes en jour + mois afin
 *    de conserver le comportement pour les saisons déjà configurées ;
 *  - les anciennes colonnes date_debut / date_fin sont rendues nullables et
 *    conservées telles quelles : les données historiques (avec leur année) ne
 *    sont donc ni supprimées ni altérées, elles ne pilotent simplement plus le
 *    calcul tarifaire.
 */
final class Version20261021000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add recurring day/month fields to saison and convert existing full dates, keeping legacy date columns.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE saison ADD date_debut_jour SMALLINT DEFAULT NULL, ADD date_debut_mois SMALLINT DEFAULT NULL, ADD date_fin_jour SMALLINT DEFAULT NULL, ADD date_fin_mois SMALLINT DEFAULT NULL');

        // Conversion des périodes existantes (date complète -> jour + mois récurrent).
        $this->addSql('UPDATE saison SET date_debut_jour = DAY(date_debut), date_debut_mois = MONTH(date_debut), date_fin_jour = DAY(date_fin), date_fin_mois = MONTH(date_fin) WHERE date_debut IS NOT NULL AND date_fin IS NOT NULL');

        $this->addSql('ALTER TABLE saison MODIFY date_debut_jour SMALLINT NOT NULL, MODIFY date_debut_mois SMALLINT NOT NULL, MODIFY date_fin_jour SMALLINT NOT NULL, MODIFY date_fin_mois SMALLINT NOT NULL');

        // Les anciennes dates deviennent facultatives (héritage / données historiques conservées).
        $this->addSql('ALTER TABLE saison CHANGE date_debut date_debut DATE DEFAULT NULL, CHANGE date_fin date_fin DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Restaure les colonnes NOT NULL d'origine : les saisons sans date historique
        // reçoivent une date de référence (année 2000, bissextile) issue du jour/mois.
        $this->addSql("UPDATE saison SET date_debut = STR_TO_DATE(CONCAT('2000-', LPAD(date_debut_mois, 2, '0'), '-', LPAD(date_debut_jour, 2, '0')), '%Y-%m-%d') WHERE date_debut IS NULL");
        $this->addSql("UPDATE saison SET date_fin = STR_TO_DATE(CONCAT('2000-', LPAD(date_fin_mois, 2, '0'), '-', LPAD(date_fin_jour, 2, '0')), '%Y-%m-%d') WHERE date_fin IS NULL");
        $this->addSql('ALTER TABLE saison CHANGE date_debut date_debut DATE NOT NULL, CHANGE date_fin date_fin DATE NOT NULL');
        $this->addSql('ALTER TABLE saison DROP date_debut_jour, DROP date_debut_mois, DROP date_fin_jour, DROP date_fin_mois');
    }
}
