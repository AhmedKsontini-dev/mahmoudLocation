<?php

namespace App\Controller\Admin;

use App\Entity\ReservationTransfere;
use App\Enum\ReservationStatus;
use App\Repository\ReservationTransfereRepository;
use App\Service\ReservationMailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/reservations', name: 'admin_reservation_')]
#[IsGranted('ROLE_ADMIN')]
class ReservationController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, ReservationTransfereRepository $repository): Response
    {
        $search = trim((string) $request->query->get('search', ''));
        $status = trim((string) $request->query->get('status', ''));
        $type = trim((string) $request->query->get('type', ''));
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        $pagination = $repository->findPaginatedReservations($search, $status, $type, $page, $limit);

        $stats = [
            'total' => $repository->count([]),
            'pending' => $repository->countByStatus(ReservationStatus::PENDING),
            'confirmed' => $repository->countByStatus(ReservationStatus::CONFIRMED),
            'cancelled' => $repository->countByStatus(ReservationStatus::CANCELLED),
        ];

        return $this->render('Backend/Reservation/index.html.twig', [
            'reservations' => $pagination['items'],
            'pagination' => $pagination,
            'search' => $search,
            'currentStatus' => $status,
            'currentType' => $type,
            'stats' => $stats,
        ]);
    }

    #[Route('/{id}/status', name: 'status', methods: ['POST'])]
    public function updateStatus(
        Request $request,
        ReservationTransfere $reservation,
        EntityManagerInterface $entityManager,
        ReservationMailerService $mailer
    ): Response {
        $token = (string) $request->request->get('_token');
        $isTokenValid = $this->isCsrfTokenValid('status_' . $reservation->getId(), $token)
            || $this->isCsrfTokenValid('reservation_status', $token);

        $isAjax = $request->isXmlHttpRequest()
            || $request->query->getBoolean('ajax')
            || str_contains((string) $request->headers->get('Accept'), 'application/json');

        if (!$isTokenValid) {
            if ($isAjax) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Token CSRF invalide ou expiré.',
                ], Response::HTTP_FORBIDDEN);
            }
            $this->addFlash('error', 'Sécurité : token CSRF invalide. Action annulée.');
            return $this->redirectToRoute('admin_reservation_index');
        }

        $statusValue = (string) $request->request->get('status');
        $newStatus = ReservationStatus::tryFrom($statusValue);

        if ($newStatus === null) {
            if ($isAjax) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Statut non reconnu. Les valeurs valides sont : confirmed, pending, cancelled.',
                ], Response::HTTP_BAD_REQUEST);
            }
            $this->addFlash('error', 'Le statut demandé n\'est pas valide.');
            return $this->redirectToRoute('admin_reservation_index');
        }

        $previousStatus = $reservation->getStatus();
        $reservation->setStatus($newStatus);
        $entityManager->flush();

        $notificationRequired = $previousStatus !== $newStatus
            && in_array($newStatus, [ReservationStatus::CONFIRMED, ReservationStatus::CANCELLED], true);
        $emailFailure = $mailer->sendStatusChangeNotification($reservation, $previousStatus);

        $label = match ($newStatus) {
            ReservationStatus::CONFIRMED => 'Confirmée',
            ReservationStatus::PENDING => 'En attente',
            ReservationStatus::CANCELLED => 'Annulée',
        };

        if ($isAjax) {
            $message = sprintf('Le statut de la réservation #%d a été modifié en « %s ».', $reservation->getId(), $label);
            if ($emailFailure !== null) {
                $message .= ' ' . $emailFailure;
            }

            return new JsonResponse([
                'success' => true,
                'status' => $newStatus->value,
                'label' => $label,
                'badgeClass' => 'badge-' . $newStatus->value,
                'emailSent' => $notificationRequired ? $emailFailure === null : null,
                'message' => $message,
            ]);
        }

        if ($emailFailure !== null) {
            $this->addFlash('warning', sprintf(
                'Le statut de la réservation #%d a été modifié en « %s », mais l’e-mail n’a pas pu être envoyé. %s',
                $reservation->getId(),
                $label,
                $emailFailure
            ));
        } else {
            $this->addFlash('success', sprintf('Le statut de la réservation #%d a été modifié en « %s ».', $reservation->getId(), $label));
        }

        $redirectParams = [];
        if ($request->query->get('search')) {
            $redirectParams['search'] = $request->query->get('search');
        }
        if ($request->query->get('status')) {
            $redirectParams['status'] = $request->query->get('status');
        }
        if ($request->query->get('type')) {
            $redirectParams['type'] = $request->query->get('type');
        }
        if ($request->query->getInt('page', 1) > 1) {
            $redirectParams['page'] = $request->query->getInt('page');
        }

        return $this->redirectToRoute('admin_reservation_index', $redirectParams);
    }

    #[Route('/{id}/show', name: 'show', methods: ['GET'])]
    public function show(ReservationTransfere $reservation): Response
    {
        return $this->render('Backend/Reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/voucher', name: 'voucher', methods: ['GET'])]
    public function voucher(ReservationTransfere $reservation): Response
    {
        return $this->render('Backend/Reservation/voucher.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ReservationTransfere $reservation, EntityManagerInterface $entityManager, ReservationMailerService $mailer): Response
    {
        $redirectParams = [];
        if ($request->query->get('search')) {
            $redirectParams['search'] = $request->query->get('search');
        }
        if ($request->query->get('status')) {
            $redirectParams['status'] = $request->query->get('status');
        }
        if ($request->query->get('type')) {
            $redirectParams['type'] = $request->query->get('type');
        }
        if ($request->query->getInt('page', 1) > 1) {
            $redirectParams['page'] = $request->query->getInt('page');
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('edit' . $reservation->getId(), (string) $request->request->get('_token'))) {
                $this->addFlash('error', 'Sécurité : token CSRF invalide. Action annulée.');
                return $this->redirectToRoute('admin_reservation_index', $redirectParams);
            }

            $status = $request->request->get('status');
            $newStatus = ReservationStatus::tryFrom((string) $status);

            if ($newStatus !== null) {
                $previousStatus = $reservation->getStatus();
                $reservation->setStatus($newStatus);
                $entityManager->flush();

                $emailFailure = $mailer->sendStatusChangeNotification($reservation, $previousStatus);

                if ($emailFailure !== null) {
                    $this->addFlash('warning', sprintf(
                        'Le statut de la réservation #%d a été mis à jour, mais l’e-mail n’a pas pu être envoyé. %s',
                        $reservation->getId(),
                        $emailFailure
                    ));
                } else {
                    $this->addFlash('success', sprintf('Le statut de la réservation #%d a été mis à jour.', $reservation->getId()));
                }
                return $this->redirectToRoute('admin_reservation_index', $redirectParams);
            }

            $this->addFlash('error', 'Statut sélectionné non valide.');
        }

        return $this->render('Backend/Reservation/edit.html.twig', [
            'reservation' => $reservation,
            'redirectParams' => $redirectParams,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, ReservationTransfere $reservation, EntityManagerInterface $entityManager): Response
    {
        $redirectParams = [];
        if ($request->request->get('search')) {
            $redirectParams['search'] = $request->request->get('search');
        }
        if ($request->request->get('status')) {
            $redirectParams['status'] = $request->request->get('status');
        }
        if ($request->request->get('type')) {
            $redirectParams['type'] = $request->request->get('type');
        }
        if ($request->request->getInt('page', 1) > 1) {
            $redirectParams['page'] = $request->request->getInt('page');
        }

        if (!$this->isCsrfTokenValid('delete' . $reservation->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide. La suppression a été refusée.');
            return $this->redirectToRoute('admin_reservation_index', $redirectParams);
        }

        $reservationId = $reservation->getId();

        try {
            $entityManager->remove($reservation);
            $entityManager->flush();

            $this->addFlash('success', sprintf('La réservation #%d a été supprimée avec succès.', $reservationId));
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Impossible de supprimer cette réservation en raison de dépendances en base de données.');
        }

        return $this->redirectToRoute('admin_reservation_index', $redirectParams);
    }
}
