<?php

namespace App\Controller\Frontend\Transfer;

use App\Entity\ReservationTransfere;
use App\Entity\Transfere;
use App\Enum\ReservationTransfereType;
use App\Form\ReservationTransfereFormType;
use App\Repository\TransfereRepository;
use App\Service\CurrencyService;
use App\Service\TransferePriceCalculator;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class TransferController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/{_locale}/transfers/search-by-route', name: 'app_transfer_search', requirements: ['_locale' => 'fr|en|it|es|de'], methods: ['GET'])]
    public function searchByRoute(Request $request, TransfereRepository $repository): Response
    {
        $transfer = $repository->findAvailableTransferByLocations(
            $request->query->getString('departure'),
            $request->query->getString('destination'),
            $request->getLocale(),
        );

        if (!$transfer instanceof Transfere) {
            throw $this->createNotFoundException('Aucun transfert disponible pour ce trajet.');
        }

        $parameters = ['_locale' => $request->getLocale(), 'slug' => $transfer->getSlug()];
        $pickupDate = $request->query->getString('pickupDate');
        if ($pickupDate !== '') {
            $parameters['pickupDate'] = $pickupDate;
        }

        return $this->redirectToRoute('app_transfer_show_locale', $parameters);
    }

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
        if ($request->isMethod('GET')) {
            $requestedDate = $request->query->get('pickupDate');
            if (is_string($requestedDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $requestedDate) === 1) {
                $pickupDate = DateTimeImmutable::createFromFormat('!Y-m-d', $requestedDate);
                $dateErrors = DateTimeImmutable::getLastErrors();
                if ($pickupDate !== false
                    && ($dateErrors === false || ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0))
                    && $pickupDate->format('Y-m-d') === $requestedDate
                ) {
                    $reservation->setPickupDate($pickupDate);
                }
            }
        }
        $form = $this->createForm(ReservationTransfereFormType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pickupDate = $reservation->getPickupDate();
            $returnDate = $reservation->getReturnPickupDate();
            $dates = $pickupDate === null ? [] : [$pickupDate];
            if ($reservation->getTransfereType() === ReservationTransfereType::RETURN && $returnDate !== null) {
                $dates[] = $returnDate;
            }

            try {
                $basePrice = $priceCalculator->getPrixPourDates($transfer, $dates);
                // Keep the legacy non-null database column populated while the UI uses one full-name field.
                $reservation->setPrenom($reservation->getNom());
                $currency = $currencyService->getCurrent();
                $reservation->setPrixTotal(number_format($currencyService->convert($basePrice, $currency), 2, '.', ''));
                $reservation->setDevise($currency->getCode());
                $entityManager->persist($reservation);
                $entityManager->flush();

                $this->addFlash('success', $this->translator->trans('transfer.form.success_message'));
                return $this->redirectToRoute('app_transfer_show_locale', ['_locale' => $request->getLocale(), 'slug' => $slug]);
            } catch (\DomainException $exception) {
                $form->addError(new FormError($this->translator->trans($exception->getMessage())));
            }
        }

        return $this->render('Frontend/Transfer/show.html.twig', [
            'transfer' => $transfer,
            'reservationForm' => $form->createView(),
            'periodesSaison' => $priceCalculator->getPeriodesActives(),
            'slug' => $slug,
            'locale' => $request->getLocale(),
        ]);
    }
}