<?php

namespace App\Controller\Frontend\Excursion;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ListeExcursionController extends AbstractController
{
    #[Route('/{_locale}/excursions', name: 'app_liste_excursion', requirements: ['_locale' => 'fr|en|it|es|de'], defaults: ['_locale' => 'fr'], methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('Frontend/Liste_excursion/index.html.twig', [
            'registrationForm' => $this->createForm(RegistrationFormType::class, new User())->createView()
        ]);
    }
}
