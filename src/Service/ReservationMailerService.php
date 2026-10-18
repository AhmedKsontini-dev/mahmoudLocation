<?php

namespace App\Service;

use App\Entity\ReservationExcursion;
use App\Entity\ReservationTransfere;
use App\Enum\ReservationStatus;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Exception\RfcComplianceException;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;
use Twig\Error\Error as TwigError;

class ReservationMailerService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        private readonly ValidatorInterface $validator,
        private readonly string $fromEmail,
        private readonly string $fromName,
    ) {
    }

    /**
     * Returns an administrator-facing error when the status was saved but its email failed.
     */
    public function sendStatusChangeNotification(
        ReservationTransfere $reservation,
        ReservationStatus $previousStatus
    ): ?string {
        $newStatus = $reservation->getStatus();
        if (
            $previousStatus === $newStatus
            || !in_array($newStatus, [ReservationStatus::CONFIRMED, ReservationStatus::CANCELLED], true)
        ) {
            return null;
        }

        $recipientEmail = trim((string) $reservation->getEmail());
        if ($recipientEmail === '' || count($this->validator->validate($recipientEmail, new Email())) > 0) {
            $this->logger->warning('Reservation status email skipped because its recipient address is missing or invalid.', [
                'reservationId' => $reservation->getId(),
            ]);

            return 'Le statut a été enregistré, mais l’adresse e-mail du client est absente ou invalide. Aucun e-mail n’a été envoyé.';
        }

        if ($this->fromEmail === '' || count($this->validator->validate($this->fromEmail, new Email())) > 0) {
            $this->logger->error('Reservation status email skipped because the configured sender address is missing or invalid.', [
                'reservationId' => $reservation->getId(),
            ]);

            return 'Le statut a été enregistré, mais l’adresse d’expédition du site est mal configurée. Aucun e-mail n’a été envoyé.';
        }

        $clientName = trim(($reservation->getPrenom() ?? '') . ' ' . ($reservation->getNom() ?? ''));
        $isConfirmed = $newStatus === ReservationStatus::CONFIRMED;
        $subject = $isConfirmed
            ? sprintf('Confirmation de votre réservation #%d', $reservation->getId())
            : sprintf('Mise à jour du statut de votre réservation #%d', $reservation->getId());

        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to(new Address($recipientEmail, $clientName))
            ->subject($subject)
            ->htmlTemplate($isConfirmed
                ? 'emails/reservation_confirmed.html.twig'
                : 'emails/reservation_cancelled.html.twig')
            ->context([
                'reservation' => $reservation,
                'clientName' => $clientName,
                'siteName' => $this->fromName,
            ]);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface|RfcComplianceException|TwigError $exception) {
            $this->logger->error('Reservation status email could not be sent.', [
                'reservationId' => $reservation->getId(),
                'status' => $newStatus->value,
            ]);

            return 'Le statut a été enregistré, mais l’envoi de l’e-mail a échoué. Vérifiez la configuration du service e-mail.';
        }

        $this->logger->info('Reservation status email sent.', [
            'reservationId' => $reservation->getId(),
            'status' => $newStatus->value,
        ]);

        return null;
    }

    /**
     * Returns an administrator-facing error when the status was saved but its excursion email failed.
     */
    public function sendExcursionStatusChangeNotification(
        ReservationExcursion $reservation,
        string $previousStatus
    ): ?string {
        $newStatus = $reservation->getStatut();
        if (
            $previousStatus === $newStatus
            || !in_array($newStatus, [ReservationExcursion::STATUS_CONFIRMED, ReservationExcursion::STATUS_CANCELLED], true)
        ) {
            return null;
        }

        $recipientEmail = trim((string) $reservation->getEmail());
        if ($recipientEmail === '' || count($this->validator->validate($recipientEmail, new Email())) > 0) {
            $this->logger->warning('Excursion reservation status email skipped because recipient address is missing or invalid.', [
                'reservationId' => $reservation->getId(),
            ]);

            return 'Le statut a été enregistré, mais l’adresse e-mail du client est absente ou invalide. Aucun e-mail n’a été envoyé.';
        }

        if ($this->fromEmail === '' || count($this->validator->validate($this->fromEmail, new Email())) > 0) {
            $this->logger->error('Excursion reservation status email skipped because sender address is missing or invalid.', [
                'reservationId' => $reservation->getId(),
            ]);

            return 'Le statut a été enregistré, mais l’adresse d’expédition du site est mal configurée. Aucun e-mail n’a été envoyé.';
        }

        $clientName = $reservation->getNomComplet();
        $isConfirmed = $newStatus === ReservationExcursion::STATUS_CONFIRMED;
        $subject = $isConfirmed
            ? sprintf('Confirmation de votre réservation d’excursion #%d', $reservation->getId())
            : sprintf('Mise à jour du statut de votre réservation d’excursion #%d', $reservation->getId());

        $excursionTitle = $reservation->getExcursion()?->getTitleTranslated('fr') ?? 'Excursion';

        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to(new Address($recipientEmail, $clientName))
            ->subject($subject)
            ->htmlTemplate($isConfirmed
                ? 'emails/reservation_excursion_confirmed.html.twig'
                : 'emails/reservation_excursion_cancelled.html.twig')
            ->context([
                'reservation' => $reservation,
                'excursionTitle' => $excursionTitle,
                'clientName' => $clientName,
                'siteName' => $this->fromName,
            ]);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface|RfcComplianceException|TwigError $exception) {
            $this->logger->error('Excursion reservation status email could not be sent.', [
                'reservationId' => $reservation->getId(),
                'status' => $newStatus,
                'error' => $exception->getMessage(),
            ]);

            return 'Le statut a été enregistré, mais l’envoi de l’e-mail a échoué. Vérifiez la configuration du service e-mail.';
        }

        $this->logger->info('Excursion reservation status email sent.', [
            'reservationId' => $reservation->getId(),
            'status' => $newStatus,
        ]);

        return null;
    }
}
