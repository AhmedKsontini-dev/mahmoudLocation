<?php

namespace App\Tests;

use App\Entity\Category;
use App\Entity\Destination;
use App\Entity\Excursion;
use App\Entity\ExcursionTranslation;
use App\Entity\ReservationExcursion;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class AdminExcursionDeletionTest extends WebTestCase
{
    private function createAdminUser(EntityManagerInterface $em): User
    {
        $user = (new User())
            ->setEmail('admin-del-' . bin2hex(random_bytes(4)) . '@example.test')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword('not-used');

        $em->persist($user);
        $em->flush();

        return $user;
    }

    private function createExcursion(EntityManagerInterface $em): Excursion
    {
        $suffix = bin2hex(random_bytes(3));

        $category = $em->getRepository(Category::class)->findOneBy([]);
        if ($category === null) {
            $category = (new Category())->setSlug('cat-' . $suffix);
            $em->persist($category);
        }

        $destination = $em->getRepository(Destination::class)->findOneBy([]);
        if ($destination === null) {
            $destination = (new Destination())->setSlug('dest-' . $suffix);
            $em->persist($destination);
        }

        $excursion = (new Excursion())
            ->setCategory($category)
            ->setDestination($destination)
            ->setPriceAdult('89.00')
            ->setSlug('excursion-del-' . $suffix)
            ->setStatus(Excursion::STATUS_ACTIVE);

        $excursion->addTranslation(
            (new ExcursionTranslation())
                ->setExcursion($excursion)
                ->setLocale('fr')
                ->setTitle('Excursion à supprimer ' . $suffix)
        );

        $em->persist($excursion);
        $em->flush();

        return $excursion;
    }

    private function createReservationFor(EntityManagerInterface $em, Excursion $excursion): ReservationExcursion
    {
        $suffix = bin2hex(random_bytes(3));

        $reservation = (new ReservationExcursion())
            ->setExcursion($excursion)
            ->setNom('Client' . $suffix)
            ->setPrenom('Test')
            ->setEmail('client-' . $suffix . '@example.test')
            ->setWhatsapp('+216 20 123 456')
            ->setNombreAdultes(2)
            ->setNombreEnfants(0)
            ->setDateExcursion(new \DateTime('+3 days'))
            ->setHeureDepart(new \DateTime('08:30:00'))
            ->setLieuPriseEnCharge('Hôtel Test')
            ->setStatut(ReservationExcursion::STATUS_PENDING);

        $em->persist($reservation);
        $em->flush();

        return $reservation;
    }

    private function submitDeleteForm(Crawler $crawler, int $excursionId): void
    {
        $client = static::getClient();
        $form = $crawler->filter('form[action$="/admin/excursions/' . $excursionId . '/delete"]')->first()->form();
        $client->submit($form);
    }

    public function testDeletionBlockedWhenExcursionHasReservations(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();
        $admin = $this->createAdminUser($em);
        $excursion = $this->createExcursion($em);
        $reservation = $this->createReservationFor($em, $excursion);
        $excursionId = $excursion->getId();

        $client->loginUser($admin);

        $crawler = $client->request('GET', '/admin/excursions');
        $this->submitDeleteForm($crawler, $excursionId);

        self::assertResponseRedirects('/admin/excursions');
        $client->followRedirect();

        self::assertResponseIsSuccessful();
        $body = $client->getResponse()->getContent();
        self::assertStringContainsString('Suppression impossible', $body);
        self::assertStringContainsString('réservation(s) associée', $body);
        self::assertStringContainsString('veuillez désactiver cette excursion', $body);

        // Aucune erreur SQL brute ne doit être exposée
        self::assertStringNotContainsString('SQLSTATE', $body);
        self::assertStringNotContainsString('Integrity constraint violation', $body);

        // L'excursion et la réservation restent intactes
        $em->clear();
        self::assertNotNull($em->getRepository(Excursion::class)->find($excursionId));
        self::assertNotNull($em->getRepository(ReservationExcursion::class)->find($reservation->getId()));

        $this->cleanup($em, $excursionId, $reservation->getId());
    }

    public function testDeletionSucceedsWhenExcursionHasNoReservations(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();
        $admin = $this->createAdminUser($em);
        $excursion = $this->createExcursion($em);
        $excursionId = $excursion->getId();

        $client->loginUser($admin);

        $crawler = $client->request('GET', '/admin/excursions');
        $this->submitDeleteForm($crawler, $excursionId);

        self::assertResponseRedirects('/admin/excursions');
        $client->followRedirect();

        self::assertResponseIsSuccessful();
        $body = $client->getResponse()->getContent();
        self::assertStringContainsString('supprimée avec succès', $body);
        self::assertStringNotContainsString('SQLSTATE', $body);

        $em->clear();
        self::assertNull($em->getRepository(Excursion::class)->find($excursionId));
    }

    private function cleanup(EntityManagerInterface $em, int $excursionId, int $reservationId): void
    {
        $reservation = $em->getRepository(ReservationExcursion::class)->find($reservationId);
        if ($reservation !== null) {
            $em->remove($reservation);
        }
        $excursion = $em->getRepository(Excursion::class)->find($excursionId);
        if ($excursion !== null) {
            $em->remove($excursion);
        }
        $em->flush();
    }
}
