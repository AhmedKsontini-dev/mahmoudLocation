<?php

namespace App\Controller\Admin;

use App\Repository\ContactRepository;
use App\Repository\CurrencyRateRepository;
use App\Repository\ReservationTransfereRepository;
use App\Repository\SaisonRepository;
use App\Repository\TransfereRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin_')]
class DashboardController extends AbstractController
{
    #[Route('', name: 'dashboard', methods: ['GET'])]
    public function index(
        TransfereRepository $transfereRepository,
        SaisonRepository $saisonRepository,
        CurrencyRateRepository $currencyRateRepository,
        ContactRepository $contactRepository,
        ReservationTransfereRepository $reservationRepository
    ): Response {
        // Real statistics from actual entities
        $stats = [
            'transferts' => [
                'total' => $transfereRepository->count([]),
                'actifs' => $transfereRepository->count(['actif' => true]),
                'inactifs' => $transfereRepository->count(['actif' => false]),
            ],
            'saisons' => [
                'total' => $saisonRepository->count([]),
                'active' => $saisonRepository->count(['actif' => true]),
            ],
            'devises' => [
                'total' => $currencyRateRepository->count([]),
            ],
            'contacts' => [
                'total' => $contactRepository->count([]),
            ],
            'reservations' => [
                'total' => $reservationRepository->count([]),
                'pending' => $reservationRepository->count(['status' => 'pending']),
                'confirmed' => $reservationRepository->count(['status' => 'confirmed']),
                'cancelled' => $reservationRepository->count(['status' => 'cancelled']),
            ],
        ];

        return $this->render('Backend/Dashboard/index.html.twig', [
            'stats' => $stats,
        ]);
    }
}
