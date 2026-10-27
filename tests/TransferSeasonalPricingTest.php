<?php

namespace App\Tests;

use App\Entity\CurrencyRate;
use App\Entity\ReservationTransfere;
use App\Entity\Saison;
use App\Entity\Transfere;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Couvre la détection des périodes de saison forte RÉCURRENTES (jour + mois, sans
 * année) et le recalcul serveur du prix d'un transfert à l'enregistrement.
 */
class TransferSeasonalPricingTest extends WebTestCase
{
    // Saison forte récurrente du 15/06 au 15/09, valable chaque année.
    private const SEASON_START_MONTH = 6;
    private const SEASON_START_DAY = 15;
    private const SEASON_END_MONTH = 9;
    private const SEASON_END_DAY = 15;
    private const PRIX_NORMAL = '50.00';
    private const PRIX_SAISON = '100.00';

    private string $slug;
    private string $email;
    private string $seasonName;

    private function seed(int $startMonth = self::SEASON_START_MONTH, int $startDay = self::SEASON_START_DAY, int $endMonth = self::SEASON_END_MONTH, int $endDay = self::SEASON_END_DAY): void
    {
        $suffix = bin2hex(random_bytes(5));
        $this->slug = 'seasonal-' . $suffix;
        $this->email = 'seasonal-' . $suffix . '@example.test';
        $this->seasonName = 'Saison forte de test ' . $suffix;

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $season = (new Saison())
            ->setNom($this->seasonName)
            ->setDateDebutMois($startMonth)
            ->setDateDebutJour($startDay)
            ->setDateFinMois($endMonth)
            ->setDateFinJour($endDay)
            ->setActif(true);

        $transfer = (new Transfere())
            ->setLieuDepart('Aéroport Tunis')
            ->setLieuArrivee('Hammamet')
            ->setPrixNormal(self::PRIX_NORMAL)
            ->setPrixSaisonForte(self::PRIX_SAISON)
            ->setSlug($this->slug)
            ->setActif(true);

        $existingTnd = $entityManager->getRepository(CurrencyRate::class)->findOneBy(['code' => 'TND']);
        $entityManager->persist($season);
        $entityManager->persist($transfer);
        if ($existingTnd === null) {
            $entityManager->persist((new CurrencyRate())->setCode('TND')->setSymbol('DT')->setRate('1.000000'));
        }
        $entityManager->flush();
        $entityManager->clear();
    }

    private function cleanup(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->createQuery('DELETE FROM App\Entity\ReservationTransfere r WHERE r.email LIKE :email')
            ->setParameter('email', $this->email . '%')->execute();
        $transfer = $entityManager->getRepository(Transfere::class)->findOneBy(['slug' => $this->slug]);
        if ($transfer !== null) {
            $entityManager->remove($transfer);
        }
        $season = $entityManager->getRepository(Saison::class)->findOneBy(['nom' => $this->seasonName]);
        if ($season !== null) {
            $entityManager->remove($season);
        }
        $entityManager->flush();
    }

    #[DataProvider('dateToExpectedPrice')]
    public function testServerRecalculatesSeasonalPriceOnBooking(string $date, string $expectedPrixTotal): void
    {
        $client = static::createClient();
        $this->seed();

        try {
            $entityManager = static::getContainer()->get(EntityManagerInterface::class);

            $crawler = $client->request('GET', '/fr/transfers/' . $this->slug);
            self::assertResponseIsSuccessful();

            $form = $crawler->filter('#fast-booking-form')->form();
            $form->setValues([
                'reservation_transfere_form[transfereType]' => 'one_way',
                'reservation_transfere_form[pickupLocation]' => 'Aéroport Tunis',
                'reservation_transfere_form[dropoffLocation]' => 'Hammamet',
                'reservation_transfere_form[pickupDate]' => $date,
                'reservation_transfere_form[pickupTime]' => '10:30',
                'reservation_transfere_form[nom]' => 'Client saison',
                'reservation_transfere_form[email]' => $this->email,
                'reservation_transfere_form[telephone]' => '+21600000000',
                'reservation_transfere_form[nombrePassagers]' => '2',
                'reservation_transfere_form[nombreBagages]' => '1',
            ]);

            $client->submit($form);
            self::assertResponseRedirects('/fr/transfers/' . $this->slug);

            $reservation = $entityManager->getRepository(ReservationTransfere::class)->findOneBy(['email' => $this->email]);
            self::assertNotNull($reservation, 'La réservation doit être enregistrée.');
            self::assertSame(
                $expectedPrixTotal,
                number_format((float) $reservation->getPrixTotal(), 2, '.', ''),
                sprintf('Le prix serveur pour la date %s doit être %s.', $date, $expectedPrixTotal)
            );
        } finally {
            $this->cleanup();
        }
    }

    /**
     * La période étant récurrente, la même saison s'applique quelle que soit l'année.
     * @return array<string, array{string, string}>
     */
    public static function dateToExpectedPrice(): array
    {
        return [
            'veille du début' => ['2028-06-14', self::PRIX_NORMAL],
            'début exactement (inclus)' => ['2028-06-15', self::PRIX_SAISON],
            'milieu saison 2028' => ['2028-07-15', self::PRIX_SAISON],
            'milieu saison 2027' => ['2027-07-15', self::PRIX_SAISON],
            'milieu saison 2030' => ['2030-07-15', self::PRIX_SAISON],
            'fin exactement (inclus)' => ['2029-09-15', self::PRIX_SAISON],
            'lendemain de la fin' => ['2027-09-16', self::PRIX_NORMAL],
            'hors saison nov' => ['2027-11-15', self::PRIX_NORMAL],
        ];
    }

    public function testSeasonCrossingYearEndAppliesEveryYear(): void
    {
        $client = static::createClient();
        // Saison forte récurrente du 01/12 au 31/03.
        $this->seed(12, 1, 3, 31);

        try {
            $entityManager = static::getContainer()->get(EntityManagerInterface::class);

            foreach (['2027-01-15' => self::PRIX_SAISON, '2026-12-20' => self::PRIX_SAISON, '2027-03-31' => self::PRIX_SAISON, '2027-06-15' => self::PRIX_NORMAL] as $date => $expected) {
                $crawler = $client->request('GET', '/fr/transfers/' . $this->slug);
                $form = $crawler->filter('#fast-booking-form')->form();
                $form->setValues([
                    'reservation_transfere_form[transfereType]' => 'one_way',
                    'reservation_transfere_form[pickupLocation]' => 'Aéroport Tunis',
                    'reservation_transfere_form[dropoffLocation]' => 'Hammamet',
                    'reservation_transfere_form[pickupDate]' => $date,
                    'reservation_transfere_form[pickupTime]' => '10:30',
                    'reservation_transfere_form[nom]' => 'Client saison',
                    'reservation_transfere_form[email]' => $this->email . '-' . $date,
                    'reservation_transfere_form[telephone]' => '+21600000000',
                    'reservation_transfere_form[nombrePassagers]' => '2',
                    'reservation_transfere_form[nombreBagages]' => '1',
                ]);

                $client->submit($form);
                self::assertResponseRedirects('/fr/transfers/' . $this->slug);

                $reservation = $entityManager->getRepository(ReservationTransfere::class)->findOneBy(['email' => $this->email . '-' . $date]);
                self::assertNotNull($reservation);
                self::assertSame($expected, number_format((float) $reservation->getPrixTotal(), 2, '.', ''),
                    sprintf('La date %s doit donner le prix attendu.', $date));
            }
        } finally {
            $this->cleanup();
        }
    }

    public function testShowPageExposesRecurringSeasonPeriodsToFrontend(): void
    {
        $client = static::createClient();
        $this->seed();

        try {
            $crawler = $client->request('GET', '/fr/transfers/' . $this->slug . '?pickupDate=2027-07-01');
            self::assertResponseIsSuccessful();

            $priceNode = $crawler->filter('#side-total-price');
            self::assertCount(1, $priceNode);
            self::assertSame(self::PRIX_NORMAL, $priceNode->attr('data-normal-price'));
            self::assertSame(self::PRIX_SAISON, $priceNode->attr('data-season-price'));

            $periods = json_decode($priceNode->attr('data-season-periods'), true, 512, JSON_THROW_ON_ERROR);
            self::assertContains(
                [
                    'startMonth' => self::SEASON_START_MONTH,
                    'startDay' => self::SEASON_START_DAY,
                    'endMonth' => self::SEASON_END_MONTH,
                    'endDay' => self::SEASON_END_DAY,
                ],
                $periods,
                'La période récurrente (jour/mois) doit être exposée au JavaScript.'
            );

            $dateInput = $crawler->filter('#reservation_transfere_form_pickupDate');
            self::assertSame('date', $dateInput->attr('type'));
            self::assertSame('2027-07-01', $dateInput->attr('value'));
        } finally {
            $this->cleanup();
        }
    }

    public function testReturnTripTotalsBothLegsByTheirOwnDates(): void
    {
        $client = static::createClient();
        $this->seed();

        try {
            $entityManager = static::getContainer()->get(EntityManagerInterface::class);

            $crawler = $client->request('GET', '/fr/transfers/' . $this->slug);
            $form = $crawler->filter('#fast-booking-form')->form();
            $form->setValues([
                'reservation_transfere_form[transfereType]' => 'return',
                'reservation_transfere_form[pickupLocation]' => 'Aéroport Tunis',
                'reservation_transfere_form[dropoffLocation]' => 'Hammamet',
                'reservation_transfere_form[pickupDate]' => '2027-07-01', // en saison -> 100
                'reservation_transfere_form[pickupTime]' => '10:30',
                'reservation_transfere_form[returnPickupLocation]' => 'Hammamet',
                'reservation_transfere_form[returnDropoffLocation]' => 'Aéroport Tunis',
                'reservation_transfere_form[returnPickupDate]' => '2028-09-20', // hors saison -> 50
                'reservation_transfere_form[returnPickupTime]' => '18:00',
                'reservation_transfere_form[nom]' => 'Client saison',
                'reservation_transfere_form[email]' => $this->email,
                'reservation_transfere_form[telephone]' => '+21600000000',
                'reservation_transfere_form[nombrePassagers]' => '2',
                'reservation_transfere_form[nombreBagages]' => '1',
            ]);

            $client->submit($form);
            self::assertResponseRedirects('/fr/transfers/' . $this->slug);

            $reservation = $entityManager->getRepository(ReservationTransfere::class)->findOneBy(['email' => $this->email]);
            self::assertNotNull($reservation);
            self::assertSame('150.00', number_format((float) $reservation->getPrixTotal(), 2, '.', ''),
                'Aller en saison (100) + retour hors saison (50).');
        } finally {
            $this->cleanup();
        }
    }
}
