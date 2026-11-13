<?php

namespace App\Tests;

use App\Entity\ReservationTransfere;
use App\Entity\Transfere;
use App\Entity\User;
use App\Enum\ReservationStatus;
use App\Enum\ReservationTransfereType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FrontendProfileTest extends WebTestCase
{
    public function testProfileIsPrivateFiltersBookingsAndSavesPersonalDetails(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $suffix = bin2hex(random_bytes(5));
        $userEmail = 'profile-'.$suffix.'@example.test';
        $otherEmail = 'other-profile-'.$suffix.'@example.test';
        $bookingEmails = [
            'confirmed-'.$suffix.'@example.test',
            'pending-'.$suffix.'@example.test',
            'private-'.$suffix.'@example.test',
            $userEmail,
        ];
        $slugs = [
            'confirmed-profile-'.$suffix,
            'pending-profile-'.$suffix,
            'private-profile-'.$suffix,
            'unlinked-profile-'.$suffix,
        ];

        $user = (new User())
            ->setEmail($userEmail)
            ->setPassword('test-password');
        $otherUser = (new User())
            ->setEmail($otherEmail)
            ->setPassword('test-password');
        $entityManager->persist($user);
        $entityManager->persist($otherUser);

        $this->createReservation(
            $entityManager,
            $user,
            $bookingEmails[0],
            $slugs[0],
            ReservationStatus::CONFIRMED,
            'My confirmed route',
        );
        $this->createReservation(
            $entityManager,
            $user,
            $bookingEmails[1],
            $slugs[1],
            ReservationStatus::PENDING,
            'My pending route',
        );
        $this->createReservation(
            $entityManager,
            $otherUser,
            $bookingEmails[2],
            $slugs[2],
            ReservationStatus::CONFIRMED,
            'Private route belonging to another user',
        );
        $this->createReservation(
            $entityManager,
            null,
            $bookingEmails[3],
            $slugs[3],
            ReservationStatus::CONFIRMED,
            'Unlinked guest booking',
        );
        $entityManager->flush();

        try {
            $client->loginUser($user);

            $client->request('GET', '/en/profile?filter=confirmed');
            self::assertResponseIsSuccessful();
            self::assertSelectorCount(1, '.profile-reservation-card');
            self::assertSelectorTextContains('.profile-reservation-card', 'My confirmed route');
            self::assertSelectorTextContains('.profile-reservation-card', 'Confirmed');
            self::assertStringNotContainsString('Private route belonging to another user', (string) $client->getResponse()->getContent());
            self::assertStringNotContainsString('Unlinked guest booking', (string) $client->getResponse()->getContent());

            $client->request('GET', '/profile?filter=pending');
            self::assertResponseIsSuccessful();
            self::assertSelectorCount(1, '.profile-reservation-card');
            self::assertSelectorTextContains('.profile-reservation-card', 'My pending route');

            $client->request('GET', '/profile?filter=completed');
            self::assertResponseIsSuccessful();
            self::assertSelectorExists('.profile-empty-state');

            $crawler = $client->request('GET', '/profile');
            $form = $crawler->selectButton('Enregistrer mes informations')->form();
            $form->setValues([
                'profile_form[firstName]' => 'Sophie',
                'profile_form[lastName]' => 'Voyageuse',
                'profile_form[phone]' => '+216 12 345 678',
            ]);
            $client->submit($form);

            self::assertResponseRedirects('/fr/profile?filter=all');
            $entityManager->clear();
            $savedUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $userEmail]);
            self::assertNotNull($savedUser);
            self::assertSame('Sophie', $savedUser->getFirstName());
            self::assertSame('Voyageuse', $savedUser->getLastName());
            self::assertSame('+216 12 345 678', $savedUser->getPhone());
        } finally {
            $entityManager->clear();
            $entityManager->createQuery('DELETE FROM App\Entity\ReservationTransfere r WHERE r.email IN (:emails)')
                ->setParameter('emails', $bookingEmails)
                ->execute();

            foreach ($slugs as $slug) {
                $transfer = $entityManager->getRepository(Transfere::class)->findOneBy(['slug' => $slug]);
                if ($transfer !== null) {
                    $entityManager->remove($transfer);
                }
            }

            foreach ([$userEmail, $otherEmail] as $email) {
                $storedUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
                if ($storedUser !== null) {
                    $entityManager->remove($storedUser);
                }
            }

            $entityManager->flush();
        }
    }

    public function testAnonymousVisitorsCannotAccessTheProfile(): void
    {
        $client = static::createClient();
        $client->request('GET', '/profile');

        self::assertResponseRedirects('/');
    }

    private function createReservation(
        EntityManagerInterface $entityManager,
        ?User $user,
        string $email,
        string $slug,
        ReservationStatus $status,
        string $dropoff,
    ): ReservationTransfere {
        $transfer = (new Transfere())
            ->setLieuDepart('Tunis airport')
            ->setLieuArrivee($dropoff)
            ->setPrixNormal('50.00')
            ->setPrixSaisonForte('65.00')
            ->setSlug($slug)
            ->setActif(true);
        $reservation = (new ReservationTransfere())
            ->setUser($user)
            ->setTransfere($transfer)
            ->setNom('Profile test')
            ->setPrenom('User')
            ->setEmail($email)
            ->setTelephone('+21600000000')
            ->setTransfereType(ReservationTransfereType::ONE_WAY)
            ->setPickupLocation('Tunis airport')
            ->setDropoffLocation($dropoff)
            ->setPickupDate(new \DateTimeImmutable('+3 days'))
            ->setPickupTime(new \DateTimeImmutable('10:30'))
            ->setNombrePassagers(2)
            ->setPrixTotal('50.00')
            ->setDevise('TND')
            ->setStatus($status);

        $entityManager->persist($transfer);
        $entityManager->persist($reservation);

        return $reservation;
    }
}
