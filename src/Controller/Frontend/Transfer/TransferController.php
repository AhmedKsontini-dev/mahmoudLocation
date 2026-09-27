<?php

namespace App\Controller\Frontend\Transfer;

use App\Entity\ReservationTransfere;
use App\Entity\Transfere;
use App\Enum\ReservationTransfereType;
use App\Form\ReservationTransfereFormType;
use App\Repository\TransfereRepository;
use App\Service\CurrencyService;
use App\Service\TransferePriceCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TransferController extends AbstractController
{
    #[Route('/transfers/{slug}', name: 'app_transfer_show', requirements: ['slug' => '[a-z0-9-]+'], methods: ['GET', 'POST'])]
    #[Route('/{_locale}/transfers/{slug}', name: 'app_transfer_show_locale', requirements: ['_locale' => 'fr|en|it|es|de', 'slug' => '[a-z0-9-]+'], methods: ['GET', 'POST'])]
    public function show(string $slug, Request $request, TransfereRepository $repository, TransferePriceCalculator $priceCalculator, CurrencyService $currencyService, EntityManagerInterface $entityManager): Response
    {
        $transfer = $repository->findOneBy(['slug' => $slug, 'actif' => true]);
        if (!$transfer instanceof Transfere) {
            throw $this->createNotFoundException('Transfert introuvable.');
        }

        $reservation = new ReservationTransfere();
        $reservation
            ->setTransfere($transfer)
            ->setPickupLocation($transfer->getLieuDepartTranslated($request->getLocale()) ?? '')
            ->setDropoffLocation($transfer->getLieuArriveeTranslated($request->getLocale()) ?? '')
            ->setTransfereType(ReservationTransfereType::ONE_WAY);
        $form = $this->createForm(ReservationTransfereFormType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Keep the legacy non-null database column populated while the UI uses one full-name field.
            $reservation->setPrenom($reservation->getNom());
            $basePrice = $priceCalculator->getPrixPourDate($transfer, $reservation->getPickupDate());
            if ($reservation->getTransfereType() === ReservationTransfereType::RETURN) {
                $basePrice = (string) ((float) $basePrice * 2);
            }
            $currency = $currencyService->getCurrent();
            $reservation->setPrixTotal((string) $currencyService->convert($basePrice, $currency));
            $reservation->setDevise($currency->getCode());
            $entityManager->persist($reservation);
            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('transfer.form.success_message'));
            return $this->redirectToRoute('app_transfer_show_locale', ['_locale' => $request->getLocale(), 'slug' => $slug]);
        }

        return $this->render('Frontend/Transfer/show.html.twig', [
            'transfer' => $transfer,
            'reservationForm' => $form->createView(),
            'prix' => $priceCalculator->getPrixPourDate($transfer),
            'slug' => $slug,
            'locale' => $request->getLocale(),
        ]);
    }
}