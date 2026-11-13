<?php

namespace App\Controller\Frontend\Profile;

use App\Entity\Reservation;
use App\Entity\ReservationExcursion;
use App\Entity\ReservationTransfere;
use App\Entity\User;
use App\Enum\ReservationStatus;
use App\Form\ProfileFormType;
use App\Repository\ReservationExcursionRepository;
use App\Repository\ReservationRepository;
use App\Repository\ReservationTransfereRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile', methods: ['GET', 'POST'])]
    #[Route('/{_locale}/profile', name: 'app_profile_locale', requirements: ['_locale' => 'fr|en|it|es|de'], methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        ReservationExcursionRepository $excursionRepository,
        ReservationTransfereRepository $transferRepository,
        ReservationRepository $legacyRepository,
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $filter = $request->query->getString('filter', 'all');
        if (!in_array($filter, ['all', 'confirmed', 'pending', 'cancelled', 'completed'], true)) {
            throw new BadRequestHttpException('Invalid reservation status filter.');
        }

        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'profile.saved');

            return $this->redirectToRoute('app_profile_locale', [
                '_locale' => $request->getLocale(),
                'filter' => $filter,
            ]);
        }

        $excursionStatus = match ($filter) {
            'confirmed' => ReservationExcursion::STATUS_CONFIRMED,
            'pending' => ReservationExcursion::STATUS_PENDING,
            'cancelled' => ReservationExcursion::STATUS_CANCELLED,
            default => null,
        };
        $transferStatus = match ($filter) {
            'confirmed' => ReservationStatus::CONFIRMED,
            'pending' => ReservationStatus::PENDING,
            'cancelled' => ReservationStatus::CANCELLED,
            default => null,
        };
        $legacyStatus = match ($filter) {
            'confirmed' => Reservation::STATUS_CONFIRMED,
            'pending' => Reservation::STATUS_PENDING,
            'cancelled' => Reservation::STATUS_CANCELLED,
            'completed' => Reservation::STATUS_COMPLETED,
            default => null,
        };

        $reservations = [];
        $excursionReservations = $filter === 'completed' ? [] : $excursionRepository->findForUser($user, $excursionStatus);
        foreach ($excursionReservations as $reservation) {
            $status = $this->statusPresentation($reservation->getStatut());
            $reservations[] = [
                'reference' => $reservation->getId(),
                'type' => 'profile.type.excursion',
                'title' => $reservation->getExcursion()?->getTitleTranslated($request->getLocale()),
                'details' => $reservation->getLieuPriseEnCharge(),
                'detailsIsTranslation' => false,
                'createdAt' => $reservation->getCreatedAt(),
                'serviceDate' => $reservation->getDateExcursion(),
                'serviceTime' => $reservation->getHeureDepart()?->format('H:i'),
                'returnDate' => null,
                'participants' => $reservation->getTotalParticipants(),
                'price' => number_format($reservation->getPrixTotalCalcule(), 2, '.', ''),
                'currency' => 'TND',
                'priceIsEstimate' => true,
                ...$status,
            ];
        }

        $transferReservations = $filter === 'completed' ? [] : $transferRepository->findForUser($user, $transferStatus);
        foreach ($transferReservations as $reservation) {
            $status = $this->statusPresentation($reservation->getStatus()->value);
            $reservations[] = [
                'reference' => $reservation->getId(),
                'type' => 'profile.type.transfer',
                'title' => $reservation->getPickupLocation().' → '.$reservation->getDropoffLocation(),
                'details' => $reservation->getTransfereType()->value === 'return' ? 'profile.return_transfer' : null,
                'detailsIsTranslation' => true,
                'createdAt' => $reservation->getCreatedAt(),
                'serviceDate' => $reservation->getPickupDate(),
                'serviceTime' => $reservation->getPickupTime()?->format('H:i'),
                'returnDate' => $reservation->getReturnPickupDate(),
                'participants' => $reservation->getNombrePassagers(),
                'price' => $reservation->getPrixTotal(),
                'currency' => $reservation->getDevise(),
                'priceIsEstimate' => false,
                ...$status,
            ];
        }

        foreach ($legacyRepository->findForUser($user, $legacyStatus) as $reservation) {
            $status = $this->statusPresentation($reservation->getStatus());
            $reservations[] = [
                'reference' => $reservation->getId(),
                'type' => 'profile.type.excursion',
                'title' => $reservation->getExcursion()?->getTitleTranslated($request->getLocale()),
                'details' => $reservation->getPickupAddress() ?? $reservation->getPickupRegion(),
                'detailsIsTranslation' => false,
                'createdAt' => $reservation->getCreatedAt(),
                'serviceDate' => $reservation->getDate(),
                'serviceTime' => null,
                'returnDate' => null,
                'participants' => $reservation->getTotalParticipants(),
                'price' => $reservation->getTotalPrice(),
                'currency' => null,
                'priceIsEstimate' => false,
                ...$status,
            ];
        }

        usort($reservations, static function (array $first, array $second): int {
            $firstCreatedAt = $first['createdAt']?->getTimestamp() ?? 0;
            $secondCreatedAt = $second['createdAt']?->getTimestamp() ?? 0;

            return $secondCreatedAt <=> $firstCreatedAt;
        });

        return $this->render('Frontend/Profile/index.html.twig', [
            'user' => $user,
            'profileForm' => $form->createView(),
            'reservations' => $reservations,
            'activeFilter' => $filter,
        ]);
    }

    /**
     * @return array{statusValue: string, statusLabel: string, statusClass: string, statusIcon: string}
     */
    private function statusPresentation(string $status): array
    {
        return match ($status) {
            'confirmed', 'confirmee' => [
                'statusValue' => $status,
                'statusLabel' => 'profile.status.confirmed',
                'statusClass' => 'profile-status--confirmed',
                'statusIcon' => 'check_circle',
            ],
            'pending', 'en_attente' => [
                'statusValue' => $status,
                'statusLabel' => 'profile.status.pending',
                'statusClass' => 'profile-status--pending',
                'statusIcon' => 'schedule',
            ],
            'cancelled', 'annulee' => [
                'statusValue' => $status,
                'statusLabel' => 'profile.status.cancelled',
                'statusClass' => 'profile-status--cancelled',
                'statusIcon' => 'cancel',
            ],
            'completed' => [
                'statusValue' => $status,
                'statusLabel' => 'profile.status.completed',
                'statusClass' => 'profile-status--completed',
                'statusIcon' => 'task_alt',
            ],
            default => [
                'statusValue' => $status,
                'statusLabel' => $status,
                'statusClass' => 'profile-status--other',
                'statusIcon' => 'info',
            ],
        };
    }
}