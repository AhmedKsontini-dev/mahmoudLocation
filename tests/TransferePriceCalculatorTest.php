<?php

namespace App\Tests;

use App\Entity\Saison;
use App\Entity\Transfere;
use App\Repository\SaisonRepository;
use App\Service\TransferePriceCalculator;
use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\TestCase;

/**
 * Le tarif dépend désormais d'une période récurrente (jour + mois) qui se répète
 * chaque année : l'année de la date de départ est ignorée.
 */
class TransferePriceCalculatorTest extends TestCase
{
    public function testRecurringSeasonAppliesEveryYearAndBoundariesAreIncluded(): void
    {
        $calculator = $this->calculatorWithSeasons([$this->createSeason(6, 1, 9, 30)]);

        // 15/07 : saison forte quelle que soit l'année.
        self::assertSame('65.00', $calculator->getPrixPourDate($this->createTransfer(), new DateTimeImmutable('2026-07-15')));
        self::assertSame('65.00', $calculator->getPrixPourDate($this->createTransfer(), new DateTimeImmutable('2027-07-15')));
        self::assertSame('65.00', $calculator->getPrixPourDate($this->createTransfer(), new DateTimeImmutable('2030-07-15')));

        // Bornes exactes incluses.
        self::assertSame('65.00', $calculator->getPrixPourDate($this->createTransfer(), new DateTimeImmutable('2028-06-01')));
        self::assertSame('65.00', $calculator->getPrixPourDate($this->createTransfer(), new DateTimeImmutable('2029-09-30')));

        // Hors période : prix normal.
        self::assertSame('50.00', $calculator->getPrixPourDate($this->createTransfer(), new DateTimeImmutable('2027-11-15')));
        self::assertSame('50.00', $calculator->getPrixPourDate($this->createTransfer(), new DateTimeImmutable('2026-05-31')));
        self::assertSame('50.00', $calculator->getPrixPourDate($this->createTransfer(), new DateTimeImmutable('2026-10-01')));
    }

    public function testSeasonCrossingYearEndAppliesToBothExtremes(): void
    {
        // Saison forte du 01/12 au 31/03 (traverse le changement d'année).
        $calculator = $this->calculatorWithSeasons([$this->createSeason(12, 1, 3, 31)]);

        self::assertSame('65.00', $calculator->getPrixPourDate($this->createTransfer(), new DateTimeImmutable('2027-01-15')));
        self::assertSame('65.00', $calculator->getPrixPourDate($this->createTransfer(), new DateTimeImmutable('2026-12-01')));
        self::assertSame('65.00', $calculator->getPrixPourDate($this->createTransfer(), new DateTimeImmutable('2027-03-31')));

        // Période creuse au milieu de l'année : prix normal.
        self::assertSame('50.00', $calculator->getPrixPourDate($this->createTransfer(), new DateTimeImmutable('2027-06-15')));
        self::assertSame('50.00', $calculator->getPrixPourDate($this->createTransfer(), new DateTimeImmutable('2027-04-01')));
        self::assertSame('50.00', $calculator->getPrixPourDate($this->createTransfer(), new DateTimeImmutable('2027-11-30')));
    }

    public function testLeapDayIsHandledForRecurringSeason(): void
    {
        // Saison incluant le 29 février (année bissextile réelle).
        $calculator = $this->calculatorWithSeasons([$this->createSeason(2, 28, 3, 1)]);

        self::assertSame('65.00', $calculator->getPrixPourDate($this->createTransfer(), new DateTimeImmutable('2028-02-29')));
        self::assertSame('65.00', $calculator->getPrixPourDate($this->createTransfer(), new DateTimeImmutable('2026-02-28')));
        self::assertSame('65.00', $calculator->getPrixPourDate($this->createTransfer(), new DateTimeImmutable('2026-03-01')));
        self::assertSame('50.00', $calculator->getPrixPourDate($this->createTransfer(), new DateTimeImmutable('2026-02-27')));
    }

    public function testReturnLegPricesAreCalculatedAgainstTheirOwnDates(): void
    {
        $calculator = $this->calculatorWithSeasons([$this->createSeason(6, 1, 9, 30)]);

        self::assertSame(
            '115.00',
            $calculator->getPrixPourDates(
                $this->createTransfer(),
                [new DateTimeImmutable('2027-05-31'), new DateTimeImmutable('2027-06-01')]
            )
        );
    }

    public function testOverlappingActiveSeasonsAreRejectedForAffectedDates(): void
    {
        $calculator = $this->calculatorWithSeasons([
            $this->createSeason(6, 1, 9, 30),
            $this->createSeason(8, 1, 10, 31),
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('transfer.form.season_overlap');
        $calculator->getPrixPourDate($this->createTransfer(), new DateTimeImmutable('2031-08-01'));
    }

    public function testMissingSeasonPriceIsRejectedInsteadOfFallingBackToNormalPrice(): void
    {
        $calculator = $this->calculatorWithSeasons([$this->createSeason(6, 1, 9, 30)]);
        $transfer = $this->createTransfer();
        (new \ReflectionProperty(Transfere::class, 'prixSaisonForte'))->setValue($transfer, null);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('transfer.form.price_unavailable');
        $calculator->getPrixPourDate($transfer, new DateTimeImmutable('2026-06-01'));
    }

    public function testExposedPeriodsUseRecurringMonthAndDay(): void
    {
        $calculator = $this->calculatorWithSeasons([$this->createSeason(12, 1, 3, 31)]);

        self::assertSame(
            [['startMonth' => 12, 'startDay' => 1, 'endMonth' => 3, 'endDay' => 31]],
            $calculator->getPeriodesActives()
        );
    }

    /**
     * @param list<Saison> $seasons
     */
    private function calculatorWithSeasons(array $seasons): TransferePriceCalculator
    {
        $repository = $this->createStub(SaisonRepository::class);
        $repository->method('findActiveSeasons')->willReturn($seasons);

        return new TransferePriceCalculator($repository);
    }

    private function createSeason(int $startMonth, int $startDay, int $endMonth, int $endDay): Saison
    {
        return (new Saison())
            ->setNom('Season')
            ->setDateDebutMois($startMonth)
            ->setDateDebutJour($startDay)
            ->setDateFinMois($endMonth)
            ->setDateFinJour($endDay);
    }

    private function createTransfer(): Transfere
    {
        return (new Transfere())
            ->setLieuDepart('Tunis')
            ->setLieuArrivee('Hammamet')
            ->setPrixNormal('50.00')
            ->setPrixSaisonForte('65.00')
            ->setSlug('test-transfer');
    }
}
