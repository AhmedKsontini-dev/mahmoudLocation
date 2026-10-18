<?php

namespace App\Tests;

use App\Entity\ReservationTransfere;
use App\Entity\Transfere;
use App\Entity\User;
use App\Enum\ReservationStatus;
use App\Enum\ReservationTransfereType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminReservationManagementTest extends WebTestCase
{
    private function createAdminUser(EntityManagerInterface $entityManager): User
    {
        $suffix = bin2hex(random_bytes(4));
        $user = (new User())
            ->setEmail('admin-res-' . $suffix . '@example.test')
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
            ->setEmail('user-res-' . $suffix . '@example.test')
            ->setRoles(['ROLE_USER'])
            ->setPassword('not-used');

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    private function ensureTransfere(EntityManagerInterface $entityManager): Transfere
    {
        $repo = $entityManager->getRepository(Transfere::class);
        $transfere = $repo->findOneBy([]);
        if ($transfere !== null) {
            return $transfere;
        }

        $transfere = (new Transfere())
            ->setLieuDepart('Aéroport Tunis Carthage')
            ->setLieuArrivee('Hammamet')
            ->setPrixNormal('50.00')
            ->setPrixSaisonForte('65.00')
            ->setActif(true)
            ->setSlug('aeroport-tunis-hammamet-' . bin2hex(random_bytes(3)));

        $entityManager->persist($transfere);
        $entityManager->flush();

        return $transfere;
    }

    private function createTestReservation(EntityManagerInterface $entityManager, ?Transfere $transfere = null): ReservationTransfere
    {
        if ($transfere === null) {
            $transfere = $this->ensureTransfere($entityManager);
        }

        $suffix = bin2hex(random_bytes(3));
        $res = (new ReservationTransfere())
            ->setTransfere($transfere)
            ->setNom('Client' . $suffix)
            ->setPrenom('Test')
            ->setEmail('client-' . $suffix . '@example.test')
            ->setTelephone('+21699000111')
            ->setTransfereType(ReservationTransfereType::ONE_WAY)
            ->setPickupLocation('Aéroport Tunis Carthage')
            ->setDropoffLocation('Hôtel Paradis')
            ->setPickupDate(new \DateTimeImmutable('+2 days'))
            ->setPickupTime(new \DateTimeImmutable('14:30'))
            ->setNombrePassagers(2)
            ->setNombreBagages(2)
            ->setPrixTotal('50.00')
            ->setDevise('EUR')
            ->setStatus(ReservationStatus::PENDING);

        $entityManager->persist($res);
        $entityManager->flush();

        return $res;
    }

    public function testAnonymousAccessIsRedirectedToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/reservations');

        self::assertResponseRedirects('/login');
    }

    public function testStandardUserCannotAccessAdminReservations(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $user = $this->createStandardUser($entityManager);

        $client->loginUser($user);
        $client->request('GET', '/admin/reservations');

        self::assertResponseStatusCodeSame(403);
    }

    public function testAdminCanViewReservationsIndexWithAllFeatures(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $admin = $this->createAdminUser($entityManager);
        $reservation = $this->createTestReservation($entityManager);

        $client->loginUser($admin);
        $crawler = $client->request('GET', '/admin/reservations');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1.page-title', 'Gestion des réservations');

        // Vérification des cartes statistiques
        self::assertSelectorExists('.reservation-stats-grid');
        self::assertSelectorExists('.reservation-stat-card');

        // Vérification du panneau de filtres
        self::assertSelectorExists('#reservationFilterForm');
        self::assertSelectorExists('#reservationSearchInput');
        self::assertSelectorExists('select[name="status"]');
        self::assertSelectorExists('select[name="type"]');
        self::assertSelectorExists('#btnFilterSubmit');

        // Vérification du tableau
        self::assertSelectorExists('.reservation-table');
        self::assertSelectorExists('#reservation-row-' . $reservation->getId());

        // Vérification du badge de statut cliquable et ses 3 choix
        self::assertSelectorExists('#status-dropdown-' . $reservation->getId());
        $statusMenu = $crawler->filter('#status-dropdown-' . $reservation->getId() . ' .status-dropdown-menu');
        self::assertCount(1, $statusMenu);
        self::assertStringContainsString('Confirmer', $statusMenu->text());
        self::assertStringContainsString('En attente', $statusMenu->text());
        self::assertStringContainsString('Annuler', $statusMenu->text());

        // Vérification du menu Actions (Show, Voucher, Supprimer) dans la dernière colonne
        $actionsMenu = $crawler->filter('#reservation-row-' . $reservation->getId() . ' td:last-child .dropdown-menu');
        self::assertCount(1, $actionsMenu);
        self::assertStringContainsString('Show', $actionsMenu->text());
        self::assertStringContainsString('Voucher', $actionsMenu->text());
        self::assertStringNotContainsString('Edit', $actionsMenu->text());
        self::assertStringContainsString('Supprimer', $actionsMenu->text());

        // Vérification de la pagination
        self::assertSelectorExists('.reservation-pagination-footer');
    }

    public function testFiltersWorkCorrectly(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $admin = $this->createAdminUser($entityManager);
        $reservation = $this->createTestReservation($entityManager);

        $client->loginUser($admin);

        // Recherche par email
        $client->request('GET', '/admin/reservations', ['search' => $reservation->getEmail()]);
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('#reservation-row-' . $reservation->getId());

        // Recherche avec un mot-clé inexistant
        $client->request('GET', '/admin/reservations', ['search' => 'introuvable_xyz_9999']);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.empty-state-title', 'Aucune réservation trouvée');

        // Filtre par statut 'pending'
        $client->request('GET', '/admin/reservations', ['status' => 'pending']);
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('#reservation-row-' . $reservation->getId());

        // Filtre par statut 'cancelled' (ne doit pas contenir notre réservation pending)
        $crawler = $client->request('GET', '/admin/reservations', ['status' => 'cancelled']);
        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('#reservation-row-' . $reservation->getId()));
    }

    public function testUpdateStatusViaAjaxWithCsrf(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $admin = $this->createAdminUser($entityManager);
        $reservation = $this->createTestReservation($entityManager);
        $resId = $reservation->getId();

        $client->loginUser($admin);

        // 1. Appel avec token CSRF invalide -> 403
        $client->request('POST', '/admin/reservations/' . $resId . '/status', [
            'status' => 'confirmed',
            '_token' => 'invalid_csrf_token',
        ], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
            'HTTP_ACCEPT' => 'application/json',
        ]);
        self::assertResponseStatusCodeSame(403);

        // 2. Récupération d'un token CSRF valide depuis la page
        $crawler = $client->request('GET', '/admin/reservations');
        // On extrait le token du onclick de l'option de statut
        $btnOnclick = $crawler->filter('#status-dropdown-' . $resId . ' button[data-status="confirmed"]')->attr('onclick');
        // onclick="updateReservationStatus(ID, 'confirmed', 'TOKEN', this)"
        preg_match("/updateReservationStatus\(\s*\d+\s*,\s*'confirmed'\s*,\s*'([^']+)'/", $btnOnclick, $matches);
        $csrfToken = $matches[1] ?? '';
        self::assertNotEmpty($csrfToken);

        // 3. Mise à jour vers CONFIRMED via AJAX
        $client->request('POST', '/admin/reservations/' . $resId . '/status', [
            'status' => 'confirmed',
            '_token' => $csrfToken,
        ], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
            'HTTP_ACCEPT' => 'application/json',
        ]);

        self::assertResponseIsSuccessful();
        $responseData = json_decode($client->getResponse()->getContent(), true);
        self::assertTrue($responseData['success']);
        self::assertSame('confirmed', $responseData['status']);
        self::assertSame('Confirmée', $responseData['label']);
        self::assertSame('badge-confirmed', $responseData['badgeClass']);

        // Vérification en base de données MySQL
        $freshReservation = $entityManager->getRepository(ReservationTransfere::class)->find($resId);
        self::assertSame(ReservationStatus::CONFIRMED, $freshReservation->getStatus());

        // 4. Mise à jour vers CANCELLED via AJAX
        $client->request('POST', '/admin/reservations/' . $resId . '/status', [
            'status' => 'cancelled',
            '_token' => $csrfToken,
        ], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
            'HTTP_ACCEPT' => 'application/json',
        ]);

        self::assertResponseIsSuccessful();
        $entityManager->clear();
        $freshReservation = $entityManager->getRepository(ReservationTransfere::class)->find($resId);
        self::assertSame(ReservationStatus::CANCELLED, $freshReservation->getStatus());
    }

    public function testStatusRemainsSavedWhenClientEmailIsInvalid(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $admin = $this->createAdminUser($entityManager);
        $reservation = $this->createTestReservation($entityManager);
        $reservation->setEmail('invalid-address');
        $entityManager->flush();

        $client->loginUser($admin);
        $crawler = $client->request('GET', '/admin/reservations');
        $button = $crawler->filter('#status-dropdown-' . $reservation->getId() . ' button[data-status="confirmed"]')->attr('onclick');
        preg_match("/updateReservationStatus\(\s*\d+\s*,\s*'confirmed'\s*,\s*'([^']+)'/", $button, $matches);

        $client->request('POST', '/admin/reservations/' . $reservation->getId() . '/status', [
            'status' => 'confirmed',
            '_token' => $matches[1] ?? '',
        ], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
            'HTTP_ACCEPT' => 'application/json',
        ]);

        self::assertResponseIsSuccessful();
        $responseData = json_decode($client->getResponse()->getContent(), true);
        self::assertTrue($responseData['success']);
        self::assertFalse($responseData['emailSent']);
        self::assertStringContainsString('absente ou invalide', $responseData['message']);

        $entityManager->clear();
        $savedReservation = $entityManager->getRepository(ReservationTransfere::class)->find($reservation->getId());
        self::assertSame(ReservationStatus::CONFIRMED, $savedReservation->getStatus());
    }

    public function testShowAndEditPages(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $admin = $this->createAdminUser($entityManager);
        $reservation = $this->createTestReservation($entityManager);
        $resId = $reservation->getId();

        $client->loginUser($admin);

        // Show page
        $client->request('GET', '/admin/reservations/' . $resId . '/show');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.page-title', 'Détails de la réservation');
        self::assertSelectorTextContains('.detail-grid', $reservation->getEmail());

        // Edit page (GET)
        $client->request('GET', '/admin/reservations/' . $resId . '/edit');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('select#status');

        // Edit page (POST change status)
        $crawler = $client->request('GET', '/admin/reservations/' . $resId . '/edit');
        $csrfToken = $crawler->filter('input[name="_token"]')->attr('value');
        $client->request('POST', '/admin/reservations/' . $resId . '/edit', [
            'status' => 'confirmed',
            '_token' => $csrfToken,
        ]);
        self::assertResponseRedirects('/admin/reservations');

        $entityManager->clear();
        $freshReservation = $entityManager->getRepository(ReservationTransfere::class)->find($resId);
        self::assertSame(ReservationStatus::CONFIRMED, $freshReservation->getStatus());
    }

    public function testDeleteRequiresValidCsrf(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $admin = $this->createAdminUser($entityManager);
        $reservation = $this->createTestReservation($entityManager);
        $id = $reservation->getId();

        $client->loginUser($admin);

        // 1. Suppression avec mauvais token CSRF
        $client->request('POST', '/admin/reservations/' . $id . '/delete', [
            '_token' => 'mauvais_token',
        ]);
        self::assertResponseRedirects('/admin/reservations');

        // La réservation doit TOUJOURS exister
        $stillExists = $entityManager->getRepository(ReservationTransfere::class)->find($id);
        self::assertNotNull($stillExists);

        // 2. Récupérer le token CSRF depuis le formulaire de suppression rendu
        $crawler = $client->request('GET', '/admin/reservations');
        $validToken = $crawler->filter('#reservation-row-' . $id . ' form input[name="_token"]')->attr('value');
        self::assertNotEmpty($validToken);

        $client->request('POST', '/admin/reservations/' . $id . '/delete', [
            '_token' => $validToken,
        ]);
        self::assertResponseRedirects('/admin/reservations');

        // La réservation doit être supprimée
        $entityManager->clear();
        $deleted = $entityManager->getRepository(ReservationTransfere::class)->find($id);
        self::assertNull($deleted);
    }
}
