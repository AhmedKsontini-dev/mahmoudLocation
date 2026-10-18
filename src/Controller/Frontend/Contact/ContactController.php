<?php

namespace App\Controller\Frontend\Contact;

use App\Form\ContactFormType;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Exception\RfcComplianceException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Error\Error as TwigError;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact', requirements: ['_locale' => 'fr|en|it|es|de'], defaults: ['_locale' => 'fr'], methods: ['GET', 'POST'])]
    #[Route('/{_locale}/contact', name: 'app_contact_locale', requirements: ['_locale' => 'fr|en|it|es|de'], methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        MailerInterface $mailer,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        ValidatorInterface $validator,
        #[Autowire('%app.mailer.from_email%')] string $fromEmail,
        #[Autowire('%app.mailer.from_name%')] string $fromName,
        #[Autowire('%app.mailer.notification_email%')] ?string $notificationEmail
    ): Response {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \App\Entity\Contact $contact */
            $contact = $form->getData();

            try {
                $entityManager->persist($contact);
                $entityManager->flush();
            } catch (DBALException $exception) {
                $logger->error('Public contact request could not be stored.', [
                    'exceptionClass' => $exception::class,
                ]);
                $this->addFlash('error', 'Une erreur de stockage est survenue. Votre demande n’a pas été enregistrée ; veuillez réessayer.');

                return $this->render('Frontend/Contact/index.html.twig', [
                    'contactForm' => $form->createView(),
                    'locale' => $request->getLocale(),
                ]);
            }

            $this->addFlash('success', 'contact.form.success_message');

            $configuredSender = trim($fromEmail);
            $configuredRecipient = trim($notificationEmail ?? '');
            if (
                $configuredSender === ''
                || count($validator->validate($configuredSender, new EmailConstraint())) > 0
                || $configuredRecipient === ''
                || count($validator->validate($configuredRecipient, new EmailConstraint())) > 0
            ) {
                $logger->error('Contact form was stored, but its notification email could not be sent because sender or notification recipient configuration is invalid.', [
                    'contactId' => $contact->getId(),
                ]);
                $this->addFlash('error', 'Votre demande est enregistrée, mais la notification à l’équipe n’est pas configurée. Aucun e-mail de notification n’a été envoyé.');
            } else {
                try {
                    $email = (new Email())
                        ->from(new Address($configuredSender, $fromName))
                        ->to($configuredRecipient)
                        ->replyTo(new Address((string) $contact->getEmail(), trim($contact->getFirstName().' '.$contact->getLastName())))
                        ->subject('Nouveau message de contact — '.$contact->getSubject())
                        ->html($this->renderView('Frontend/Contact/email.html.twig', [
                            'contact' => $contact,
                        ]));

                    $mailer->send($email);
                    $logger->info('Contact form notification accepted by the mail transport.', ['contactId' => $contact->getId()]);
                } catch (TransportExceptionInterface|RfcComplianceException|TwigError $exception) {
                    $logger->error('Contact form was stored, but its notification email could not be sent.', [
                        'contactId' => $contact->getId(),
                        'exceptionClass' => $exception::class,
                    ]);
                    $this->addFlash('error', 'Votre demande est enregistrée, mais la notification par e-mail à l’équipe a échoué. Vous n’avez pas besoin de renvoyer le formulaire.');
                }
            }

            return $this->redirectToRoute('app_contact_locale', ['_locale' => $request->getLocale()]);
        }

        return $this->render('Frontend/Contact/index.html.twig', [
            'contactForm' => $form->createView(),
            'locale' => $request->getLocale(),
        ]);
    }
}
