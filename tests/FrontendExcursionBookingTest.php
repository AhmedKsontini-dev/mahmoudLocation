<?php

namespace App\Tests;

use App\Entity\Category;
use App\Entity\CurrencyRate;
use App\Entity\Destination;
use App\Entity\Excursion;
use App\Entity\ExcursionTranslation;
use App\Entity\ReservationExcursion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FrontendExcursionBookingTest extends WebTestCase
{
    public function testBookingIsPersistedAndShowsPendingSummaryAfterRedirect(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $fixture = $this->createExcursion($entityManager);

        try {
            $crawler = $client->request('GET', '/fr/excursion/'.$fixture['slug']);
            self::assertResponseIsSuccessful();
            self::assertSelectorExists('#booking-form');

            // Verify only one visible "Réserver cette excursion" button exists
            $buttons = $crawler->filter('button, a')->reduce(function ($node) {
                return trim($node->text()) === 'Réserver cette excursion' || str_contains($node->text(), 'Réserver cette excursion');
            });
            self::assertCount(1, $buttons, 'There should be exactly one visible "Réserver cette excursion" button on the page.');
            self::assertSame('submit', $buttons->first()->attr('type'), 'The reservation button must be a submit button.');
            self::assertSame('booking-form', $buttons->first()->closest('form')->attr('id'), 'The button must belong to the booking form.');

            $form = $crawler->filter('#booking-form')->form();
            $form->setValues($this->validBookingValues($fixture['email']));
            $client->submit($form);

            self::assertResponseRedirects('/fr/excursion/'.$fixture['slug']);
            $client->followRedirect();
            self::assertResponseIsSuccessful();
            self::assertSelectorTextContains('[role="status"]', 'En attente de confirmation');
            self::assertSelectorTextContains('[role="status"]', 'Notre équipe vous contactera');
            self::assertSelectorTextContains('#reservation-status', 'Votre demande de réservation a été enregistrée avec succès !');
            self::assertSelectorTextContains('#reservation-status', 'Excursion test');
            self::assertSelectorTextContains('#reservation-status', 'Client');
            self::assertSelectorTextContains('#reservation-status', 'Date et heure');
            self::assertSelectorTextContains('#reservation-status', 'Participants');
            self::assertSelectorTextContains('#reservation-status', '2 adultes, 1 enfant');
            self::assertSelectorTextContains('#reservation-status', 'Lieu de prise en charge');
            self::assertSelectorTextContains('#reservation-status', 'Statut');

            // Test page refresh does not duplicate reservation (PRG pattern)
            $client->reload();
            self::assertResponseIsSuccessful();

            $entityManager->clear();
            $reservations = $entityManager->getRepository(ReservationExcursion::class)->findBy(['email' => $fixture['email']]);
            self::assertCount(1, $reservations, 'Exactly one reservation should exist even after page reload.');
            self::assertSame(ReservationExcursion::STATUS_PENDING, $reservations[0]->getStatut());
            self::assertSame($fixture['excursionId'], $reservations[0]->getExcursion()->getId());
            self::assertSame('Client', $reservations[0]->getNom());
            self::assertSame('Test', $reservations[0]->getPrenom());
            self::assertSame(2, $reservations[0]->getNombreAdultes());
            self::assertSame(1, $reservations[0]->getNombreEnfants());
            self::assertSame('+216 12 345 678', $reservations[0]->getWhatsapp());
            self::assertSame('Hôtel de test', $reservations[0]->getLieuPriseEnCharge());
            self::assertNotNull($reservations[0]->getDateExcursion());
            self::assertNotNull($reservations[0]->getHeureDepart());
            self::assertNotNull($reservations[0]->getCreatedAt());
        } finally {
            $this->removeExcursionFixture($entityManager, $fixture);
        }
    }

    public function testPastDateAndInvalidParticipantCountsAreRejected(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $fixture = $this->createExcursion($entityManager);

        try {
            $crawler = $client->request('GET', '/fr/excursion/'.$fixture['slug']);
            $form = $crawler->filter('#booking-form')->form();
            $form->setValues([
                'reservation_excursion[prenom]' => 'Test',
                'reservation_excursion[email]' => $fixture['email'],
                'reservation_excursion[whatsapp]' => '+216 12 345 678',
                'reservation_excursion[nombreAdultes]' => '0',
                'reservation_excursion[nombreEnfants]' => '-1',
                'reservation_excursion[dateExcursion]' => (new \DateTimeImmutable('-1 day'))->format('Y-m-d'),
                'reservation_excursion[heureDepart]' => '10:30',
                'reservation_excursion[lieuPriseEnCharge]' => 'Hôtel de test',
            ]);

            $client->submit($form);

            self::assertResponseIsSuccessful();
            self::assertSelectorTextContains('#booking-form', 'La date de l’excursion ne peut pas être passée.');
            $formText = $client->getCrawler()->filter('#booking-form')->text();
            self::assertTrue(
                str_contains($formText, 'This value should be positive.')
                || str_contains($formText, 'Cette valeur doit être strictement positive.')
            );
            self::assertSame(0, $entityManager->getRepository(ReservationExcursion::class)->count(['email' => $fixture['email']]));
        } finally {
            $this->removeExcursionFixture($entityManager, $fixture);
        }
    }

    public function testBookingConfirmationUsesTheSelectedLocaleAndExcursionTranslation(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $locales = [
            'en' => [
                'title' => 'Your excursion booking request has been submitted successfully!',
                'message' => 'Our team will contact you to confirm the details of your excursion.',
                'date_time' => 'Date and time',
                'participants_label' => 'Participants',
                'customer' => 'Customer',
                'excursion' => 'English test excursion',
                'pickup' => 'Pickup location',
                'status_label' => 'Status',
                'pending' => 'Awaiting confirmation',
                'participants' => '2 adults, 1 child',
            ],
        ];

        foreach ($locales as $locale => $expected) {
            $fixture = $this->createExcursion($entityManager);
            try {
                $crawler = $client->request('GET', '/' . $locale . '/excursion/' . $fixture['slug']);
                self::assertResponseIsSuccessful();

                $form = $crawler->filter('#booking-form')->form();
                $form->setValues($this->validBookingValues($fixture['email']));
                $client->submit($form);

                self::assertResponseRedirects('/' . $locale . '/excursion/' . $fixture['slug']);
                $client->followRedirect();
                self::assertResponseIsSuccessful();
                self::assertSelectorTextContains('#reservation-status', $expected['title']);
                self::assertSelectorTextContains('#reservation-status', $expected['message']);
                self::assertSelectorTextContains('#reservation-status', $expected['customer']);
                self::assertSelectorTextContains('#reservation-status', $expected['excursion']);
                self::assertSelectorTextContains('#reservation-status', $expected['date_time']);
                self::assertSelectorTextContains('#reservation-status', $expected['participants_label']);
                self::assertSelectorTextContains('#reservation-status', $expected['pickup']);
                self::assertSelectorTextContains('#reservation-status', $expected['status_label']);
                self::assertSelectorTextContains('#reservation-status', $expected['pending']);
                self::assertSelectorTextContains('#reservation-status', $expected['participants']);
            } finally {
                $this->removeExcursionFixture($entityManager, $fixture);
            }
        }
    }

    public function testExcursionDetailIsAvailableOnlyInFrenchAndEnglish(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $fixture = $this->createExcursion($entityManager);

        try {
            $crawler = $client->request('GET', '/en/excursion/' . $fixture['slug']);

            self::assertResponseIsSuccessful();
            self::assertSame(2, $crawler->filter('[data-navbar-dropdown-menu] a')->count());
            self::assertSame('/fr/excursion/' . $fixture['slug'], $crawler->filter('[data-navbar-dropdown-menu] a')->eq(0)->attr('href'));
            self::assertSame('/en/excursion/' . $fixture['slug'], $crawler->filter('[data-navbar-dropdown-menu] a')->eq(1)->attr('href'));
            self::assertStringContainsString('Book this excursion', $crawler->filter('#booking-form')->text());
            self::assertStringContainsString('Excursion date', $crawler->filter('#booking-form')->text());
            self::assertStringContainsString('What to expect', $crawler->text());
            self::assertStringContainsString('English test excursion', $crawler->text());
            self::assertStringNotContainsString('Programme de la journée', $crawler->text());
            self::assertStringNotContainsString('Questions fréquentes', $crawler->text());

            $form = $crawler->filter('#booking-form')->form();
            $invalidValues = $this->validBookingValues($fixture['email']);
            $invalidValues['reservation_excursion[dateExcursion]'] = (new \DateTimeImmutable('-1 day'))->format('Y-m-d');
            $form->setValues($invalidValues);
            $client->submit($form);
            self::assertResponseIsSuccessful();
            self::assertSelectorTextContains('#booking-form', 'The excursion date cannot be in the past.');

            $client->request('GET', '/it/excursion/' . $fixture['slug']);
            self::assertResponseStatusCodeSame(404);
        } finally {
            $this->removeExcursionFixture($entityManager, $fixture);
        }
    }

    public function testExcursionPricesRenderInTheSelectedCurrencyFromConfiguredRates(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $currencyRepository = $entityManager->getRepository(CurrencyRate::class);
        $fixture = $this->createExcursion($entityManager);
        $createdCurrencies = [];

        try {
            foreach ([
                'TND' => ['DT', '1.000000'],
                'EUR' => ['€', '0.300000'],
                'USD' => ['$', '0.350000'],
            ] as $code => [$symbol, $rate]) {
                if ($currencyRepository->findOneBy(['code' => $code]) === null) {
                    $currency = (new CurrencyRate())
                        ->setCode($code)
                        ->setSymbol($symbol)
                        ->setRate($rate);
                    $entityManager->persist($currency);
                    $createdCurrencies[] = $currency;
                }
            }
            $entityManager->flush();

            foreach (['TND', 'EUR', 'USD'] as $code) {
                $currency = $currencyRepository->findOneBy(['code' => $code]);
                self::assertNotNull($currency, 'Configured currency rate missing for ' . $code);

                $client->request('GET', '/currency/' . $code);
                self::assertResponseIsSuccessful();

                $crawler = $client->request('GET', '/fr/excursion/' . $fixture['slug']);
                self::assertResponseIsSuccessful();
                self::assertSame('50.00', $crawler->filter('[data-price-base]')->first()->attr('data-price-base'));
                self::assertSame(
                    static::getContainer()->get(\App\Service\CurrencyService::class)->format(50, $currency),
                    trim($crawler->filter('[data-price-base]')->first()->text())
                );
            }
        } finally {
            $this->removeExcursionFixture($entityManager, $fixture);
            foreach ($createdCurrencies as $currency) {
                $managedCurrency = $currencyRepository->find($currency->getId());
                if ($managedCurrency !== null) {
                    $entityManager->remove($managedCurrency);
                }
            }
            $entityManager->flush();
        }
    }

    /**
     * @return array{slug: string, email: string, excursionId: int}
     */
    private function createExcursion(EntityManagerInterface $entityManager): array
    {
        $suffix = bin2hex(random_bytes(5));
        $category = (new Category())->setSlug('booking-category-'.$suffix);
        $destination = (new Destination())->setSlug('booking-destination-'.$suffix);
        $excursion = (new Excursion())
            ->setCategory($category)
            ->setDestination($destination)
            ->setPriceAdult('50.00')
            ->setSlug('booking-excursion-'.$suffix)
            ->setStatus(Excursion::STATUS_ACTIVE);
        $excursion->addTranslation((new ExcursionTranslation())->setLocale('fr')->setTitle('Excursion test')->setDescription('<p>Une escapade pour découvrir la Tunisie.</p>'));
        $excursion->addTranslation((new ExcursionTranslation())->setLocale('en')->setTitle('English test excursion')->setDescription('<p>A tour to discover Tunisia.</p>'));
        $excursion->addTranslation((new ExcursionTranslation())->setLocale('it')->setTitle('Escursione di prova italiana'));

        $entityManager->persist($category);
        $entityManager->persist($destination);
        $entityManager->persist($excursion);
        $entityManager->flush();

        return [
            'slug' => $excursion->getSlug(),
            'email' => 'booking-'.$suffix.'@example.test',
            'excursionId' => $excursion->getId(),
        ];
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
            'reservation_excursion[nombreAdultes]' => '2',
            'reservation_excursion[nombreEnfants]' => '1',
            'reservation_excursion[dateExcursion]' => (new \DateTimeImmutable('+2 days'))->format('Y-m-d'),
            'reservation_excursion[heureDepart]' => '10:30',
            'reservation_excursion[lieuPriseEnCharge]' => 'Hôtel de test',
        ];
    }
}
