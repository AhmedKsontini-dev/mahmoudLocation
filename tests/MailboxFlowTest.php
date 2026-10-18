<?php

namespace App\Tests;

use App\Entity\Contact;
use App\Entity\ContactMessage;
use App\Entity\User;
use App\Enum\ContactMessageStatus;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MailboxFlowTest extends WebTestCase
{
    public function testFullMailboxManagementFlow(): void
    {
        $client = static::createClient();
        $suffix = bin2hex(random_bytes(4));
        $projectDir = static::getContainer()->getParameter('kernel.project_dir');

        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $user = (new User())
            ->setEmail('mailbox-flow-'.$suffix.'@example.test')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword('not-used');
        $entityManager->persist($user);

        $contact = (new Contact())
            ->setFirstName('Flow')
            ->setLastName('Tester')
            ->setEmail('client-'.$suffix.'@example.test')
            ->setPhone('+21600000000')
            ->setServiceCategory('location')
            ->setSubject('Sujet test '.$suffix)
            ->setMessage('Message de test pour le flux complet de la messagerie.')
            ->setRequestType('information');
        $entityManager->persist($contact);
        $entityManager->flush();

        $contactId = (int) $contact->getId();

        try {
            $client->loginUser($user);

            // 1) Rendu initial : dossiers visibles + message listé + formulaires présents.
            $crawler = $client->request('GET', '/admin/contacts');
            self::assertResponseIsSuccessful();
            $content = (string) $client->getResponse()->getContent();

            foreach (['Réception', 'Épinglés', 'Envoyés', 'Brouillons', 'Archives', 'id="mailboxItems"', 'id="mailboxReader"', 'Sujet test '.$suffix, 'Enregistrer comme brouillon'] as $marker) {
                self::assertStringContainsString($marker, $content, sprintf('Marker "%s" should be present', $marker));
            }

            // 2) Marquer le message comme lu puis non lu.
            self::assertFalse($contact->isRead());
            $readForm = $crawler->filter('form[action="/admin/contacts/'.$contactId.'/read"]');
            self::assertCount(1, $readForm);
            $client->request('POST', '/admin/contacts/'.$contactId.'/read', [
                '_token' => $readForm->filter('input[name="_token"]')->attr('value'),
                '_return_folder' => 'inbox',
            ]);
            self::assertResponseRedirects();

            $entityManager = static::getContainer()->get('doctrine')->getManager();
            $contact = $entityManager->getRepository(Contact::class)->find($contactId);
            self::assertTrue($contact->isRead());

            $crawler = $client->request('GET', '/admin/contacts?selected='.$contactId);
            $unreadForm = $crawler->filter('form[action="/admin/contacts/'.$contactId.'/unread"]');
            self::assertCount(1, $unreadForm);
            $client->request('POST', '/admin/contacts/'.$contactId.'/unread', [
                '_token' => $unreadForm->filter('input[name="_token"]')->attr('value'),
                '_return_folder' => 'inbox',
            ]);
            self::assertResponseRedirects();

            $entityManager = static::getContainer()->get('doctrine')->getManager();
            $contact = $entityManager->getRepository(Contact::class)->find($contactId);
            self::assertFalse($contact->isRead());

            // 3) Archiver le message.
            $token = $crawler->filter('input[name="_csrf_archive"]')->attr('value');
            $client->request('POST', '/admin/contacts/'.$contactId.'/archive', ['_csrf_archive' => $token, '_return_folder' => 'inbox']);
            self::assertResponseRedirects();
            self::assertStringContainsString('folder=archived', (string) $client->getResponse()->headers->get('Location'));

            $entityManager = static::getContainer()->get('doctrine')->getManager();
            $contact = $entityManager->getRepository(Contact::class)->find($contactId);
            self::assertTrue($contact->isArchived(), 'Contact should be archived in database');

            $client->request('GET', '/admin/contacts?folder=archived');
            self::assertResponseIsSuccessful();
            self::assertStringContainsString('Sujet test '.$suffix, (string) $client->getResponse()->getContent());

            $client->request('GET', '/admin/contacts');
            self::assertStringNotContainsString('Sujet test '.$suffix, (string) $client->getResponse()->getContent(), 'Archived message should not appear in inbox');

            // 3) Désarchiver le message.
            $crawler = $client->request('GET', '/admin/contacts?folder=archived');
            $token = $crawler->filter('input[name="_csrf_unarchive"]')->attr('value');
            $client->request('POST', '/admin/contacts/'.$contactId.'/unarchive', ['_csrf_unarchive' => $token, '_return_folder' => 'archived']);
            self::assertResponseRedirects();

            $entityManager = static::getContainer()->get('doctrine')->getManager();
            $contact = $entityManager->getRepository(Contact::class)->find($contactId);
            self::assertFalse($contact->isArchived(), 'Contact should be back in inbox');

            // 4) Épingler le message.
            $crawler = $client->request('GET', '/admin/contacts');
            $token = $crawler->filter('input[name="_csrf_pin"]')->attr('value');
            $client->request('POST', '/admin/contacts/'.$contactId.'/pin', ['_csrf_pin' => $token, '_return_folder' => 'inbox']);
            self::assertResponseRedirects();

            $entityManager = static::getContainer()->get('doctrine')->getManager();
            $contact = $entityManager->getRepository(Contact::class)->find($contactId);
            self::assertTrue($contact->isPinned(), 'Contact should be pinned in database');

            $client->request('GET', '/admin/contacts?folder=pinned');
            self::assertResponseIsSuccessful();
            self::assertStringContainsString('Sujet test '.$suffix, (string) $client->getResponse()->getContent());

            // 5) Désépingler le message.
            $crawler = $client->request('GET', '/admin/contacts?folder=pinned');
            $token = $crawler->filter('input[name="_csrf_unpin"]')->attr('value');
            $client->request('POST', '/admin/contacts/'.$contactId.'/unpin', ['_csrf_unpin' => $token, '_return_folder' => 'pinned']);
            self::assertResponseRedirects();

            $entityManager = static::getContainer()->get('doctrine')->getManager();
            $contact = $entityManager->getRepository(Contact::class)->find($contactId);
            self::assertFalse($contact->isPinned(), 'Contact should be unpinned in database');

            // 6) Enregistrer un brouillon avec pièce jointe.
            $crawler = $client->request('GET', '/admin/contacts');
            $token = $crawler->filter('input[name="_csrf_draft"]')->attr('value');

            $tmpFile = tempnam(sys_get_temp_dir(), 'mailbox_');
            file_put_contents($tmpFile, "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF\n");
            $upload = new UploadedFile($tmpFile, 'devis-test.pdf', 'application/pdf', null, true);

            $client->request('POST', '/admin/contacts/'.$contactId.'/draft', [
                '_csrf_draft' => $token,
                'draft_id' => '',
                'reply_message' => 'Brouillon en cours de redaction '.$suffix,
            ], ['attachment' => $upload]);
            self::assertResponseRedirects();
            self::assertStringContainsString('folder=drafts', (string) $client->getResponse()->headers->get('Location'));

            $entityManager = static::getContainer()->get('doctrine')->getManager();
            $draft = $entityManager->getRepository(ContactMessage::class)->findOneBy([
                'contact' => $contactId,
                'status' => ContactMessageStatus::DRAFT,
            ]);
            self::assertInstanceOf(ContactMessage::class, $draft, 'Draft should be persisted');
            self::assertSame('Brouillon en cours de redaction '.$suffix, $draft->getBody());
            self::assertNotNull($draft->getAttachmentPath(), 'Draft attachment should be stored');
            self::assertFileExists($projectDir.'/public/'.$draft->getAttachmentPath());

            $client->request('GET', '/admin/contacts?folder=drafts&selected='.$draft->getId());
            self::assertResponseIsSuccessful();
            $content = (string) $client->getResponse()->getContent();
            self::assertStringContainsString('Brouillon en cours de redaction '.$suffix, $content);
            self::assertStringContainsString('Modifier le brouillon', $content);

            // 7) Ouvrir le brouillon, le modifier et l'envoyer : transformé en message envoyé.
            $crawler = $client->request('GET', '/admin/contacts?folder=drafts&selected='.$draft->getId());
            $token = $crawler->filter('input[name="_csrf_reply"]')->attr('value');

            $client->request('POST', '/admin/contacts/'.$contactId.'/reply', [
                '_csrf_reply' => $token,
                'draft_id' => (string) $draft->getId(),
                'reply_message' => 'Reponse finale envoyee '.$suffix,
            ]);
            self::assertResponseRedirects();

            $entityManager = static::getContainer()->get('doctrine')->getManager();
            $sentMessage = $entityManager->getRepository(ContactMessage::class)->find($draft->getId());
            self::assertSame(ContactMessageStatus::SENT, $sentMessage->getStatus(), 'Draft should become a sent message');
            self::assertNotNull($sentMessage->getSentAt(), 'Sent date should be recorded');
            self::assertSame('Reponse finale envoyee '.$suffix, $sentMessage->getBody());
            self::assertNotNull($sentMessage->getAttachmentPath(), 'Attachment should be kept on sent message');

            $remainingDrafts = $entityManager->getRepository(ContactMessage::class)->findOneBy([
                'contact' => $contactId,
                'status' => ContactMessageStatus::DRAFT,
            ]);
            self::assertNull($remainingDrafts, 'No draft should remain after sending');

            $client->request('GET', '/admin/contacts?folder=sent&selected='.$sentMessage->getId());
            self::assertResponseIsSuccessful();
            $content = (string) $client->getResponse()->getContent();
            self::assertStringContainsString('Reponse finale envoyee '.$suffix, $content);
            self::assertStringContainsString('Message envoyé par', $content, 'Sent notice should be displayed');
            self::assertStringContainsString('devis-test.pdf', $content, 'Attachment chip should be displayed');

            // 8) Créer puis supprimer un brouillon.
            $crawler = $client->request('GET', '/admin/contacts');
            $token = $crawler->filter('input[name="_csrf_draft"]')->attr('value');
            $client->request('POST', '/admin/contacts/'.$contactId.'/draft', [
                '_csrf_draft' => $token,
                'reply_message' => 'Brouillon a supprimer '.$suffix,
            ]);
            self::assertResponseRedirects();

            $entityManager = static::getContainer()->get('doctrine')->getManager();
            $draftToDelete = $entityManager->getRepository(ContactMessage::class)->findOneBy([
                'contact' => $contactId,
                'status' => ContactMessageStatus::DRAFT,
            ]);
            self::assertInstanceOf(ContactMessage::class, $draftToDelete);
            $draftToDeleteId = (int) $draftToDelete->getId();

            $crawler = $client->request('GET', '/admin/contacts?folder=drafts&selected='.$draftToDeleteId);
            $token = $crawler->filter('input[name="_csrf_draft_delete"]')->attr('value');
            $client->request('POST', '/admin/contacts/drafts/'.$draftToDeleteId.'/delete', ['_csrf_draft_delete' => $token]);
            self::assertResponseRedirects();

            $entityManager = static::getContainer()->get('doctrine')->getManager();
            self::assertNull($entityManager->getRepository(ContactMessage::class)->find($draftToDeleteId), 'Draft should be deleted');
        } finally {
            // Nettoyage : fichiers stockés, messages, contact de test et utilisateur de test.
            $entityManager = static::getContainer()->get('doctrine')->getManager();

            $messages = $entityManager->getRepository(ContactMessage::class)->findBy(['contact' => $contactId]);
            foreach ($messages as $message) {
                if ($message->getAttachmentPath() !== null) {
                    $absolutePath = $projectDir.'/public/'.$message->getAttachmentPath();
                    if (is_file($absolutePath)) {
                        unlink($absolutePath);
                    }
                }
            }

            $contactToRemove = $entityManager->getRepository(Contact::class)->find($contactId);
            if ($contactToRemove !== null) {
                $entityManager->remove($contactToRemove);
            }

            $userToRemove = $entityManager->getRepository(User::class)->findOneBy(['email' => 'mailbox-flow-'.$suffix.'@example.test']);
            if ($userToRemove !== null) {
                $entityManager->remove($userToRemove);
            }

            $entityManager->flush();
        }
    }
}
