<?php

namespace App\Tests;

use App\Entity\Category;
use App\Entity\CurrencyRate;
use App\Entity\Destination;
use App\Entity\Excursion;
use App\Entity\ExcursionTranslation;
use App\Entity\ReservationExcursion;
use App\Service\CurrencyService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FrontendExcursionTotalPriceTest extends WebTestCase
{
    public function testPrixTotalCalculeCoteServeurDepuisLesPrixEnBase(): void
    {
        $excursion = (new Excursion())
            ->setPriceAdult('80.00')
            ->setPriceChild('40.00');

        // 0 adulte et 0 enfant : total de 0
        $reservation = (new ReservationExcursion())
            ->setExcursion($excursion)
            ->setNombreAdultes(0)
            ->setNombreEnfants(0);
        self::assertSame(0.0, $reservation->getPrixTotalCalcule());

        // 1 adulte et 0 enfant : total égal au prix adulte
        $reservation->setNombreAdultes(1);
        self::assertSame(80.0, $reservation->getPrixTotalCalcule());

        // 2 adultes et 1 enfant : (2 × 80) + (1 × 40) = 200
        $reservation->setNombreAdultes(2)->setNombreEnfants(1);
        self::assertSame(200.0, $reservation->getPrixTotalCalcule());

        // Prix enfant null : seuls les adultes comptent
        $excursion->setPriceChild(null);
        self::assertSame(160.0, $reservation->getPrixTotalCalcule());
    }

    public function testSidebarPricesAndTotalMatchFormInitialValues(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $fixture = $this->createExcursion($entityManager);

        try {
            $crawler = $client->request('GET', '/fr/excursion/' . $fixture['slug']);
            self::assertResponseIsSuccessful();

            // Les prix réels de l'excursion sont transmis au JS depuis la base, jamais en dur
            $pricing = $crawler->filter('#booking-pricing');
            self::assertCount(1, $pricing);
            self::assertSame('80.00', $pricing->attr('data-adult-price'));
            self::assertSame('40.00', $pricing->attr('data-child-price'));

            // Les deux montants sont initialisés depuis les valeurs du formulaire (1 adulte, 0 enfant par défaut) et restent identiques
            $total = $crawler->filter('#booking-total');
            $totalMain = $crawler->filter('#booking-total-main');
            self::assertCount(1, $total);
            self::assertCount(1, $totalMain);
            self::assertSame('80.00', $total->attr('data-price-base'));
            self::assertSame('80.00', $totalMain->attr('data-price-base'));
            self::assertSame($totalMain->text(), $total->text(), 'Le prix bleu et la ligne Montant total doivent afficher le même montant.');

            // Le prix adulte et le prix enfant réels sont affichés dans le panneau latéral (valeurs de référence TND)
            self::assertGreaterThanOrEqual(1, $crawler->filter('[data-price-base="80.00"]')->count());
            self::assertGreaterThanOrEqual(1, $crawler->filter('[data-price-base="40.00"]')->count());

            // Les limites maximales de participants sont appliquées aux champs du formulaire
            $adults = $crawler->filter('#reservation_excursion_nombreAdultes')->first()->attr('max');
            $children = $crawler->filter('#reservation_excursion_nombreEnfants')->first()->attr('max');
            self::assertSame('12', $adults);
            self::assertSame('12', $children);
        } finally {
            $this->removeExcursionFixture($entityManager, $fixture);
        }
    }

    public function testSubmittedReservationRecalculatesTotalServerSideInSelectedCurrency(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $fixture = $this->createExcursion($entityManager);

        try {
            $crawler = $client->request('GET', '/fr/excursion/' . $fixture['slug']);
            $form = $crawler->filter('#booking-form')->form();
            $values = $this->validBookingValues($fixture['email']);
            $values['reservation_excursion[nombreAdultes]'] = '2';
            $values['reservation_excursion[nombreEnfants]'] = '1';
            $form->setValues($values);
            $client->submit($form);

            self::assertResponseRedirects('/fr/excursion/' . $fixture['slug']);
            $client->followRedirect();
            self::assertResponseIsSuccessful();

            // Le résumé affiche le montant recalculé côté serveur : (2 × 80) + (1 × 40) = 200 DT
            $currencyService = static::getContainer()->get(CurrencyService::class);
            self::assertSelectorTextContains('#reservation-status', 'Montant total');
            self::assertSelectorTextContains('#reservation-status', $currencyService->format(200));
        } finally {
            $this->removeExcursionFixture($entityManager, $fixture);
        }
    }

    public function testTotalIsConvertedExactlyOnceWhenCurrencyChanges(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $createdCurrencies = $this->ensureCurrencyRates($entityManager);
        $fixture = $this->createExcursion($entityManager);

        try {
            // Devise EUR sélectionnée avant la réservation : la conversion est appliquée une seule fois
            // aux prix de référence TND stockés en base (200 DT → 60 €, jamais 200 × taux × taux).
            $client->request('GET', '/currency/EUR');
            self::assertResponseIsSuccessful();

            $crawler = $client->request('GET', '/fr/excursion/' . $fixture['slug']);
            $form = $crawler->filter('#booking-form')->form();
            $values = $this->validBookingValues($fixture['email']);
            $values['reservation_excursion[nombreAdultes]'] = '2';
            $values['reservation_excursion[nombreEnfants]'] = '1';
            $form->setValues($values);
            $client->submit($form);
            self::assertResponseRedirects('/fr/excursion/' . $fixture['slug']);
            $client->followRedirect();
            self::assertResponseIsSuccessful();

            $currencyService = static::getContainer()->get(CurrencyService::class);
            $eur = $entityManager->getRepository(CurrencyRate::class)->findOneBy(['code' => 'EUR']);
            self::assertNotNull($eur, 'Le jeu de test doit contenir la devise EUR');
            // Conversion appliquée une seule fois depuis la valeur de référence TND (200)
            $expected = $currencyService->format(200, $eur);
            self::assertStringContainsString($eur->getSymbol(), $expected);
            self::assertSelectorTextContains('#reservation-status', $expected);
        } finally {
            $this->removeExcursionFixture($entityManager, $fixture);
            $this->removeCreatedCurrencies($entityManager, $createdCurrencies);
        }
    }

    /**
     * @return array{slug: string, email: string, excursionId: int}
     */
    private function createExcursion(EntityManagerInterface $entityManager): array
    {
        $suffix = bin2hex(random_bytes(5));
        $category = (new Category())->setSlug('total-price-category-' . $suffix);
        $destination = (new Destination())->setSlug('total-price-destination-' . $suffix);
        $excursion = (new Excursion())
            ->setCategory($category)
            ->setDestination($destination)
            ->setPriceAdult('80.00')
            ->setPriceChild('40.00')
            ->setMaxParticipants(12)
            ->setSlug('total-price-excursion-' . $suffix)
            ->setStatus(Excursion::STATUS_ACTIVE);
        $excursion->addTranslation((new ExcursionTranslation())->setLocale('fr')->setTitle('Excursion total price test')->setDescription('<p>Une escapade pour découvrir la Tunisie.</p>'));

        $entityManager->persist($category);
        $entityManager->persist($destination);
        $entityManager->persist($excursion);
        $entityManager->flush();

        return [
            'slug' => $excursion->getSlug(),
            'email' => 'total-price-' . $suffix . '@example.test',
            'excursionId' => $excursion->getId(),
        ];
    }

    /**
     * @return list<CurrencyRate>
     */
    private function ensureCurrencyRates(EntityManagerInterface $entityManager): array
    {
        $repository = $entityManager->getRepository(CurrencyRate::class);
        $created = [];
        foreach (['TND' => ['DT', '1.000000'], 'EUR' => ['€', '0.300000'], 'USD' => ['$', '0.350000']] as $code => [$symbol, $rate]) {
            if ($repository->findOneBy(['code' => $code]) === null) {
                $currency = (new CurrencyRate())->setCode($code)->setSymbol($symbol)->setRate($rate);
                $entityManager->persist($currency);
                $created[] = $currency;
            }
        }
        $entityManager->flush();

        return $created;
    }

    /**
     * @param list<CurrencyRate> $createdCurrencies
     */
    private function removeCreatedCurrencies(EntityManagerInterface $entityManager, array $createdCurrencies): void
    {
        $repository = $entityManager->getRepository(CurrencyRate::class);
        foreach ($createdCurrencies as $currency) {
            $managedCurrency = $repository->find($currency->getId());
            if ($managedCurrency !== null) {
                $entityManager->remove($managedCurrency);
            }
        }
        $entityManager->flush();
    }

    private function removeExcursionFixture(EntityManagerInterface $entityManager, array $fixture): void
    {
        $entityManager->clear();
        $entityManager->createQuery('DELETE FROM App\Entity\ReservationExcursion r WHERE r.email = :email')
            ->setParameter('email', $fixture['email'])
            ->execute();

        $excursion = $entityManager->getRepository(Excursion::class)->findOneBy(['slug' => $fixture['slug']]);
        if ($excursion !== null) {
            $category = $excursion->getCategory();
            $destination = $excursion->getDestination();
            $entityManager->remove($excursion);
            $entityManager->flush();
            if ($category) {
                $entityManager->remove($category);
            }
            if ($destination) {
                $entityManager->remove($destination);
            }
            $entityManager->flush();
        }
    }

    private function validBookingValues(string $email): array
    {
        return [
            'reservation_excursion[nom]' => 'Client',
            'reservation_excursion[prenom]' => 'Test',
            'reservation_excursion[email]' => $email,
            'reservation_excursion[whatsapp]' => '+216 12 345 678',
            'reservation_excursion[nombreAdultes]' => '1',
            'reservation_excursion[nombreEnfants]' => '0',
            'reservation_excursion[dateExcursion]' => (new \DateTimeImmutable('+2 days'))->format('Y-m-d'),
            'reservation_excursion[heureDepart]' => '10:30',
            'reservation_excursion[lieuPriseEnCharge]' => 'Hôtel de test',
        ];
    }
}
