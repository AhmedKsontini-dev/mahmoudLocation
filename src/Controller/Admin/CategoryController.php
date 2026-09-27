<?php

namespace App\Controller\Admin;

use App\Config\LocaleConfig;
use App\Entity\Category;
use App\Entity\CategoryTranslation;
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
            $category->setActive(true);
            
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
