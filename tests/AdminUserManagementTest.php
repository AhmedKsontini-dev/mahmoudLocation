<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminUserManagementTest extends WebTestCase
{
    private function createAdminUser($entityManager, string $emailPrefix = 'admin-test'): User
    {
        $suffix = bin2hex(random_bytes(4));
        $user = (new User())
            ->setEmail($emailPrefix . '-' . $suffix . '@example.test')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword('not-used');

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    private function createStandardUser($entityManager, string $emailPrefix = 'user-test'): User
    {
        $suffix = bin2hex(random_bytes(4));
        $user = (new User())
            ->setEmail($emailPrefix . '-' . $suffix . '@example.test')
            ->setRoles(['ROLE_USER'])
            ->setPassword('not-used');

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    public function testAnonymousAccessIsRedirectedToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/users');

        self::assertResponseRedirects('/login');
    }

    public function testStandardUserCannotAccessAdminUsers(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $user = $this->createStandardUser($entityManager, 'non-admin');

        $client->loginUser($user);
        $client->request('GET', '/admin/users');

        self::assertResponseStatusCodeSame(403);
    }

    public function testAdminCanViewUsersListAndSidebar(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $admin = $this->createAdminUser($entityManager, 'superadmin');

        $client->loginUser($admin);
        $crawler = $client->request('GET', '/admin/users');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Gestion des utilisateurs', (string) $client->getResponse()->getContent());
        self::assertStringContainsString($admin->getEmail(), (string) $client->getResponse()->getContent());
        self::assertStringContainsString('Utilisateurs', (string) $client->getResponse()->getContent());
        self::assertCount(1, $crawler->filter('form#userSearchForm'));
        self::assertCount(1, $crawler->filter('table.user-table'));
        self::assertCount(1, $crawler->filter('.menu-group-toggle[aria-controls="submenu-users"][aria-expanded="true"]'));
        self::assertCount(1, $crawler->filter('#submenu-users.is-open a.menu-link.active[aria-current="page"]'));
    }

    public function testSearchFilterMatchesAndPreservesTerm(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $admin = $this->createAdminUser($entityManager, 'search-admin');

        $suffix = bin2hex(random_bytes(3));
        $targetUser = $this->createStandardUser($entityManager, 'target-search-' . $suffix);
        $otherUser = $this->createStandardUser($entityManager, 'other-isolated-' . $suffix);

        $client->loginUser($admin);

        // Recherche avec le terme spécifique
        $crawler = $client->request('GET', '/admin/users?search=' . $targetUser->getEmail());
        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();

        self::assertStringContainsString($targetUser->getEmail(), $content);
        self::assertStringNotContainsString($otherUser->getEmail(), $content);

        // Recherche d'un terme inexistant
        $client->request('GET', '/admin/users?search=nonexistent-user-query-' . bin2hex(random_bytes(6)));
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Aucun utilisateur trouvé', (string) $client->getResponse()->getContent());
    }

    public function testPaginationWorksAcrossMultiplePages(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $admin = $this->createAdminUser($entityManager, 'page-admin');

        $batchSuffix = bin2hex(random_bytes(3));
        $created = [];
        for ($i = 1; $i <= 12; $i++) {
            $created[] = $this->createStandardUser($entityManager, sprintf('batch-%s-user-%02d', $batchSuffix, $i));
        }

        $client->loginUser($admin);

        // Recherche limitée à ce lot
        $crawler = $client->request('GET', '/admin/users?search=batch-' . $batchSuffix);
        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();

        // 12 résultats = au moins 2 pages (10 par page)
        self::assertStringContainsString('12', $content);
        self::assertStringContainsString('Page suivante', $content);

        // Visiter la page 2
        $crawlerPage2 = $client->request('GET', '/admin/users?page=2&search=batch-' . $batchSuffix);
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Page précédente', (string) $client->getResponse()->getContent());
    }

    public function testEditUserSucceeds(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $admin = $this->createAdminUser($entityManager, 'editor-admin');
        $userToEdit = $this->createStandardUser($entityManager, 'to-modify');

        $client->loginUser($admin);

        $crawler = $client->request('GET', '/admin/users/' . $userToEdit->getId() . '/edit');
        self::assertResponseIsSuccessful();

        $token = $crawler->filter('input[name="user[_token]"]')->attr('value');
        $newEmail = 'modified-' . bin2hex(random_bytes(4)) . '@example.test';

        $client->request('POST', '/admin/users/' . $userToEdit->getId() . '/edit', [
            'user' => [
                'email' => $newEmail,
                'roles' => ['ROLE_ADMIN', 'ROLE_USER'],
                '_token' => $token,
            ],
        ]);

        self::assertResponseRedirects('/admin/users');

        $entityManager->clear();
        $refreshed = $entityManager->getRepository(User::class)->find($userToEdit->getId());
        self::assertNotNull($refreshed);
        self::assertSame($newEmail, $refreshed->getEmail());
        self::assertContains('ROLE_ADMIN', $refreshed->getRoles());
    }

    public function testEditDuplicateEmailFailsValidation(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $admin = $this->createAdminUser($entityManager, 'dup-admin');
        $userA = $this->createStandardUser($entityManager, 'user-a');
        $userB = $this->createStandardUser($entityManager, 'user-b');

        $client->loginUser($admin);

        $crawler = $client->request('GET', '/admin/users/' . $userA->getId() . '/edit');
        self::assertResponseIsSuccessful();

        $token = $crawler->filter('input[name="user[_token]"]')->attr('value');
        $client->request('POST', '/admin/users/' . $userA->getId() . '/edit', [
            'user' => [
                'email' => $userB->getEmail(), // Email already taken by userB
                'roles' => ['ROLE_USER'],
                '_token' => $token,
            ],
        ]);

        self::assertResponseIsSuccessful(); // Stays on edit page with error message
        self::assertStringContainsString('déjà utilisée', (string) $client->getResponse()->getContent());
    }

    public function testAdminCannotRemoveOwnAdminPrivileges(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $admin = $this->createAdminUser($entityManager, 'self-protect-admin');

        $client->loginUser($admin);

        $crawler = $client->request('GET', '/admin/users/' . $admin->getId() . '/edit');
        self::assertResponseIsSuccessful();

        $token = $crawler->filter('input[name="user[_token]"]')->attr('value');
        $client->request('POST', '/admin/users/' . $admin->getId() . '/edit', [
            'user' => [
                'email' => $admin->getEmail(),
                'roles' => ['ROLE_USER'], // Removing ROLE_ADMIN from own account
                '_token' => $token,
            ],
        ]);

        self::assertResponseIsSuccessful(); // Rejected, stays on form with warning
        self::assertStringContainsString('ne pouvez pas retirer vos propres privilèges', (string) $client->getResponse()->getContent());

        $entityManager->clear();
        $refreshed = $entityManager->getRepository(User::class)->find($admin->getId());
        self::assertNotNull($refreshed);
        self::assertContains('ROLE_ADMIN', $refreshed->getRoles());
    }

    public function testAdminCannotDeleteOwnAccount(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $admin = $this->createAdminUser($entityManager, 'no-suicide-admin');

        $client->loginUser($admin);

        // Simule tentative de suppression par POST
        $crawler = $client->request('GET', '/admin/users');
        $client->request('POST', '/admin/users/' . $admin->getId() . '/delete', [
            '_token' => 'invalid-or-valid-wont-matter-for-self',
        ]);

        self::assertResponseRedirects('/admin/users');

        $client->followRedirect();
        self::assertStringContainsString('ne pouvez pas supprimer votre propre compte', (string) $client->getResponse()->getContent());

        $entityManager->clear();
        $refreshed = $entityManager->getRepository(User::class)->find($admin->getId());
        self::assertNotNull($refreshed);
    }

    public function testAdminCanDeleteOtherUserWithValidCsrf(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $admin = $this->createAdminUser($entityManager, 'deleter-admin');
        $victim = $this->createStandardUser($entityManager, 'victim-user');
        $victimId = $victim->getId();

        $client->loginUser($admin);

        $crawler = $client->request('GET', '/admin/users?search=' . $victim->getEmail());
        self::assertResponseIsSuccessful();

        // Récupérer le token CSRF du formulaire de suppression de cette victime
        $token = $crawler->filter('form[action*="/admin/users/' . $victimId . '/delete"] input[name="_token"]')->attr('value');
        self::assertNotEmpty($token);

        $client->request('POST', '/admin/users/' . $victimId . '/delete', [
            '_token' => $token,
        ]);

        self::assertResponseRedirects('/admin/users');

        $entityManager->clear();
        $deleted = $entityManager->getRepository(User::class)->find($victimId);
        self::assertNull($deleted);
    }
}
