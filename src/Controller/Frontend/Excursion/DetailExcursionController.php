<?php

namespace App\Controller\Frontend\Excursion;

use App\Entity\Excursion;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\DestinationRepository;
use App\Repository\ExcursionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DetailExcursionController extends AbstractController
{
    #[Route('/{_locale}/excursion/{slug}', name: 'app_detail_excursion', requirements: ['_locale' => 'fr|en|it|es|de'], defaults: ['_locale' => 'fr'], methods: ['GET'])]
    public function index(string $slug, string $_locale, ExcursionRepository $excursionRepository, DestinationRepository $destinationRepository): Response
    {
        $excursion = $excursionRepository->findOneBy(['slug' => $slug, 'status' => Excursion::STATUS_ACTIVE]);

        if (!$excursion) {
            throw $this->createNotFoundException('L\'excursion demandée n\'existe pas ou n\'est plus disponible.');
        }

        return $this->render('Frontend/Excursion/detail.html.twig', [
            'registrationForm' => $this->createForm(RegistrationFormType::class, new User())->createView(),
            'excursion' => $excursion,
            '_locale' => $_locale,
            'destinations' => $destinationRepository->findActiveWithPublicExcursionCounts(),
        ]);
    }
}
