<?php

namespace App\Controller\Admin;

use App\Entity\ReservationExcursion;
use App\Form\ReservationExcursionAdminType;
use App\Repository\ExcursionRepository;
use App\Repository\ReservationExcursionRepository;
use App\Service\ReservationMailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/reservations-excursions', name: 'admin_reservation_excursion_')]
#[IsGranted('ROLE_ADMIN')]
class ReservationExcursionController extends AbstractController
{
    /**
     * Nombre de réservations par page.
     * Configuré à 1 selon l'exigence spécifique du projet.
     */
    public const ITEMS_PER_PAGE = 1;

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        ReservationExcursionRepository $repository,
        ExcursionRepository $excursionRepository
    ): Response {
        $search = trim((string) $request->query->get('search', ''));
        $status = trim((string) $request->query->get('status', ''));
        $excursionId = $request->query->getInt('excursion', 0);
        $dateFromStr = trim((string) $request->query->get('date_from', ''));
        $dateToStr = trim((string) $request->query->get('date_to', ''));
        $page = max(1, $request->query->getInt('page', 1));

        $dateFrom = null;
        if ($dateFromStr !== '') {
            $parsedDate = \DateTime::createFromFormat('Y-m-d', $dateFromStr);
            if ($parsedDate !== false) {
                $dateFrom = $parsedDate;
            }
        }

        $dateTo = null;
        if ($dateToStr !== '') {
            $parsedDate = \DateTime::createFromFormat('Y-m-d', $dateToStr);
            if ($parsedDate !== false) {
                $dateTo = $parsedDate;
            }
        }

        $pagination = $repository->findPaginatedReservations(
            search: $search !== '' ? $search : null,
            statut: $status !== '' ? $status : null,
            excursionId: $excursionId > 0 ? $excursionId : null,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            page: $page,
            limit: self::ITEMS_PER_PAGE
        );

        $stats = $repository->getStats();
        $excursions = $excursionRepository->findBy([], ['id' => 'DESC']);

        $filterParams = array_filter([
            'search' => $search,
            'status' => $status,
            'excursion' => $excursionId > 0 ? $excursionId : null,
            'date_from' => $dateFromStr,
            'date_to' => $dateToStr,
        ]);

        return $this->render('Backend/ReservationExcursion/index.html.twig', [
            'reservations' => $pagination['items'],
            'pagination' => $pagination,
            'stats' => $stats,
            'excursions' => $excursions,
            'search' => $search,
            'currentStatus' => $status,
            'currentExcursion' => $excursionId,
            'dateFrom' => $dateFromStr,
            'dateTo' => $dateToStr,
            'filterParams' => $filterParams,
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(ReservationExcursion $reservation): Response
    {
        return $this->render('Backend/ReservationExcursion/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        ReservationExcursion $reservation,
        EntityManagerInterface $entityManager,
        ReservationMailerService $mailer
    ): Response {
        $redirectParams = $this->extractFilterParams($request);

        $previousStatus = $reservation->getStatut();

        $form = $this->createForm(ReservationExcursionAdminType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newStatus = $reservation->getStatut();
            $entityManager->flush();

            $emailFailure = null;
            if ($previousStatus !== $newStatus) {
                $emailFailure = $mailer->sendExcursionStatusChangeNotification($reservation, $previousStatus);
            }

            if ($emailFailure !== null) {
                $this->addFlash('warning', sprintf(
                    'La réservation #%d a été modifiée avec succès, mais l’e-mail n’a pas pu être envoyé. %s',
                    $reservation->getId(),
                    $emailFailure
                ));
            } else {
                $this->addFlash('success', sprintf(
                    'La réservation d’excursion #%d a été modifiée avec succès.',
                    $reservation->getId()
                ));
            }

            return $this->redirectToRoute('admin_reservation_excursion_show', array_merge(['id' => $reservation->getId()], $redirectParams));
        }

        return $this->render('Backend/ReservationExcursion/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
            'redirectParams' => $redirectParams,
        ]);
    }

    #[Route('/{id}/status', name: 'status', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function updateStatus(
        Request $request,
        ReservationExcursion $reservation,
        EntityManagerInterface $entityManager,
        ReservationMailerService $mailer
    ): Response {
        $token = (string) $request->request->get('_token');
        $isTokenValid = $this->isCsrfTokenValid('status_' . $reservation->getId(), $token)
            || $this->isCsrfTokenValid('reservation_excursion_status', $token);

        $isAjax = $request->isXmlHttpRequest()
            || $request->query->getBoolean('ajax')
            || str_contains((string) $request->headers->get('Accept'), 'application/json');

        $redirectParams = $this->extractFilterParams($request);

        if (!$isTokenValid) {
            if ($isAjax) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Jeton CSRF invalide ou expiré.',
                ], Response::HTTP_FORBIDDEN);
            }
            $this->addFlash('error', 'Sécurité : jeton CSRF invalide. Action annulée.');
            return $this->redirectToRoute('admin_reservation_excursion_index', $redirectParams);
        }

        $statusValue = trim((string) $request->request->get('status'));
        $allowedStatuses = [
            ReservationExcursion::STATUS_PENDING,
            ReservationExcursion::STATUS_CONFIRMED,
            ReservationExcursion::STATUS_CANCELLED,
        ];

        if (!in_array($statusValue, $allowedStatuses, true)) {
            if ($isAjax) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Statut non autorisé. Valeurs acceptées : en_attente, confirmee, annulee.',
                ], Response::HTTP_BAD_REQUEST);
            }
            $this->addFlash('error', 'Le statut demandé n\'est pas valide.');
            return $this->redirectToRoute('admin_reservation_excursion_index', $redirectParams);
        }

        $previousStatus = $reservation->getStatut();
        $reservation->setStatut($statusValue);
        $entityManager->flush();

        $emailFailure = null;
        if ($previousStatus !== $statusValue) {
            $emailFailure = $mailer->sendExcursionStatusChangeNotification($reservation, $previousStatus);
        }

        $label = $reservation->getStatutLabel();
        $badgeClass = $reservation->getStatutBadgeClass();

        if ($isAjax) {
            $message = sprintf('Le statut de la réservation #%d a été modifié en « %s ».', $reservation->getId(), $label);
            if ($emailFailure !== null) {
                $message .= ' ' . $emailFailure;
            }

            return new JsonResponse([
                'success' => true,
                'status' => $statusValue,
                'label' => $label,
                'badgeClass' => $badgeClass,
                'emailSent' => $previousStatus !== $statusValue && in_array($statusValue, [ReservationExcursion::STATUS_CONFIRMED, ReservationExcursion::STATUS_CANCELLED], true) ? ($emailFailure === null) : null,
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
            $this->addFlash('success', sprintf(
                'Le statut de la réservation #%d a été modifié en « %s ».',
                $reservation->getId(),
                $label
            ));
        }

        return $this->redirectToRoute('admin_reservation_excursion_index', $redirectParams);
    }

    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(
        Request $request,
        ReservationExcursion $reservation,
        EntityManagerInterface $entityManager
    ): Response {
        $redirectParams = $this->extractFilterParams($request);

        if (!$this->isCsrfTokenValid('delete' . $reservation->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Sécurité : jeton CSRF invalide. La suppression a été refusée.');
            return $this->redirectToRoute('admin_reservation_excursion_index', $redirectParams);
        }

        $reservationId = $reservation->getId();

        try {
            $entityManager->remove($reservation);
            $entityManager->flush();

            $this->addFlash('success', sprintf(
                'La réservation d’excursion #%d a été supprimée avec succès.',
                $reservationId
            ));
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Impossible de supprimer cette réservation en raison de dépendances en base de données.');
        }

        return $this->redirectToRoute('admin_reservation_excursion_index', $redirectParams);
    }

    /**
     * @return array<string, mixed>
     */
    private function extractFilterParams(Request $request): array
    {
        $params = [];
        $src = $request->isMethod('POST') ? $request->request : $request->query;

        if ($search = $src->get('search')) {
            $params['search'] = $search;
        }
        if ($status = $src->get('status')) {
            $params['status'] = $status;
        }
        if ($excursion = $src->get('excursion')) {
            $params['excursion'] = $excursion;
        }
        if ($dateFrom = $src->get('date_from')) {
            $params['date_from'] = $dateFrom;
        }
        if ($dateTo = $src->get('date_to')) {
            $params['date_to'] = $dateTo;
        }
        if ($page = $src->getInt('page', 1)) {
            if ($page > 1) {
                $params['page'] = $page;
            }
        }

        return $params;
    }
}
