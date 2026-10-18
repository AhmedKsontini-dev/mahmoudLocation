<?php

namespace App\Tests;

use App\Entity\CurrencyRate;
use App\Entity\ReservationTransfere;
use App\Entity\Transfere;
use App\Enum\ReservationStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FrontendTransferBookingTest extends WebTestCase
{
    public function testBookingIsPersistedOnceAndShowsTranslatedSuccess(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $suffix = bin2hex(random_bytes(5));
        $slug = 'translator-booking-' . $suffix;
        $email = 'booking-' . $suffix . '@example.test';

        $currency = (new CurrencyRate())
            ->setCode('TND')
            ->setSymbol('DT')
            ->setRate('1.000000');
        $transfer = (new Transfere())
            ->setLieuDepart('Aéroport Tunis')
            ->setLieuArrivee('Hammamet')
            ->setPrixNormal('50.00')
            ->setPrixSaisonForte('65.00')
            ->setSlug($slug)
            ->setActif(true);

        $entityManager->persist($currency);
        $entityManager->persist($transfer);
        $entityManager->flush();

        try {
            $crawler = $client->request('GET', '/fr/transfers/' . $slug);
            self::assertResponseIsSuccessful();
            $form = $crawler->filter('#fast-booking-form')->form();
            $form->setValues([
                'reservation_transfere_form[transfereType]' => 'one_way',
                'reservation_transfere_form[pickupLocation]' => 'Aéroport Tunis',
                'reservation_transfere_form[dropoffLocation]' => 'Hammamet',
                'reservation_transfere_form[pickupDate]' => (new \DateTimeImmutable('+3 days'))->format('Y-m-d'),
                'reservation_transfere_form[pickupTime]' => '10:30',
                'reservation_transfere_form[nom]' => 'Client de test',
                'reservation_transfere_form[email]' => $email,
                'reservation_transfere_form[telephone]' => '+21600000000',
                'reservation_transfere_form[nombrePassagers]' => '2',
                'reservation_transfere_form[nombreBagages]' => '1',
            ]);

            $client->submit($form);

            self::assertResponseRedirects('/fr/transfers/' . $slug);
            $client->followRedirect();
            self::assertResponseIsSuccessful();
            self::assertSelectorTextContains('.text-tertiary', 'Votre demande de réservation a été enregistrée.');

            $reservations = $entityManager->getRepository(ReservationTransfere::class)->findBy(['email' => $email]);
            self::assertCount(1, $reservations, 'A single booking should be created for the submitted request.');
            self::assertSame(ReservationStatus::PENDING, $reservations[0]->getStatus());
            self::assertSame($transfer->getId(), $reservations[0]->getTransfere()->getId());
            self::assertSame($email, $reservations[0]->getEmail());
        } finally {
            $entityManager->createQuery('DELETE FROM App\Entity\ReservationTransfere r WHERE r.email = :email')
                ->setParameter('email', $email)
                ->execute();
            $managedTransfer = $entityManager->getRepository(Transfere::class)->findOneBy(['slug' => $slug]);
            if ($managedTransfer !== null) {
                $entityManager->remove($managedTransfer);
            }
            $managedCurrency = $entityManager->getRepository(CurrencyRate::class)->find($currency->getId());
            if ($managedCurrency !== null) {
                $entityManager->remove($managedCurrency);
            }
            $entityManager->flush();
        }
    }
}
