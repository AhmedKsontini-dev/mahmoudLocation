<?php

namespace App\Service;

use App\Entity\Saison;
use App\Entity\Transfere;
use App\Repository\SaisonRepository;
use DateTimeInterface;

class TransferePriceCalculator
{
    public function __construct(private readonly SaisonRepository $saisonRepository)
    {
    }

    public function getPrixPourDate(Transfere $transfere, ?DateTimeInterface $date = null): string
    {
        $saison = $this->saisonRepository->findActive();

        if ($date !== null && $saison !== null && $this->isInSeason($date, $saison)) {
            return $transfere->getPrixSaisonForte();
        }

        return $transfere->getPrixNormal();
    }

    private function isInSeason(DateTimeInterface $date, Saison $saison): bool
    {
        if ($saison->getDateDebut() === null || $saison->getDateFin() === null) {
            return false;
        }

        $value = ((int) $date->format('m')) * 100 + (int) $date->format('d');
        $start = ((int) $saison->getDateDebut()->format('m')) * 100 + (int) $saison->getDateDebut()->format('d');
        $end = ((int) $saison->getDateFin()->format('m')) * 100 + (int) $saison->getDateFin()->format('d');

        return $start <= $end
            ? $value >= $start && $value <= $end
            : $value >= $start || $value <= $end;
    }
}
