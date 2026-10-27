<?php

namespace App\Controller\Frontend\Excursion;

use App\Entity\Excursion;
use App\Entity\ReservationExcursion;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\ReservationExcursionType;
use App\Repository\DestinationRepository;
use App\Repository\ExcursionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DetailExcursionController extends AbstractController
{
    #[Route('/{_locale}/excursion/{slug}', name: 'app_detail_excursion', requirements: ['_locale' => 'fr|en'], defaults: ['_locale' => 'fr'], methods: ['GET', 'POST'])]
    public function index(
        string $slug,
        string $_locale,
        ExcursionRepository $excursionRepository,
        DestinationRepository $destinationRepository,
        EntityManagerInterface $entityManager,
        Request $request,
    ): Response {
        $excursion = $excursionRepository->findOneBy(['slug' => $slug, 'status' => Excursion::STATUS_ACTIVE]);

        if (!$excursion) {
            throw $this->createNotFoundException('L\'excursion demandée n\'existe pas ou n\'est plus disponible.');
        }

        $reservation = new ReservationExcursion();
        $reservation
            ->setExcursion($excursion)
            ->setStatut(ReservationExcursion::STATUS_PENDING);

        $bookingForm = $this->createForm(ReservationExcursionType::class, $reservation, ['excursion' => $excursion]);
        $bookingForm->handleRequest($request);

        if ($bookingForm->isSubmitted() && $bookingForm->isValid()) {
            $reservation
                ->setExcursion($excursion)
                ->setStatut(ReservationExcursion::STATUS_PENDING);

            // Montant total recalculé côté serveur depuis les prix en base : le navigateur n'est jamais crédité.
            $prixTotal = $reservation->getPrixTotalCalcule();

            try {
                $entityManager->persist($reservation);
                $entityManager->flush();

                $this->addFlash('reservation_success', [
                    'excursion' => $excursion->getTitleTranslated($_locale) ?? 'Excursion',
                    'nom' => $reservation->getNom(),
                    'prenom' => $reservation->getPrenom(),
                    'date' => $reservation->getDateExcursion()?->format('d/m/Y'),
                    'heure' => $reservation->getHeureDepart()?->format('H:i'),
                    'adultes' => $reservation->getNombreAdultes(),
                    'enfants' => $reservation->getNombreEnfants(),
                    'total' => $prixTotal,
                    'lieu' => $reservation->getLieuPriseEnCharge(),
                ]);

                return $this->redirectToRoute('app_detail_excursion', [
                    'slug' => $slug,
                    '_locale' => $_locale,
                ]);
            } catch (\Throwable $e) {
                $this->addFlash('reservation_error', 'excursion.detail.booking_error');
            }
        }

        return $this->render('Frontend/Excursion/detail.html.twig', [
            'registrationForm' => $this->createForm(RegistrationFormType::class, new User())->createView(),
            'bookingForm' => $bookingForm->createView(),
            'excursion' => $excursion,
            '_locale' => $_locale,
            'destinations' => $destinationRepository->findActiveWithPublicExcursionCounts(),
        ]);
    }
}
