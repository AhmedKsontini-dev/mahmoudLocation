<?php

namespace App\Service;

use App\Entity\Saison;
use App\Entity\Transfere;
use App\Repository\SaisonRepository;
use DateTimeInterface;
use DomainException;

class TransferePriceCalculator
{
    public function __construct(private readonly SaisonRepository $saisonRepository)
    {
    }

    public function getPrixPourDate(Transfere $transfere, ?DateTimeInterface $date = null): string
    {
        if ($date === null) {
            return $this->getTransferPrice($transfere->getPrixNormal());
        }

        return $this->getPrixPourDates($transfere, [$date]);
    }

    /**
     * @param list<DateTimeInterface> $dates
     */
    public function getPrixPourDates(Transfere $transfere, array $dates): string
    {
        if ($dates === []) {
            throw new DomainException('transfer.form.price_date_required');
        }

        $seasons = $this->saisonRepository->findActiveSeasons();
        $totalCents = 0;

        foreach ($dates as $date) {
            $matchingSeasons = array_values(array_filter(
                $seasons,
                fn (Saison $saison): bool => $this->isInSeason($date, $saison)
            ));

            if (count($matchingSeasons) > 1) {
                throw new DomainException('transfer.form.season_overlap');
            }

            $price = $matchingSeasons === []
                ? $transfere->getPrixNormal()
                : $transfere->getPrixSaisonForte();

            $totalCents += $this->toCents($this->getTransferPrice($price));
        }

        return sprintf('%d.%02d', intdiv($totalCents, 100), $totalCents % 100);
    }

    /**
     * @return list<array{startMonth: int, startDay: int, endMonth: int, endDay: int}>
     */
    public function getPeriodesActives(): array
    {
        $periods = [];
        foreach ($this->saisonRepository->findActiveSeasons() as $saison) {
            if ($this->hasRecurringPeriod($saison)) {
                $periods[] = [
                    'startMonth' => (int) $saison->getDateDebutMois(),
                    'startDay' => (int) $saison->getDateDebutJour(),
                    'endMonth' => (int) $saison->getDateFinMois(),
                    'endDay' => (int) $saison->getDateFinJour(),
                ];
            }
        }

        return $periods;
    }

    /**
     * Une saison est définie de façon récurrente par un jour et un mois uniquement.
     * La comparaison ignore donc l'année : la période se répète chaque année.
     * Les bornes de début et de fin sont incluses, et les périodes traversant le
     * changement d'année (ex. 01/12 -> 31/03) sont correctement gérées.
     */
    private function isInSeason(DateTimeInterface $date, Saison $saison): bool
    {
        if (!$this->hasRecurringPeriod($saison)) {
            return false;
        }

        $value = (int) $date->format('n') * 100 + (int) $date->format('j');
        $start = (int) $saison->getDateDebutMois() * 100 + (int) $saison->getDateDebutJour();
        $end = (int) $saison->getDateFinMois() * 100 + (int) $saison->getDateFinJour();

        if ($start <= $end) {
            return $value >= $start && $value <= $end;
        }

        // Période qui traverse la fin d'année (ex. 01/12 -> 31/03).
        return $value >= $start || $value <= $end;
    }

    private function hasRecurringPeriod(Saison $saison): bool
    {
        return $saison->getDateDebutJour() !== null
            && $saison->getDateDebutMois() !== null
            && $saison->getDateFinJour() !== null
            && $saison->getDateFinMois() !== null;
    }

    private function getTransferPrice(?string $price): string
    {
        if ($price === null || $price === '') {
            throw new DomainException('transfer.form.price_unavailable');
        }

        return $price;
    }

    private function toCents(string $price): int
    {
        [$whole, $fraction] = array_pad(explode('.', number_format((float) $price, 2, '.', '')), 2, '00');
        return ((int) $whole * 100) + (int) $fraction;
    }
}
