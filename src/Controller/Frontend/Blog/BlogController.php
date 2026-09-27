<?php

namespace App\Controller\Frontend\Blog;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    #[Route('/{_locale}/blog', name: 'app_blog_index', requirements: ['_locale' => 'fr|en|it|es|de'], defaults: ['_locale' => 'fr'], methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('Frontend/Blog/index.html.twig', [
            'registrationForm' => $this->createForm(RegistrationFormType::class, new User())->createView()
        ]);
    }
}
