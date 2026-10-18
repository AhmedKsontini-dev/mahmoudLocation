<?php

namespace App\Tests;

use App\Entity\User;
use App\Entity\ContactMessage;
use App\Enum\ContactMessageStatus;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminContactComposeTest extends WebTestCase
{
    public function testAnonymousUserCannotOpenComposePage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/contacts/new-message');

        self::assertResponseRedirects('/login');
    }

    public function testStandardUserCannotOpenComposePage(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createUser(['ROLE_USER']));
        $client->request('GET', '/admin/contacts/new-message');

        self::assertResponseStatusCodeSame(403);
    }

    public function testAdminCanOpenComposePageFromMailbox(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createAdminUser());

        $crawler = $client->request('GET', '/admin/contacts');
        self::assertResponseIsSuccessful();
        self::assertSame('/admin/contacts/new-message', $crawler->filter('a[aria-label="Nouveau message"]')->attr('href'));

        $crawler = $client->click($crawler->filter('a[aria-label="Nouveau message"]')->link());
        self::assertResponseIsSuccessful();
        self::assertSame(1, $crawler->filter('form#newContactMessageForm')->count());
        self::assertSame(1, $crawler->filter('input[name="admin_contact_message[_token]"]')->count());
    }

    public function testInvalidMessageShowsFieldErrorsAndRetainsSubmittedValues(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createAdminUser());

        $crawler = $client->request('GET', '/admin/contacts/new-message');
        $csrfToken = $crawler->filter('input[name="admin_contact_message[_token]"]')->attr('value');

        $crawler = $client->request('POST', '/admin/contacts/new-message', [
            'admin_contact_message' => [
                '_token' => $csrfToken,
                'recipient' => 'adresse-invalide',
                'recipientName' => '',
                'subject' => '',
                'body' => '',
            ],
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertStringContainsString('Veuillez saisir une adresse e-mail valide.', (string) $client->getResponse()->getContent());
        self::assertStringContainsString('Veuillez saisir un sujet.', (string) $client->getResponse()->getContent());
        self::assertStringContainsString('Veuillez saisir le contenu du message.', (string) $client->getResponse()->getContent());
        self::assertSame('adresse-invalide', $crawler->filter('#admin_contact_message_recipient')->attr('value'));
    }

    public function testComposeFormRejectsInvalidCsrfToken(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createAdminUser());

        $client->request('POST', '/admin/contacts/new-message', [
            'admin_contact_message' => [
                '_token' => 'invalid-token',
                'recipient' => 'recipient@example.test',
                'recipientName' => '',
                'subject' => 'Test',
                'body' => 'Test content.',
            ],
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertStringContainsString('Le jeton CSRF est invalide.', (string) $client->getResponse()->getContent());
    }

    public function testValidMessageIsAcceptedByConfiguredMailerForUnregisteredRecipient(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createAdminUser());

        $crawler = $client->request('GET', '/admin/contacts/new-message');
        $csrfToken = $crawler->filter('input[name="admin_contact_message[_token]"]')->attr('value');

        $client->request('POST', '/admin/contacts/new-message', [
            'admin_contact_message' => [
                '_token' => $csrfToken,
                'recipient' => 'not-in-contacts@example.test',
                'recipientName' => 'Destinataire de test',
                'subject' => 'Message de test',
                'body' => 'Contenu du message de test.',
            ],
        ]);

        self::assertResponseRedirects('/admin/contacts');
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Le message a été accepté par le service de messagerie.', (string) $client->getResponse()->getContent());

        $message = static::getContainer()->get('doctrine')->getRepository(ContactMessage::class)->findOneBy([
            'recipient' => 'not-in-contacts@example.test',
            'subject' => 'Message de test',
        ]);
        self::assertInstanceOf(ContactMessage::class, $message);
        self::assertNull($message->getContact());
        self::assertSame(ContactMessageStatus::SENT, $message->getStatus());
        self::assertSame('Destinataire de test', $message->getRecipientName());
        self::assertNotNull($message->getSentAt());
    }

    private function createAdminUser(): User
    {
        return $this->createUser(['ROLE_ADMIN']);
    }

    /**
     * @param list<string> $roles
     */
    private function createUser(array $roles): User
    {
        $user = (new User())
            ->setEmail('compose-admin-'.bin2hex(random_bytes(5)).'@example.test')
            ->setRoles($roles)
            ->setPassword('not-used');

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }
}
