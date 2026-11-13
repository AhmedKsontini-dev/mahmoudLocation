<?php

namespace App\Tests;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginPageDisabledTest extends WebTestCase
{
    public function testLoginPageRedirectsToHomepageAndLocalizedLoginPathsAreUnavailable(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        self::assertResponseRedirects('/');
        $crawler = $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSame(1, $crawler->filter('form[data-auth-login-form][action="/login"]')->count());
        self::assertSame('', $crawler->filter('[data-auth-modal]')->attr('data-auth-initial'));

        foreach (['/fr/login', '/en/login', '/de/login', '/es/login', '/it/login'] as $path) {
            $client->request('GET', $path);
            self::assertResponseStatusCodeSame(404);
        }
    }

    public function testLoginPostEndpointStillHandlesModalAuthentication(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/fr');
        $csrfToken = $crawler->filter('form[data-auth-login-form] input[name="_csrf_token"]')->attr('value');

        $client->request(
            'POST',
            '/login',
            [
                '_username' => 'unknown-user@example.test',
                '_password' => 'invalid-password',
                '_csrf_token' => $csrfToken,
                '_target_path' => '/fr',
            ],
            [],
            [
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            ],
        );

        self::assertResponseStatusCodeSame(422);
        $response = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(['Aucun compte ne correspond à cette adresse email.'], $response['errors']['email']);
    }

    public function testValidModalLoginStillCreatesAnAuthenticatedSession(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $user = (new User())
            ->setEmail('login-page-' . bin2hex(random_bytes(5)) . '@example.test')
            ->setRoles(['ROLE_USER']);
        $user->setPassword(static::getContainer()->get(UserPasswordHasherInterface::class)->hashPassword($user, 'valid-password'));
        $entityManager->persist($user);
        $entityManager->flush();
        $userId = $user->getId();

        try {
            $crawler = $client->request('GET', '/fr');
            $csrfToken = $crawler->filter('form[data-auth-login-form] input[name="_csrf_token"]')->attr('value');
            $client->request(
                'POST',
                '/login',
                [
                    '_username' => $user->getEmail(),
                    '_password' => 'valid-password',
                    '_csrf_token' => $csrfToken,
                    '_target_path' => '/fr',
                ],
                [],
                [
                    'HTTP_ACCEPT' => 'application/json',
                    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
                ],
            );

            self::assertResponseIsSuccessful();
            self::assertSame(['success' => true], json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR));

            $client->request('GET', '/profile');
            self::assertResponseIsSuccessful();
        } finally {
            $managedUser = $entityManager->find(User::class, $userId);
            if ($managedUser !== null) {
                $entityManager->remove($managedUser);
                $entityManager->flush();
            }
        }
    }
}
