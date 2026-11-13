<?php

namespace App\Tests;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class FrontendNavbarAuthorizationTest extends WebTestCase
{
    public function testAdminLinkIsOnlyRenderedForAdministrators(): void
    {
        $client = static::createClient();
        $users = [];

        try {
            $crawler = $client->request('GET', '/fr');
            self::assertResponseIsSuccessful();
            self::assertSame(0, $this->adminLinkCount($crawler));

            $user = $this->createUser(['ROLE_USER']);
            $users[] = $user;
            $client->loginUser($user);
            $crawler = $client->request('GET', '/fr');
            self::assertResponseIsSuccessful();
            self::assertSame(0, $this->adminLinkCount($crawler));

            $admin = $this->createUser(['ROLE_ADMIN']);
            $users[] = $admin;
            $client->loginUser($admin);
            $crawler = $client->request('GET', '/fr');
            self::assertResponseIsSuccessful();
            self::assertSame(1, $this->adminLinkCount($crawler));
        } finally {
            $entityManager = static::getContainer()->get(EntityManagerInterface::class);
            foreach ($users as $user) {
                $managedUser = $entityManager->find(User::class, $user->getId());
                if ($managedUser !== null) {
                    $entityManager->remove($managedUser);
                }
            }
            $entityManager->flush();
        }
    }

    /**
     * @param list<string> $roles
     */
    private function createUser(array $roles): User
    {
        $user = (new User())
            ->setEmail('navbar-' . bin2hex(random_bytes(5)) . '@example.test')
            ->setRoles($roles)
            ->setPassword('not-used');

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    private function adminLinkCount(Crawler $crawler): int
    {
        return $crawler->filter('.auth-user-menu a[href="/admin/transferts"]')->count();
    }
}
