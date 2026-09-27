<?php

namespace App\Controller\Frontend\Contact;

use App\Form\ContactFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact', requirements: ['_locale' => 'fr|en|it|es|de'], defaults: ['_locale' => 'fr'], methods: ['GET', 'POST'])]
    #[Route('/{_locale}/contact', name: 'app_contact_locale', requirements: ['_locale' => 'fr|en|it|es|de'], methods: ['GET', 'POST'])]
    public function index(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \App\Entity\Contact $contact */
            $contact = $form->getData();

            // Send email
            $email = (new Email())
                ->from($_ENV['MAILER_FROM'] ?? 'contact@tunisiatravel.com')
                ->to('ahmedksontini122@gmail.com')
                ->replyTo($contact->getEmail())
                ->subject('Nouveau message de contact — LocationMahmoud')
                ->html($this->renderView('Frontend/Contact/email.html.twig', [
                    'contact' => $contact,
                ]));

            try {
                $entityManager->persist($contact);
                $entityManager->flush();

                $mailer->send($email);
                $this->addFlash('success', 'contact.form.success_message');
            } catch (\Exception $e) {
                $this->addFlash('error', 'contact.form.error_message');
            }

            return $this->redirectToRoute('app_contact_locale', ['_locale' => $request->getLocale()]);
        }

        return $this->render('Frontend/Contact/index.html.twig', [
            'contactForm' => $form->createView(),
            'locale' => $request->getLocale(),
        ]);
    }
}
