<?php

namespace App\Controller\Frontend\Excursion;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DetailExcursionController extends AbstractController
{
    #[Route('/{_locale}/excursion/detail', name: 'app_detail_excursion', requirements: ['_locale' => 'fr|en|it|es|de'], defaults: ['_locale' => 'fr'], methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('Frontend/Excursion/detail.html.twig', [
            'registrationForm' => $this->createForm(RegistrationFormType::class, new User())->createView()
        ]);
    }
}
