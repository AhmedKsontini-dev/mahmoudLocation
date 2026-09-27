<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use App\Entity\ContactMessage;
use App\Enum\ContactMessageStatus;
use App\Repository\ContactMessageRepository;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/contacts', name: 'admin_contact_')]
class ContactController extends AbstractController
{
    private const MAX_ATTACHMENT_SIZE = 5 * 1024 * 1024;

    private const ALLOWED_ATTACHMENT_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/webp',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    private const FOLDERS = ['inbox', 'pinned', 'sent', 'drafts', 'archived'];

    private const REQUEST_TYPES = ['information', 'reservation', 'group_quote', 'partnership'];

    private const ATTACHMENT_DIRECTORY = 'uploads/mailbox';

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(ContactRepository $contactRepository, ContactMessageRepository $messageRepository): Response
    {
        return $this->render('Backend/Contact/index.html.twig', [
            'inboxContacts' => $contactRepository->findInbox(),
            'archivedContacts' => $contactRepository->findArchived(),
            'pinnedContacts' => $contactRepository->findPinned(),
            'sentMessages' => $messageRepository->findSent(),
            'draftMessages' => $messageRepository->findDrafts(),
        ]);
    }

    #[Route('/{id}/show', name: 'show', methods: ['GET'])]
    public function show(Contact $contact): Response
    {
        return $this->render('Backend/Contact/show.html.twig', [
            'contact' => $contact,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(
        Contact $contact,
        Request $request,
        ContactMessageRepository $messageRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('delete'.$contact->getId(), $request->request->getString('_token'))) {
            $this->addFlash('error', 'Jeton de sécurité invalide. Veuillez réessayer.');

            return $this->redirectToRoute('admin_contact_index', $this->returnParams($request));
        }

        // Supprime les messages liés (envoyés, brouillons) et leurs pièces jointes.
        foreach ($messageRepository->findBy(['contact' => $contact]) as $message) {
            $this->deleteStoredAttachment($message->getAttachmentPath());
            $entityManager->remove($message);
        }

        $entityManager->remove($contact);
        $entityManager->flush();

        $this->addFlash('success', 'Le contact a été supprimé avec succès.');

        return $this->redirectToRoute('admin_contact_index', $this->returnParams($request));
    }

    #[Route('/{id}/archive', name: 'archive', methods: ['POST'])]
    public function archive(Contact $contact, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('archive'.$contact->getId(), $request->request->getString('_csrf_archive'))) {
            $this->addFlash('error', 'Jeton de sécurité invalide. Veuillez réessayer.');

            return $this->redirectToRoute('admin_contact_index', $this->returnParams($request, $contact->getId()));
        }

        $contact->setIsArchived(true);
        $entityManager->flush();

        $this->addFlash('success', 'Le message a été archivé. Il reste accessible dans « Archives ».');

        return $this->redirectToRoute('admin_contact_index', [
            'folder' => 'archived',
            'selected' => $contact->getId(),
        ]);
    }

    #[Route('/{id}/unarchive', name: 'unarchive', methods: ['POST'])]
    public function unarchive(Contact $contact, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('unarchive'.$contact->getId(), $request->request->getString('_csrf_unarchive'))) {
            $this->addFlash('error', 'Jeton de sécurité invalide. Veuillez réessayer.');

            return $this->redirectToRoute('admin_contact_index', $this->returnParams($request, $contact->getId()));
        }

        $contact->setIsArchived(false);
        $entityManager->flush();

        $this->addFlash('success', 'Le message a été remis dans la boîte de réception.');

        $params = $this->returnParams($request, $contact->getId());
        $params['folder'] = 'inbox';

        return $this->redirectToRoute('admin_contact_index', $params);
    }

    #[Route('/{id}/pin', name: 'pin', methods: ['POST'])]
    public function pin(Contact $contact, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('pin'.$contact->getId(), $request->request->getString('_csrf_pin'))) {
            $this->addFlash('error', 'Jeton de sécurité invalide. Veuillez réessayer.');

            return $this->redirectToRoute('admin_contact_index', $this->returnParams($request, $contact->getId()));
        }

        $contact->setIsPinned(true);
        $entityManager->flush();

        $this->addFlash('success', 'Le message a été épinglé.');

        return $this->redirectToRoute('admin_contact_index', $this->returnParams($request, $contact->getId()));
    }

    #[Route('/{id}/unpin', name: 'unpin', methods: ['POST'])]
    public function unpin(Contact $contact, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('unpin'.$contact->getId(), $request->request->getString('_csrf_unpin'))) {
            $this->addFlash('error', 'Jeton de sécurité invalide. Veuillez réessayer.');

            return $this->redirectToRoute('admin_contact_index', $this->returnParams($request, $contact->getId()));
        }

        $contact->setIsPinned(false);
        $entityManager->flush();

        $this->addFlash('success', 'Le message a été désépinglé.');

        return $this->redirectToRoute('admin_contact_index', $this->returnParams($request, $contact->getId()));
    }

    #[Route('/{id}/reply', name: 'reply', methods: ['POST'])]
    public function reply(
        Contact $contact,
        Request $request,
        MailerInterface $mailer,
        LoggerInterface $logger,
        ContactMessageRepository $messageRepository,
        EntityManagerInterface $entityManager,
        #[Autowire('%env(MAILER_DSN)%')] string $mailerDsn
    ): Response {
        if (!$this->isCsrfTokenValid('reply'.$contact->getId(), $request->request->getString('_csrf_reply'))) {
            $this->addFlash('error', 'Jeton de sécurité invalide. Veuillez réessayer.');

            return $this->redirectToRoute('admin_contact_index', $this->returnParams($request, $contact->getId()));
        }

        $draft = $this->resolveDraft($request, $contact, $messageRepository, false);
        $replyMessage = trim($request->request->getString('reply_message'));

        if ($replyMessage === '') {
            $this->addFlash('error', 'La réponse ne peut pas être vide.');

            return $this->redirectToRoute('admin_contact_index', $this->redirectParamsForDraft($request, $contact, $draft));
        }

        $attachment = $request->files->get('attachment');
        $attachment = $attachment instanceof UploadedFile ? $attachment : null;

        if (($attachmentError = $this->validateAttachment($attachment)) !== null) {
            $this->addFlash('error', $attachmentError);

            return $this->redirectToRoute('admin_contact_index', $this->redirectParamsForDraft($request, $contact, $draft));
        }

        $subject = 'Re: '.$contact->getSubject();
        $draftAttachmentPath = $draft?->getAttachmentPath();

        $email = (new Email())
            ->from($this->resolveSenderAddress($mailerDsn))
            ->to((string) $contact->getEmail())
            ->subject($subject)
            ->text($replyMessage.$this->quotedOriginalMessage($contact));

        $storedAttachment = null;

        if ($attachment !== null) {
            $storedAttachment = $this->storeAttachment($attachment);
            $email->attach($attachment->getContent(), $storedAttachment['name'], (string) $attachment->getMimeType());
        } elseif ($draftAttachmentPath !== null && is_file($this->attachmentAbsolutePath($draftAttachmentPath))) {
            $email->attachFromPath($this->attachmentAbsolutePath($draftAttachmentPath), (string) $draft?->getAttachmentName());
        }

        try {
            $mailer->send($email);
        } catch (\Throwable $exception) {
            if ($storedAttachment !== null) {
                $this->deleteStoredAttachment($storedAttachment['path']);
            }

            $logger->error('Échec de l\'envoi de la réponse au contact {contactId}.', [
                'contactId' => $contact->getId(),
                'exception' => $exception,
            ]);
            $this->addFlash('error', "L'envoi de l'email a échoué. Vérifiez la configuration SMTP (MAILER_DSN).");

            return $this->redirectToRoute('admin_contact_index', $this->redirectParamsForDraft($request, $contact, $draft));
        }

        $now = new \DateTimeImmutable();

        if ($draft !== null) {
            // Le brouillon est transformé en message envoyé.
            if ($storedAttachment !== null && $draftAttachmentPath !== null && $draftAttachmentPath !== $storedAttachment['path']) {
                $this->deleteStoredAttachment($draftAttachmentPath);
            }

            $sentMessage = $draft;
        } else {
            $sentMessage = (new ContactMessage())->setContact($contact);
        }

        $sentMessage
            ->setStatus(ContactMessageStatus::SENT)
            ->setRecipient((string) $contact->getEmail())
            ->setSubject($subject)
            ->setBody($replyMessage)
            ->setUpdatedAt($now)
            ->setSentAt($now);

        if ($storedAttachment !== null) {
            $sentMessage->setAttachmentName($storedAttachment['name'])->setAttachmentPath($storedAttachment['path']);
        }

        $entityManager->persist($sentMessage);
        $entityManager->flush();

        $this->addFlash('success', sprintf(
            'Votre réponse a été envoyée à %s. Une copie a été enregistrée dans « Envoyés ».',
            $contact->getEmail()
        ));

        $params = $this->returnParams($request, $contact->getId());

        if (($params['folder'] ?? null) === 'drafts') {
            unset($params['folder']);
        }

        return $this->redirectToRoute('admin_contact_index', $params);
    }

    #[Route('/{id}/draft', name: 'draft', methods: ['POST'])]
    public function saveDraft(
        Contact $contact,
        Request $request,
        ContactMessageRepository $messageRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('draft'.$contact->getId(), $request->request->getString('_csrf_draft'))) {
            $this->addFlash('error', 'Jeton de sécurité invalide. Veuillez réessayer.');

            return $this->redirectToRoute('admin_contact_index', $this->returnParams($request, $contact->getId()));
        }

        $draft = $this->resolveDraft($request, $contact, $messageRepository, true);
        $body = trim($request->request->getString('reply_message'));

        if ($body === '') {
            $this->addFlash('warning', 'Le brouillon ne peut pas être vide.');

            return $this->redirectToRoute('admin_contact_index', $this->redirectParamsForDraft($request, $contact, $draft));
        }

        $attachment = $request->files->get('attachment');
        $attachment = $attachment instanceof UploadedFile ? $attachment : null;

        if (($attachmentError = $this->validateAttachment($attachment)) !== null) {
            $this->addFlash('error', $attachmentError);

            return $this->redirectToRoute('admin_contact_index', $this->redirectParamsForDraft($request, $contact, $draft));
        }

        if ($draft === null) {
            $draft = (new ContactMessage())->setContact($contact);
        }

        if ($attachment !== null) {
            $storedAttachment = $this->storeAttachment($attachment);
            $this->deleteStoredAttachment($draft->getAttachmentPath());
            $draft->setAttachmentName($storedAttachment['name'])->setAttachmentPath($storedAttachment['path']);
        }

        $draft
            ->setStatus(ContactMessageStatus::DRAFT)
            ->setRecipient((string) $contact->getEmail())
            ->setSubject('Re: '.$contact->getSubject())
            ->setBody($body)
            ->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->persist($draft);
        $entityManager->flush();

        $this->addFlash('success', 'Brouillon enregistré. Vous pouvez le reprendre depuis « Brouillons ».');

        return $this->redirectToRoute('admin_contact_index', [
            'folder' => 'drafts',
            'selected' => $draft->getId(),
        ]);
    }

    #[Route('/drafts/{id}/delete', name: 'draft_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function deleteDraft(
        int $id,
        Request $request,
        ContactMessageRepository $messageRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $draft = $messageRepository->find($id);

        if (!$draft instanceof ContactMessage || $draft->getStatus() !== ContactMessageStatus::DRAFT) {
            throw $this->createNotFoundException('Brouillon introuvable.');
        }

        if (!$this->isCsrfTokenValid('draft_delete'.$draft->getId(), $request->request->getString('_csrf_draft_delete'))) {
            $this->addFlash('error', 'Jeton de sécurité invalide. Veuillez réessayer.');

            return $this->redirectToRoute('admin_contact_index', ['folder' => 'drafts']);
        }

        $this->deleteStoredAttachment($draft->getAttachmentPath());
        $entityManager->remove($draft);
        $entityManager->flush();

        $this->addFlash('success', 'Le brouillon a été supprimé.');

        return $this->redirectToRoute('admin_contact_index', ['folder' => 'drafts']);
    }

    /**
     * Retrouve le brouillon ciblé par le formulaire, s'il est bien valide
     * et rattaché au contact courant.
     */
    private function resolveDraft(
        Request $request,
        Contact $contact,
        ContactMessageRepository $messageRepository,
        bool $fallbackToLatest
    ): ?ContactMessage {
        $draftId = (int) $request->request->get('draft_id', 0);

        if ($draftId > 0) {
            $draft = $messageRepository->find($draftId);

            return $draft instanceof ContactMessage
                && $draft->getStatus() === ContactMessageStatus::DRAFT
                && $draft->getContact()?->getId() === $contact->getId() ? $draft : null;
        }

        return $fallbackToLatest ? $messageRepository->findLatestDraftForContact($contact) : null;
    }

    /**
     * Paramètres de redirection vers la vue courante de la messagerie,
     * communiqués par les champs cachés des formulaires.
     *
     * @return array<string, int|string>
     */
    private function returnParams(Request $request, ?int $selectedId = null): array
    {
        $params = [];

        $folder = $request->request->getString('_return_folder', 'inbox');

        if (!in_array($folder, self::FOLDERS, true)) {
            $folder = 'inbox';
        }

        if ($folder !== 'inbox') {
            $params['folder'] = $folder;
        }

        if ($folder === 'inbox') {
            $type = $request->request->getString('_return_type', '');

            if (in_array($type, self::REQUEST_TYPES, true)) {
                $params['type'] = $type;
            }
        }

        $tab = $request->request->getString('_return_tab', 'all');

        if (in_array($tab, ['today', 'week'], true)) {
            $params['tab'] = $tab;
        }

        if ($selectedId !== null) {
            $params['selected'] = $selectedId;
        }

        return $params;
    }

    /**
     * Redirection adaptée en cas d'erreur : retour à l'éditeur du brouillon
     * s'il est connu, sinon vers la vue courante.
     *
     * @return array<string, int|string>
     */
    private function redirectParamsForDraft(Request $request, Contact $contact, ?ContactMessage $draft): array
    {
        if ($draft !== null) {
            return ['folder' => 'drafts', 'selected' => $draft->getId()];
        }

        return $this->returnParams($request, $contact->getId());
    }

    private function validateAttachment(?UploadedFile $attachment): ?string
    {
        if ($attachment === null) {
            return null;
        }

        if ($attachment->getSize() > self::MAX_ATTACHMENT_SIZE) {
            return 'La pièce jointe dépasse la taille maximale autorisée (5 Mo).';
        }

        if (!in_array((string) $attachment->getMimeType(), self::ALLOWED_ATTACHMENT_TYPES, true)) {
            return 'Type de pièce jointe non autorisé (PDF, images, Word ou Excel).';
        }

        return null;
    }

    /**
     * Enregistre la pièce jointe sur le disque et retourne son nom d'origine
     * ainsi que son chemin relatif (public/).
     *
     * @return array{name: string, path: string}
     */
    private function storeAttachment(UploadedFile $attachment): array
    {
        $directory = $this->attachmentDirectory();

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $extension = $attachment->guessExtension() ?: $attachment->getClientOriginalExtension();
        $storedName = bin2hex(random_bytes(16)).($extension !== '' ? '.'.$extension : '');
        $attachment->move($directory, $storedName);

        return [
            'name' => $attachment->getClientOriginalName(),
            'path' => self::ATTACHMENT_DIRECTORY.'/'.$storedName,
        ];
    }

    private function deleteStoredAttachment(?string $relativePath): void
    {
        if ($relativePath === null || $relativePath === '') {
            return;
        }

        $absolutePath = $this->attachmentAbsolutePath($relativePath);

        if (is_file($absolutePath)) {
            unlink($absolutePath);
        }
    }

    private function attachmentDirectory(): string
    {
        return $this->getParameter('kernel.project_dir').'/public/'.self::ATTACHMENT_DIRECTORY;
    }

    private function attachmentAbsolutePath(string $relativePath): string
    {
        return $this->getParameter('kernel.project_dir').'/public/'.$relativePath;
    }

    private function resolveSenderAddress(string $mailerDsn): string
    {
        $user = parse_url($mailerDsn, PHP_URL_USER);

        if (is_string($user)) {
            $address = urldecode($user);

            if (filter_var($address, FILTER_VALIDATE_EMAIL)) {
                return $address;
            }
        }

        return 'no-reply@locationmahmoud.tn';
    }

    private function quotedOriginalMessage(Contact $contact): string
    {
        return sprintf(
            "\n\n---\nMessage original du %s :\n%s",
            $contact->getCreatedAt()?->format('d/m/Y H:i') ?? '',
            (string) $contact->getMessage()
        );
    }
}
