<?php

namespace App\Tests;

use App\Entity\ReservationTransfere;
use App\Enum\ReservationStatus;
use App\Service\ReservationMailerService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;

class ReservationMailerServiceTest extends KernelTestCase
{
    public static function statusTransitions(): iterable
    {
        yield 'pending to confirmed' => [ReservationStatus::PENDING, ReservationStatus::CONFIRMED, 'emails/reservation_confirmed.html.twig'];
        yield 'pending to cancelled' => [ReservationStatus::PENDING, ReservationStatus::CANCELLED, 'emails/reservation_cancelled.html.twig'];
        yield 'confirmed to cancelled' => [ReservationStatus::CONFIRMED, ReservationStatus::CANCELLED, 'emails/reservation_cancelled.html.twig'];
        yield 'cancelled to confirmed' => [ReservationStatus::CANCELLED, ReservationStatus::CONFIRMED, 'emails/reservation_confirmed.html.twig'];
    }

    #[DataProvider('statusTransitions')]
    public function testChangedFinalStatusSendsTheCorrectEmail(
        ReservationStatus $previousStatus,
        ReservationStatus $newStatus,
        string $template
    ): void {
        self::bootKernel();
        $reservation = $this->createReservation($newStatus);
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())
            ->method('send')
            ->with(self::callback(function ($email) use ($template, $reservation): bool {
                self::assertSame($template, $email->getHtmlTemplate());
                self::assertSame('client@example.test', $email->getTo()[0]->getAddress());
                self::assertSame('site@example.test', $email->getFrom()[0]->getAddress());
                self::assertSame('Aline Martin', $email->getTo()[0]->getName());
                self::assertSame($reservation, $email->getContext()['reservation']);
                self::assertStringContainsString(
                    $template === 'emails/reservation_confirmed.html.twig' ? 'Confirmation' : 'Mise à jour',
                    $email->getSubject()
                );

                $html = static::getContainer()->get(Environment::class)->render($template, $email->getContext());
                self::assertStringContainsString('Aline Martin', $html);
                self::assertStringContainsString('Aéroport Tunis', $html);
                self::assertStringContainsString('Hammamet', $html);
                self::assertStringContainsString('20/12/2026', $html);
                self::assertStringContainsString('0.00 TND', $html);
                self::assertStringContainsString(
                    $template === 'emails/reservation_confirmed.html.twig' ? 'acceptée et confirmée' : 'actuellement annulée',
                    $html
                );

                return true;
            }));

        $service = $this->createService($mailer);
        self::assertNull($service->sendStatusChangeNotification($reservation, $previousStatus));
    }

    public function testUnchangedStatusDoesNotSendAnotherEmail(): void
    {
        self::bootKernel();
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::never())->method('send');

        $service = $this->createService($mailer);
        self::assertNull($service->sendStatusChangeNotification(
            $this->createReservation(ReservationStatus::CONFIRMED),
            ReservationStatus::CONFIRMED
        ));
    }

    public function testPendingStatusDoesNotSendAnEmail(): void
    {
        self::bootKernel();
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::never())->method('send');

        $service = $this->createService($mailer);
        self::assertNull($service->sendStatusChangeNotification(
            $this->createReservation(ReservationStatus::PENDING),
            ReservationStatus::CONFIRMED
        ));
    }

    public function testInvalidRecipientReturnsAnErrorWithoutSending(): void
    {
        self::bootKernel();
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::never())->method('send');
        $reservation = $this->createReservation(ReservationStatus::CONFIRMED);
        $reservation->setEmail('not-an-email');

        $service = $this->createService($mailer);
        self::assertStringContainsString(
            'absente ou invalide',
            (string) $service->sendStatusChangeNotification($reservation, ReservationStatus::PENDING)
        );
    }

    public function testTransportFailureDoesNotThrowAndReturnsAnError(): void
    {
        self::bootKernel();
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())
            ->method('send')
            ->willThrowException(new TransportException('SMTP unavailable'));

        $service = $this->createService($mailer);
        $error = $service->sendStatusChangeNotification(
            $this->createReservation(ReservationStatus::CANCELLED),
            ReservationStatus::PENDING
        );

        self::assertStringContainsString('l’envoi de l’e-mail a échoué', (string) $error);
        self::assertStringNotContainsString('SMTP unavailable', (string) $error);
    }

    private function createService(MailerInterface&MockObject $mailer): ReservationMailerService
    {
        return new ReservationMailerService(
            $mailer,
            new NullLogger(),
            static::getContainer()->get(ValidatorInterface::class),
            'site@example.test',
            'Site de réservation'
        );
    }

    private function createReservation(ReservationStatus $status): ReservationTransfere
    {
        return (new ReservationTransfere())
            ->setNom('Martin')
            ->setPrenom('Aline')
            ->setEmail('client@example.test')
            ->setTelephone('+21600000000')
            ->setPickupLocation('Aéroport Tunis')
            ->setDropoffLocation('Hammamet')
            ->setPickupDate(new \DateTimeImmutable('2026-12-20'))
            ->setPickupTime(new \DateTimeImmutable('2026-12-20 10:30'))
            ->setStatus($status);
    }
}
