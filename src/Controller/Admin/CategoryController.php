<?php

namespace App\Controller\Admin;

use App\Config\LocaleConfig;
use App\Entity\Category;
use App\Entity\CategoryTranslation;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/categories', name: 'admin_category_')]
class CategoryController extends AbstractController
{
    public function __construct(
        private CategoryRepository $repository,
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $categories = $this->repository->findBy([], ['position' => 'ASC']);
        
        return $this->render('Backend/Category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Auto-generate slug if not provided
            if (!$category->getSlug()) {
                $frenchName = $category->getNameTranslated('fr');
                if ($frenchName) {
                    $category->setSlug($this->slugger->slug($frenchName)->lower());
                }
            }
            
            $this->entityManager->persist($category);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'La catégorie a été créée avec succès.');
            
            return $this->redirectToRoute('admin_category_index');
        }
        
        return $this->render('Backend/Category/form.html.twig', [
            'form' => $form,
            'title' => 'Nouvelle catégorie',
            'category' => null,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Auto-generate slug if not provided
            if (!$category->getSlug()) {
                $frenchName = $category->getNameTranslated('fr');
                if ($frenchName) {
                    $category->setSlug($this->slugger->slug($frenchName)->lower());
                }
            }
            
            $this->entityManager->flush();
            
            $this->addFlash('success', 'La catégorie a été modifiée avec succès.');
            
            return $this->redirectToRoute('admin_category_index');
        }
        
        return $this->render('Backend/Category/form.html.twig', [
            'form' => $form,
            'title' => 'Modifier la catégorie',
            'category' => $category,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Category $category): Response
    {
        // Check if category has excursions
        if ($category->getExcursions()->count() > 0) {
            $this->addFlash('error', 'Impossible de supprimer cette catégorie car elle est utilisée par des excursions.');
            return $this->redirectToRoute('admin_category_index');
        }
        
        // Check if category has children
        if ($category->getChildren()->count() > 0) {
            $this->addFlash('error', 'Impossible de supprimer cette catégorie car elle contient des sous-catégories.');
            return $this->redirectToRoute('admin_category_index');
        }
        
        if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($category);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'La catégorie a été supprimée avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }
        
        return $this->redirectToRoute('admin_category_index');
    }

    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        
        $queryBuilder = $this->repository->createQueryBuilder('c')
            ->leftJoin('c.translations', 't')
            ->where('t.name LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('c.position', 'ASC')
            ->setMaxResults(20);
        
        $categories = $queryBuilder->getQuery()->getResult();
        
        $results = [];
        foreach ($categories as $category) {
            $results[] = [
                'id' => $category->getId(),
                'name' => $category->getNameTranslated('fr') ?? $category->getSlug(),
                'slug' => $category->getSlug(),
            ];
        }
        
        return new JsonResponse(['results' => $results]);
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['name']) || empty(trim($data['name']))) {
                return new JsonResponse(['error' => 'Le nom est requis'], Response::HTTP_BAD_REQUEST);
            }
            
            $category = new Category();
            $category->setSlug($this->slugger->slug($data['name'])->lower());
            $category->setPosition(0);
            $category->setIsActive(true);
            
            // Create French translation
            $translation = new CategoryTranslation();
            $translation->setLocale('fr');
            $translation->setName($data['name']);
            $translation->setDescription($data['description'] ?? null);
            $category->addTranslation($translation);
            
            // Create translations for other locales
            $locales = LocaleConfig::getSupportedLocales();
            foreach ($locales as $locale) {
                if ($locale !== 'fr') {
                    $translation = new CategoryTranslation();
                    $translation->setLocale($locale);
                    $translation->setName($data['name']); // Default to same name
                    $translation->setDescription($data['description'] ?? null);
                    $category->addTranslation($translation);
                }
            }
            
            $this->entityManager->persist($category);
            $this->entityManager->flush();
            
            return new JsonResponse([
                'id' => $category->getId(),
                'name' => $category->getNameTranslated('fr'),
                'slug' => $category->getSlug(),
            ]);
        } catch (\Exception $e) {
            error_log('Category creation error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return new JsonResponse(['error' => 'Erreur: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
