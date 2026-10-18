<?php

namespace App\Tests;

use App\Entity\ReservationTransfere;
use App\Entity\Transfere;
use App\Entity\User;
use App\Enum\ReservationStatus;
use App\Enum\ReservationTransfereType;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminReservationVoucherTest extends WebTestCase
{
    private function createAdminUser(EntityManagerInterface $entityManager): User
    {
        $suffix = bin2hex(random_bytes(4));
        $user = (new User())
            ->setEmail('admin-voucher-' . $suffix . '@example.test')
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
            ->setEmail('user-voucher-' . $suffix . '@example.test')
            ->setRoles(['ROLE_USER'])
            ->setPassword('not-used');

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    private function getOrCreateTransfere(EntityManagerInterface $entityManager): Transfere
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

    public function testVoucherRequiresAdminRole(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $transfere = $this->getOrCreateTransfere($em);

        $res = (new ReservationTransfere())
            ->setTransfere($transfere)
            ->setNom('Dupont')
            ->setPrenom('Jean')
            ->setEmail('jean.dupont@test.com')
            ->setTelephone('+21699887766')
            ->setPickupLocation('Aéroport Tunis')
            ->setDropoffLocation('Hôtel Hammamet')
            ->setPickupDate(new DateTimeImmutable('2026-11-15'))
            ->setPickupTime(new DateTimeImmutable('14:30:00'))
            ->setNombrePassagers(2)
            ->setNombreBagages(2)
            ->setPrixTotal('65.00')
            ->setDevise('EUR');
        $em->persist($res);
        $em->flush();

        // 1. Visiteur non connecté -> redirection login
        $client->request('GET', '/admin/reservations/' . $res->getId() . '/voucher');
        $this->assertResponseRedirects('/login');

        // 2. Utilisateur standard -> 403 Forbidden
        $user = $this->createStandardUser($em);
        $client->loginUser($user);
        $client->request('GET', '/admin/reservations/' . $res->getId() . '/voucher');
        $this->assertResponseStatusCodeSame(403);

        // 3. Admin -> 200 OK
        $admin = $this->createAdminUser($em);
        $client->loginUser($admin);
        $client->request('GET', '/admin/reservations/' . $res->getId() . '/voucher');
        $this->assertResponseIsSuccessful();
    }

    public function testVoucherReturns404ForNonExistentReservation(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $admin = $this->createAdminUser($em);
        $client->loginUser($admin);

        $client->request('GET', '/admin/reservations/99999999/voucher');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testVoucherDisplaysRealDataForOneWayReservation(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $admin = $this->createAdminUser($em);
        $client->loginUser($admin);
        $transfere = $this->getOrCreateTransfere($em);

        $res = (new ReservationTransfere())
            ->setTransfere($transfere)
            ->setNom('Ben Ali')
            ->setPrenom('Mohamed')
            ->setEmail('mohamed.benali@test.com')
            ->setTelephone('+21612345678')
            ->setTransfereType(ReservationTransfereType::ONE_WAY)
            ->setPickupLocation('Terminal 1 - Tunis Carthage')
            ->setDropoffLocation('Hôtel The Residence Gammarth')
            ->setPickupDate(new DateTimeImmutable('2026-12-01'))
            ->setPickupTime(new DateTimeImmutable('10:45:00'))
            ->setNombrePassagers(3)
            ->setNombreBagages(4)
            ->setNumeroVol('TU721')
            ->setCompagnieVol('Tunisair')
            ->setMessage('Siège bébé souhaité svp')
            ->setPrixTotal('85.00')
            ->setDevise('TND')
            ->setStatus(ReservationStatus::CONFIRMED);
        $em->persist($res);
        $em->flush();

        $crawler = $client->request('GET', '/admin/reservations/' . $res->getId() . '/voucher');
        $this->assertResponseIsSuccessful();

        $content = (string) $client->getResponse()->getContent();

        // Vérification des données réelles
        $this->assertStringContainsString('#' . sprintf('%05d', $res->getId()), $content);
        $this->assertStringContainsString('Ben Ali Mohamed', $content);
        $this->assertStringContainsString('mohamed.benali@test.com', $content);
        $this->assertStringContainsString('+21612345678', $content);
        $this->assertStringContainsString('Terminal 1 - Tunis Carthage', $content);
        $this->assertStringContainsString('Hôtel The Residence Gammarth', $content);
        $this->assertStringContainsString('01/12/2026', $content);
        $this->assertStringContainsString('10:45', $content);
        $this->assertStringContainsString('3 passagers', $content);
        $this->assertStringContainsString('4 bagages', $content);
        $this->assertStringContainsString('TU721', $content);
        $this->assertStringContainsString('Tunisair', $content);
        $this->assertStringContainsString('Siège bébé souhaité svp', $content);
        $this->assertStringContainsString('85.00 TND', $content);
        $this->assertStringContainsString('Confirmée', $content);

        // Bouton d'impression window.print()
        $this->assertStringContainsString('window.print()', $content);
        $this->assertStringContainsString('Imprimer le voucher', $content);

        // Lien retour
        $this->assertStringContainsString('/admin/reservations', $content);
    }

    public function testVoucherDisplaysReturnTripDetails(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $admin = $this->createAdminUser($em);
        $client->loginUser($admin);
        $transfere = $this->getOrCreateTransfere($em);

        $res = (new ReservationTransfere())
            ->setTransfere($transfere)
            ->setNom('Smith')
            ->setPrenom('John')
            ->setEmail('john.smith@uk.test')
            ->setTelephone('+447123456789')
            ->setTransfereType(ReservationTransfereType::RETURN)
            ->setPickupLocation('Aéroport Enfidha')
            ->setDropoffLocation('Sousse Palace')
            ->setPickupDate(new DateTimeImmutable('2026-10-20'))
            ->setPickupTime(new DateTimeImmutable('16:00:00'))
            ->setReturnPickupLocation('Sousse Palace')
            ->setReturnDropoffLocation('Aéroport Enfidha')
            ->setReturnPickupDate(new DateTimeImmutable('2026-10-27'))
            ->setReturnPickupTime(new DateTimeImmutable('11:00:00'))
            ->setNombrePassagers(2)
            ->setNombreBagages(2)
            ->setPrixTotal('150.00')
            ->setDevise('EUR')
            ->setStatus(ReservationStatus::PENDING);
        $em->persist($res);
        $em->flush();

        $crawler = $client->request('GET', '/admin/reservations/' . $res->getId() . '/voucher');
        $this->assertResponseIsSuccessful();

        $content = (string) $client->getResponse()->getContent();

        $this->assertStringContainsString('Aller-retour', $content);
        $this->assertStringContainsString('20/10/2026', $content);
        $this->assertStringContainsString('16:00', $content);
        $this->assertStringContainsString('27/10/2026', $content);
        $this->assertStringContainsString('11:00', $content);
        $this->assertStringContainsString('Sousse Palace', $content);
        $this->assertStringContainsString('150.00 EUR', $content);
        $this->assertStringContainsString('En attente', $content);
    }

    public function testVoucherHandlesMissingOptionalDataGracefullyWithoutFakeData(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $admin = $this->createAdminUser($em);
        $client->loginUser($admin);
        $transfere = $this->getOrCreateTransfere($em);

        $res = (new ReservationTransfere())
            ->setTransfere($transfere)
            ->setNom('Martin')
            ->setPrenom('Claire')
            ->setEmail('claire.martin@test.com')
            ->setTelephone('+33600000000')
            ->setTransfereType(ReservationTransfereType::ONE_WAY)
            ->setPickupLocation('Gare de Tunis')
            ->setDropoffLocation('La Marsa Plage')
            ->setPickupDate(new DateTimeImmutable('2026-11-05'))
            ->setPickupTime(new DateTimeImmutable('09:15:00'))
            ->setNombrePassagers(1)
            ->setNombreBagages(1)
            ->setNumeroVol(null)
            ->setCompagnieVol(null)
            ->setMessage(null)
            ->setPrixTotal('40.00')
            ->setDevise('TND')
            ->setStatus(ReservationStatus::CONFIRMED);
        $em->persist($res);
        $em->flush();

        $client->request('GET', '/admin/reservations/' . $res->getId() . '/voucher');
        $this->assertResponseIsSuccessful();

        $content = (string) $client->getResponse()->getContent();
        // Pas de numéro de vol inventé, affichage explicite "Non spécifié"
        $this->assertStringContainsString('Non spécifié', $content);
        $this->assertStringNotContainsString('AF123', $content);
    }

    public function testVoucherActionAppearsInReservationsIndexDropdown(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $admin = $this->createAdminUser($em);
        $client->loginUser($admin);
        $transfere = $this->getOrCreateTransfere($em);

        $res = (new ReservationTransfere())
            ->setTransfere($transfere)
            ->setNom('TestDropdown')
            ->setPrenom('Voucher')
            ->setEmail('dropdown@test.com')
            ->setTelephone('+21655555555')
            ->setPickupLocation('Tunis')
            ->setDropoffLocation('Nabeul')
            ->setPickupDate(new DateTimeImmutable('2026-12-10'))
            ->setPickupTime(new DateTimeImmutable('08:00:00'))
            ->setPrixTotal('70.00')
            ->setDevise('TND');
        $em->persist($res);
        $em->flush();

        $crawler = $client->request('GET', '/admin/reservations');
        $this->assertResponseIsSuccessful();

        $voucherUrl = '/admin/reservations/' . $res->getId() . '/voucher';
        $voucherLinks = $crawler->filter('a[href="' . $voucherUrl . '"]');
        $this->assertGreaterThanOrEqual(1, $voucherLinks->count(), 'Le lien Voucher doit être présent dans le menu Actions.');
    }
}
