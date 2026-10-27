<?php

namespace App\Tests;

use App\Entity\CurrencyRate;
use App\Entity\ReservationTransfere;
use App\Entity\Transfere;
use App\Enum\ReservationStatus;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Validation et sécurité frontend du formulaire de réservation de transfert.
 * La validation Symfony (serveur) est la source de vérité ; le frontend n'est
 * qu'une aide. Couvre noms, e-mail, téléphone, dates (passé / aller-retour),
 * nombre de passagers, transfert inexistant et champs client non fiables.
 */
class TransferReservationValidationTest extends WebTestCase
{
    private string $slug;
    private string $email;

    private function seed(): void
    {
        $suffix = bin2hex(random_bytes(5));
        $this->slug = 'val-' . $suffix;
        $this->email = 'val-' . $suffix . '@example.test';

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $transfer = (new Transfere())
            ->setLieuDepart('Aéroport Tunis')
            ->setLieuArrivee('Hammamet')
            ->setPrixNormal('50.00')
            ->setPrixSaisonForte('65.00')
            ->setSlug($this->slug)
            ->setActif(true);

        $entityManager->persist($transfer);
        if ($entityManager->getRepository(CurrencyRate::class)->findOneBy(['code' => 'TND']) === null) {
            $entityManager->persist((new CurrencyRate())->setCode('TND')->setSymbol('DT')->setRate('1.000000'));
        }
        $entityManager->flush();
    }

    private function cleanup(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->createQuery('DELETE FROM App\Entity\ReservationTransfere r WHERE r.email LIKE :email')
            ->setParameter('email', 'val-%@example.test')->execute();
        $transfer = $entityManager->getRepository(Transfere::class)->findOneBy(['slug' => $this->slug]);
        if ($transfer !== null) {
            $entityManager->remove($transfer);
        }
        $entityManager->flush();
    }

    /** @return array<string, mixed> Valeurs de base volontairement valides. */
    private function validValues(array $overrides = []): array
    {
        return array_merge([
            'reservation_transfere_form[transfereType]' => 'one_way',
            'reservation_transfere_form[pickupLocation]' => 'Aéroport Tunis',
            'reservation_transfere_form[dropoffLocation]' => 'Hammamet',
            'reservation_transfere_form[pickupDate]' => (new \DateTimeImmutable('+3 days'))->format('Y-m-d'),
            'reservation_transfere_form[pickupTime]' => '10:30',
            'reservation_transfere_form[nom]' => 'Ahmed Ben Ali',
            'reservation_transfere_form[email]' => $this->email,
            'reservation_transfere_form[telephone]' => '+21612345678',
            'reservation_transfere_form[nombrePassagers]' => '2',
            'reservation_transfere_form[nombreBagages]' => '1',
        ], $overrides);
    }

    #[DataProvider('validNames')]
    public function testValidNamesAreAccepted(string $name): void
    {
        $client = static::createClient();
        $this->seed();
        try {
            $entityManager = static::getContainer()->get(EntityManagerInterface::class);
            $crawler = $client->request('GET', '/fr/transfers/' . $this->slug);
            $form = $crawler->filter('#fast-booking-form')->form();
            $form->setValues($this->validValues(['reservation_transfere_form[nom]' => $name]));
            $client->submit($form);

            self::assertResponseRedirects('/fr/transfers/' . $this->slug, null, sprintf('Le nom valide « %s » doit être accepté.', $name));
            self::assertNotNull($entityManager->getRepository(ReservationTransfere::class)->findOneBy(['email' => $this->email]));
        } finally {
            $this->cleanup();
        }
    }

    /** @return array<string, array{string}> */
    public static function validNames(): array
    {
        return [
            'lettres seules' => ['Ahmed'],
            'trait d\'union' => ['Jean-Pierre'],
            'espace' => ['Ben Ali'],
            'apostrophe' => ["O'Connor"],
            'accentué' => ['Élodie Naïma'],
        ];
    }

    #[DataProvider('invalidNames')]
    public function testInvalidNamesAreRejected(string $name): void
    {
        $client = static::createClient();
        $this->seed();
        try {
            $entityManager = static::getContainer()->get(EntityManagerInterface::class);
            $crawler = $client->request('GET', '/fr/transfers/' . $this->slug);
            $form = $crawler->filter('#fast-booking-form')->form();
            $form->setValues($this->validValues(['reservation_transfere_form[nom]' => $name]));
            $client->submit($form);

            self::assertFalse($client->getResponse()->isRedirect(), sprintf('Le nom invalide « %s » doit être refusé.', $name));
            self::assertCount(0, $entityManager->getRepository(ReservationTransfere::class)->findBy(['email' => $this->email]));
            self::assertStringContainsString('nom', $client->getResponse()->getContent());
        } finally {
            $this->cleanup();
        }
    }

    /** @return array<string, array{string}> */
    public static function invalidNames(): array
    {
        return [
            'chiffres' => ['Ahmed123'],
            'chiffres seuls' => ['12345'],
            'espaces uniquement' => ['     '],
            'html' => ['<script>x</script>'],
            'caractères spéciaux' => ['Ah@med!'],
        ];
    }

    public function testInvalidEmailIsRejected(): void
    {
        $this->assertRejected(['reservation_transfere_form[email]' => 'not-an-email'], 'e-mail');
    }

    public function testValidInternationalPhoneIsAccepted(): void
    {
        $client = static::createClient();
        $this->seed();
        try {
            $crawler = $client->request('GET', '/fr/transfers/' . $this->slug);
            $form = $crawler->filter('#fast-booking-form')->form();
            $form->setValues($this->validValues(['reservation_transfere_form[telephone]' => '+33 6 12 34 56 78']));
            $client->submit($form);
            self::assertResponseRedirects('/fr/transfers/' . $this->slug);
        } finally {
            $this->cleanup();
        }
    }

    #[DataProvider('invalidPhones')]
    public function testInvalidPhoneIsRejected(string $phone): void
    {
        $client = static::createClient();
        $this->seed();
        try {
            $entityManager = static::getContainer()->get(EntityManagerInterface::class);
            $crawler = $client->request('GET', '/fr/transfers/' . $this->slug);
            $form = $crawler->filter('#fast-booking-form')->form();
            $form->setValues($this->validValues(['reservation_transfere_form[telephone]' => $phone]));
            $client->submit($form);

            self::assertFalse($client->getResponse()->isRedirect(), sprintf('Le téléphone invalide « %s » doit être refusé.', $phone));
            self::assertCount(0, $entityManager->getRepository(ReservationTransfere::class)->findBy(['email' => $this->email]));
        } finally {
            $this->cleanup();
        }
    }

    /** @return array<string, array{string}> */
    public static function invalidPhones(): array
    {
        return [
            'trop court' => ['12345'],
            'lettres' => ['not-a-phone'],
            'symbole seul' => ['+++++'],
        ];
    }

    public function testPastDepartureDateIsRejected(): void
    {
        $this->assertRejected([
            'reservation_transfere_form[pickupDate]' => (new \DateTimeImmutable('-5 days'))->format('Y-m-d'),
        ], 'passé');
    }

    public function testReturnDateBeforeDepartureIsRejected(): void
    {
        $client = static::createClient();
        $this->seed();
        try {
            $entityManager = static::getContainer()->get(EntityManagerInterface::class);
            $crawler = $client->request('GET', '/fr/transfers/' . $this->slug);
            $form = $crawler->filter('#fast-booking-form')->form();
            $form->setValues($this->validValues([
                'reservation_transfere_form[transfereType]' => 'return',
                'reservation_transfere_form[pickupDate]' => (new \DateTimeImmutable('+10 days'))->format('Y-m-d'),
                'reservation_transfere_form[returnPickupLocation]' => 'Hammamet',
                'reservation_transfere_form[returnDropoffLocation]' => 'Aéroport Tunis',
                'reservation_transfere_form[returnPickupDate]' => (new \DateTimeImmutable('+5 days'))->format('Y-m-d'),
                'reservation_transfere_form[returnPickupTime]' => '18:00',
            ]));
            $client->submit($form);

            self::assertFalse($client->getResponse()->isRedirect(), 'Un retour antérieur au départ doit être refusé.');
            self::assertCount(0, $entityManager->getRepository(ReservationTransfere::class)->findBy(['email' => $this->email]));
        } finally {
            $this->cleanup();
        }
    }

    public function testRoundTripWithValidDatesIsAccepted(): void
    {
        $client = static::createClient();
        $this->seed();
        try {
            $crawler = $client->request('GET', '/fr/transfers/' . $this->slug);
            $form = $crawler->filter('#fast-booking-form')->form();
            $form->setValues($this->validValues([
                'reservation_transfere_form[transfereType]' => 'return',
                'reservation_transfere_form[pickupDate]' => (new \DateTimeImmutable('+10 days'))->format('Y-m-d'),
                'reservation_transfere_form[returnPickupLocation]' => 'Hammamet',
                'reservation_transfere_form[returnDropoffLocation]' => 'Aéroport Tunis',
                'reservation_transfere_form[returnPickupDate]' => (new \DateTimeImmutable('+12 days'))->format('Y-m-d'),
                'reservation_transfere_form[returnPickupTime]' => '18:00',
            ]));
            $client->submit($form);
            self::assertResponseRedirects('/fr/transfers/' . $this->slug);
        } finally {
            $this->cleanup();
        }
    }

    #[DataProvider('invalidPassengerCounts')]
    public function testInvalidPassengerCountIsRejected(string $count): void
    {
        $client = static::createClient();
        $this->seed();
        try {
            $entityManager = static::getContainer()->get(EntityManagerInterface::class);
            $crawler = $client->request('GET', '/fr/transfers/' . $this->slug);
            $form = $crawler->filter('#fast-booking-form')->form();
            $form->setValues($this->validValues(['reservation_transfere_form[nombrePassagers]' => $count]));
            $client->submit($form);

            self::assertFalse($client->getResponse()->isRedirect(), sprintf('Le nombre de passagers « %s » doit être refusé.', $count));
            self::assertCount(0, $entityManager->getRepository(ReservationTransfere::class)->findBy(['email' => $this->email]));
        } finally {
            $this->cleanup();
        }
    }

    /** @return array<string, array{string}> */
    public static function invalidPassengerCounts(): array
    {
        return [['0'], ['20'], ['-3']];
    }

    public function testUnknownTransferSlugReturnsNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/fr/transfers/this-slug-does-not-exist-99999');
        self::assertResponseStatusCodeSame(404);
    }

    public function testClientCannotInjectPriceStatusOrTransfer(): void
    {
        $client = static::createClient();
        $this->seed();
        try {
            $entityManager = static::getContainer()->get(EntityManagerInterface::class);
            $crawler = $client->request('GET', '/fr/transfers/' . $this->slug);
            $form = $crawler->filter('#fast-booking-form')->form();
            $form->setValues($this->validValues());
            $values = $form->getPhpValues();
            // Tentative de falsification via la requête HTTP : prixTotal / status / devise
            // ne sont PAS des champs du formulaire ; le serveur les recalcule/ignore.
            $values['reservation_transfere_form']['prixTotal'] = '999.00';
            $values['reservation_transfere_form']['status'] = 'confirmed';
            $values['reservation_transfere_form']['devise'] = 'USD';

            $client->request('POST', $form->getUri(), $values);

            $reservation = $entityManager->getRepository(ReservationTransfere::class)->findOneBy(['email' => $this->email]);
            // Que la soumission soit acceptée (valeurs falsifiées ignorées) ou refusée
            // (champs inconnus), aucune valeur falsifiée ne doit être persistée.
            if ($reservation !== null) {
                self::assertSame('50.00', number_format((float) $reservation->getPrixTotal(), 2, '.', ''), 'Le prix est recalculé côté serveur.');
                self::assertSame(ReservationStatus::PENDING, $reservation->getStatus(), 'Le statut n\'est pas défini par le client.');
                self::assertSame('TND', $reservation->getDevise(), 'La devise est fixée par le serveur.');
            }
            self::assertNull($entityManager->getRepository(ReservationTransfere::class)->findOneBy(['devise' => 'USD']), 'La devise falsifiée ne doit jamais être enregistrée.');
        } finally {
            $this->cleanup();
        }
    }

    private function assertRejected(array $overrides, string $expectedMessageFragment): void
    {
        $client = static::createClient();
        $this->seed();
        try {
            $entityManager = static::getContainer()->get(EntityManagerInterface::class);
            $crawler = $client->request('GET', '/fr/transfers/' . $this->slug);
            $form = $crawler->filter('#fast-booking-form')->form();
            $form->setValues($this->validValues($overrides));
            $client->submit($form);

            self::assertFalse($client->getResponse()->isRedirect(), 'La soumission invalide ne doit pas être acceptée.');
            self::assertCount(0, $entityManager->getRepository(ReservationTransfere::class)->findBy(['email' => $this->email]));
            self::assertStringContainsString($expectedMessageFragment, $client->getResponse()->getContent());
        } finally {
            $this->cleanup();
        }
    }
}
