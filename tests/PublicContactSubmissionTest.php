<?php

namespace App\Tests;

use App\Entity\Contact;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PublicContactSubmissionTest extends WebTestCase
{
    public function testValidPublicRequestIsStoredEvenWhenTeamNotificationIsNotConfigured(): void
    {
        $client = static::createClient();
        $email = 'public-contact-'.bin2hex(random_bytes(5)).'@example.test';

        $crawler = $client->request('GET', '/fr/contact');
        self::assertResponseIsSuccessful();

        $form = $crawler->filter('#contactForm')->form();
        $form['contact_form[firstName]'] = 'Voyageur';
        $form['contact_form[lastName]'] = 'Test';
        $form['contact_form[email]'] = $email;
        $form['contact_form[phone]'] = '+21612345678';
        $form['contact_form[requestType]'] = 'information';
        $form['contact_form[serviceCategory]'] = 'excursion';
        $form['contact_form[reservationNumber]'] = '';
        $form['contact_form[subject]'] = 'Question sur une excursion';
        $form['contact_form[message]'] = 'Je souhaite recevoir des informations sur cette excursion.';

        $client->submit($form);

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Votre demande a été enregistrée.', (string) $client->getResponse()->getContent());

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $contact = $entityManager->getRepository(Contact::class)->findOneBy(['email' => $email]);
        self::assertInstanceOf(Contact::class, $contact);
        self::assertSame('Question sur une excursion', $contact->getSubject());
        self::assertSame('Je souhaite recevoir des informations sur cette excursion.', $contact->getMessage());
        self::assertNotNull($contact->getCreatedAt());
        self::assertFalse($contact->isRead());

        $entityManager->remove($contact);
        $entityManager->flush();
    }

    public function testPublicContactRejectsInvalidEmailWithoutStoringRequest(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/fr/contact');
        $form = $crawler->filter('#contactForm')->form();
        $form['contact_form[firstName]'] = 'Voyageur';
        $form['contact_form[lastName]'] = 'Test';
        $form['contact_form[email]'] = 'adresse-invalide';
        $form['contact_form[phone]'] = '+21612345678';
        $form['contact_form[requestType]'] = 'information';
        $form['contact_form[serviceCategory]'] = 'excursion';
        $form['contact_form[reservationNumber]'] = '';
        $form['contact_form[subject]'] = 'Question sur une excursion';
        $form['contact_form[message]'] = 'Je souhaite recevoir des informations sur cette excursion.';

        $client->submit($form);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('L’adresse e-mail n’est pas valide.', (string) $client->getResponse()->getContent());
        self::assertNull(static::getContainer()->get('doctrine')->getRepository(Contact::class)->findOneBy([
            'email' => 'adresse-invalide',
        ]));
    }
}
