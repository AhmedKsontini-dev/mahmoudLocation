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

class AdminReservationExcursionManagementTest extends WebTestCase
{
    private function createAdminUser(EntityManagerInterface $entityManager): User
    {
        $suffix = bin2hex(random_bytes(4));
        $user = (new User())
            ->setEmail('admin-exc-' . $suffix . '@example.test')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword('not-used');

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    private function createStandardUser(EntityManagerInterface $entityManager): User
    {
        $suffix = bin2hex(random_bytes(4));
        $user = (new User())
            ->setEmail('user-exc-' . $suffix . '@example.test')
            ->setRoles(['ROLE_USER'])
            ->setPassword('not-used');

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    private function ensureExcursion(EntityManagerInterface $entityManager): Excursion
    {
        $repo = $entityManager->getRepository(Excursion::class);
        $excursion = $repo->findOneBy([]);
        if ($excursion !== null) {
            return $excursion;
        }

        $category = $entityManager->getRepository(Category::class)->findOneBy([]);
        if ($category === null) {
            $category = (new Category())->setSlug('cat-' . bin2hex(random_bytes(3)));
            $entityManager->persist($category);
        }

        $destination = $entityManager->getRepository(Destination::class)->findOneBy([]);
        if ($destination === null) {
            $destination = (new Destination())->setSlug('dest-' . bin2hex(random_bytes(3)));
            $entityManager->persist($destination);
        }

        $excursion = (new Excursion())
            ->setCategory($category)
            ->setDestination($destination)
            ->setPriceAdult('75.00')
            ->setSlug('excursion-test-' . bin2hex(random_bytes(3)))
            ->setStatus(Excursion::STATUS_ACTIVE);

        $trans = (new ExcursionTranslation())
            ->setExcursion($excursion)
            ->setLocale('fr')
            ->setTitle('Excursion Découverte Test');
        $excursion->addTranslation($trans);

        $entityManager->persist($excursion);
        $entityManager->flush();

        return $excursion;
    }

    private function createTestReservationExcursion(EntityManagerInterface $entityManager): ReservationExcursion
    {
        $excursion = $this->ensureExcursion($entityManager);
        $suffix = bin2hex(random_bytes(3));

        $res = (new ReservationExcursion())
            ->setExcursion($excursion)
            ->setNom('Client' . $suffix)
            ->setPrenom('Test')
            ->setEmail('client-' . $suffix . '@example.test')
            ->setWhatsapp('+216 20 123 456')
            ->setNombreAdultes(2)
            ->setNombreEnfants(1)
            ->setDateExcursion(new \DateTime('+3 days'))
            ->setHeureDepart(new \DateTime('08:30:00'))
            ->setLieuPriseEnCharge('Hôtel Mövenpick Resort')
            ->setStatut(ReservationExcursion::STATUS_PENDING);

        $entityManager->persist($res);
        $entityManager->flush();

        return $res;
    }

    public function testAnonymousAccessIsRedirectedToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/reservations-excursions');

        self::assertResponseRedirects('/login');
    }

    public function testStandardUserCannotAccessAdminReservationExcursions(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();
        $user = $this->createStandardUser($em);

        $client->loginUser($user);
        $client->request('GET', '/admin/reservations-excursions');

        self::assertResponseStatusCodeSame(403);
    }

    public function testAdminCanViewReservationExcursionsList(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();
        $admin = $this->createAdminUser($em);
        $reservation = $this->createTestReservationExcursion($em);

        $client->loginUser($admin);
        $crawler = $client->request('GET', '/admin/reservations-excursions');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1.page-title', 'Gestion des réservations d’excursions');
        self::assertSelectorExists('.reservation-stats-grid');
        self::assertSelectorExists('#filterForm');
        self::assertSelectorExists('table.reservation-table');
        self::assertStringContainsString($reservation->getNom(), $crawler->html());
        self::assertSame(7, $crawler->filter('#reservation-row-' . $reservation->getId() . ' > td')->count());
        self::assertSame(2, $crawler->filter('#reservation-row-' . $reservation->getId() . ' .status-dropdown-menu .status-action-item')->count());
        self::assertSame(3, $crawler->filter('#reservation-row-' . $reservation->getId() . ' > td:last-child .dropdown-menu .dropdown-item')->count());

        $rowText = $crawler->filter('#reservation-row-' . $reservation->getId())->text();
        self::assertStringNotContainsString($reservation->getEmail(), $rowText);
        self::assertStringNotContainsString($reservation->getWhatsapp(), $rowText);
        self::assertStringNotContainsString('Hôtel Mövenpick Resort', $rowText);
    }

    public function testAdminCanViewSingleReservationExcursion(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();
        $admin = $this->createAdminUser($em);
        $reservation = $this->createTestReservationExcursion($em);

        $client->loginUser($admin);
        $crawler = $client->request('GET', '/admin/reservations-excursions/' . $reservation->getId());

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1.page-title', 'Réservation d’excursion #' . $reservation->getId());
        self::assertStringContainsString('Informations sur l\'excursion', $crawler->html());
        self::assertStringContainsString('Informations du client', $crawler->html());
        self::assertStringContainsString($reservation->getEmail(), $crawler->html());
        self::assertStringContainsString($reservation->getWhatsapp(), $crawler->html());
        self::assertStringContainsString($reservation->getLieuPriseEnCharge(), $crawler->html());
    }

    public function testAdminCanUpdateStatusViaAjaxWithCsrf(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();
        $admin = $this->createAdminUser($em);
        $reservation = $this->createTestReservationExcursion($em);
        $resId = $reservation->getId();

        $client->loginUser($admin);

        // Récupération de la page pour extraire le token CSRF du bouton
        $crawler = $client->request('GET', '/admin/reservations-excursions', ['search' => $reservation->getEmail()]);
        self::assertResponseIsSuccessful();

        $btnOnclick = $crawler->filter('#status-dropdown-' . $resId . ' button[data-status="confirmee"]')->attr('onclick');
        preg_match("/updateReservationStatus\(\s*\d+\s*,\s*'confirmee'\s*,\s*'([^']+)'/", $btnOnclick, $matches);
        $csrfToken = $matches[1] ?? '';
        self::assertNotEmpty($csrfToken);

        $client->request(
            'POST',
            '/admin/reservations-excursions/' . $resId . '/status',
            [
                'status' => 'confirmee',
                '_token' => $csrfToken,
            ],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'HTTP_ACCEPT' => 'application/json',
            ]
        );

        self::assertResponseIsSuccessful();
        $responseContent = json_decode((string) $client->getResponse()->getContent(), true);

        self::assertTrue($responseContent['success']);
        self::assertSame('confirmee', $responseContent['status']);
        self::assertSame('Confirmée', $responseContent['label']);

        $em->clear();
        $freshReservation = $em->getRepository(ReservationExcursion::class)->find($resId);
        self::assertNotNull($freshReservation);
        self::assertSame('confirmee', $freshReservation->getStatut());
    }

    public function testAdminCanEditReservationExcursion(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();
        $admin = $this->createAdminUser($em);
        $reservation = $this->createTestReservationExcursion($em);
        $resId = $reservation->getId();

        $client->loginUser($admin);
        $crawler = $client->request('GET', '/admin/reservations-excursions/' . $resId . '/edit');
        self::assertResponseIsSuccessful();

        $buttonCrawler = $crawler->selectButton('Enregistrer les modifications');
        $form = $buttonCrawler->form([
            'reservation_excursion_admin[nom]' => 'NouveauNom',
            'reservation_excursion_admin[prenom]' => 'NouveauPrenom',
            'reservation_excursion_admin[lieuPriseEnCharge]' => 'Nouveau lieu de pickup',
            'reservation_excursion_admin[statut]' => 'confirmee',
        ]);

        $client->submit($form);
        self::assertResponseRedirects('/admin/reservations-excursions/' . $resId);

        $em->clear();
        $fresh = $em->getRepository(ReservationExcursion::class)->find($resId);
        self::assertNotNull($fresh);
        self::assertSame('NouveauNom', $fresh->getNom());
        self::assertSame('NouveauPrenom', $fresh->getPrenom());
        self::assertSame('Nouveau lieu de pickup', $fresh->getLieuPriseEnCharge());
        self::assertSame('confirmee', $fresh->getStatut());
    }

    public function testAdminCanDeleteReservationExcursionWithCsrf(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();
        $admin = $this->createAdminUser($em);
        $reservation = $this->createTestReservationExcursion($em);
        $resId = $reservation->getId();

        $client->loginUser($admin);

        $crawler = $client->request('GET', '/admin/reservations-excursions', ['search' => $reservation->getEmail()]);
        self::assertResponseIsSuccessful();

        $deleteBtn = $crawler->filter('#reservation-row-' . $resId . ' .btn-delete-reservation')->attr('onclick');
        preg_match("/,\s*'([^']+)'\s*\)\s*$/", $deleteBtn, $matches);
        $csrfToken = $matches[1] ?? '';
        self::assertNotEmpty($csrfToken);

        $client->request('POST', '/admin/reservations-excursions/' . $resId . '/delete', [
            '_token' => $csrfToken,
        ]);

        self::assertResponseRedirects('/admin/reservations-excursions');

        $em->clear();
        $deleted = $em->getRepository(ReservationExcursion::class)->find($resId);
        self::assertNull($deleted);
    }

    public function testAdminCannotDeleteReservationExcursionWithGet(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();
        $admin = $this->createAdminUser($em);
        $reservation = $this->createTestReservationExcursion($em);

        $client->loginUser($admin);
        $client->request('GET', '/admin/reservations-excursions/' . $reservation->getId() . '/delete');

        self::assertResponseStatusCodeSame(405);
        self::assertNotNull($em->getRepository(ReservationExcursion::class)->find($reservation->getId()));
    }
}
