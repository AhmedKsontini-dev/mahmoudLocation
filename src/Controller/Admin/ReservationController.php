<?php

namespace App\Controller\Admin;

use App\Entity\ReservationTransfere;
use App\Enum\ReservationStatus;
use App\Repository\ReservationTransfereRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/reservations', name: 'admin_reservation_')]
class ReservationController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(ReservationTransfereRepository $repository): Response
    {
        return $this->render('Backend/Reservation/index.html.twig', [
            'reservations' => $repository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/{id}/show', name: 'show', methods: ['GET'])]
    public function show(ReservationTransfere $reservation): Response
    {
        return $this->render('Backend/Reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ReservationTransfere $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $status = $request->request->get('status');
            if ($status && in_array($status, [ReservationStatus::PENDING->value, ReservationStatus::CONFIRMED->value, ReservationStatus::CANCELLED->value])) {
                $reservation->setStatus(ReservationStatus::from($status));
                $entityManager->flush();
                
                $this->addFlash('success', 'Le statut de la réservation a été mis à jour.');
                return $this->redirectToRoute('admin_reservation_index');
            }
        }

        return $this->render('Backend/Reservation/edit.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(ReservationTransfere $reservation, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($reservation);
        $entityManager->flush();
        
        $this->addFlash('success', 'La réservation a été supprimée avec succès.');
        
        return $this->redirectToRoute('admin_reservation_index');
    }
}
