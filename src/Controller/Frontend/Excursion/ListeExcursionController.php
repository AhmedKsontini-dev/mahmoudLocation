<?php

namespace App\Controller\Frontend\Excursion;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ExcursionRepository;

class ListeExcursionController extends AbstractController
{
    #[Route('/{_locale}/excursions', name: 'app_liste_excursion', requirements: ['_locale' => 'fr|en|it|es|de'], defaults: ['_locale' => 'fr'], methods: ['GET'])]
    public function index(Request $request, ExcursionRepository $excursionRepository): Response
    {
        $page  = max(1, $request->query->getInt('page', 1));
        $limit = 2;

        // --- Lecture des filtres GET ---
        $filters = [
            'category'         => $request->query->get('category'),
            'destination'      => $request->query->get('destination'),
            'excursion'        => $request->query->get('excursion'),
            'duration_minutes' => $request->query->get('duration_minutes'),
            'min_price'        => $request->query->get('min_price'),
            'max_price'        => $request->query->get('max_price'),
        ];

        // --- Données paginées et filtrées ---
        $paginator       = $excursionRepository->getPaginatedExcursions($page, $limit, $filters);
        $totalExcursions = count($paginator);
        $totalPages      = (int) ceil($totalExcursions / $limit);

        // --- Données utilitaires pour les filtres ---
        $categories     = $excursionRepository->findCategoriesUsedByActiveExcursions();
        $categoryCounts = $excursionRepository->countPublishedExcursionsByCategory();
        $destinations   = $excursionRepository->findDestinationsUsedByActiveExcursions();
        $durations      = $excursionRepository->findDistinctDurations();
        $priceRange     = $excursionRepository->findPriceRange();

        return $this->render('Frontend/Liste_excursion/index.html.twig', [
            'registrationForm' => $this->createForm(RegistrationFormType::class, new User())->createView(),
            'excursions'       => $paginator,
            'totalExcursions'  => $totalExcursions,
            'totalPages'       => $totalPages,
            'currentPage'      => $page,
            'limit'            => $limit,
            '_locale'          => $request->getLocale(),
            // Filtres dynamiques
            'categories'       => $categories,
            'categoryCounts'   => $categoryCounts,
            'destinations'     => $destinations,
            'durations'        => $durations,
            'priceRange'       => $priceRange,
            // Filtres actifs (pour pré-sélectionner les contrôles)
            'activeFilters'    => $filters,
        ]);
    }
}

