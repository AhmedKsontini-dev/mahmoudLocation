<?php

namespace App\Controller\Admin;

use App\Config\LocaleConfig;
use App\Entity\Category;
use App\Entity\CategoryTranslation;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
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
    /** Portée CSRF des créations de catégorie depuis l'AJAX. */
    public const CREATE_TOKEN_ID = 'admin_category_create';

    private const MAX_NAME_LENGTH = 255;

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

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
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

    #[Route('/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['POST'])]
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

    /**
     * Recherche JSON utilisée par le champ « Catégorie » du formulaire d'excursion.
     * Sans requête (q vide), la liste complète des catégories est renvoyée afin
     * d'être affichée dès l'ouverture du champ, sans obliger l'utilisateur à saisir du texte.
     */
    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->query->get('q', ''));

        $categories = '' === $query
            ? $this->repository->findBy([], ['position' => 'ASC', 'id' => 'ASC'])
            : $this->repository->searchByTranslatedName($query);

        $results = [];
        foreach ($categories as $category) {
            $results[] = $this->serializeCategory($category);
        }

        return new JsonResponse(['results' => $results]);
    }

    /**
     * Création JSON utilisée par le champ « Catégorie » : réutilise une catégorie existante
     * plutôt que de créer un doublon, et renvoie toujours du JSON (jamais une page HTML).
     */
    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = json_decode((string) $request->getContent(), true);

        if (!is_array($payload)) {
            return new JsonResponse(
                ['error' => 'Requête invalide : le corps JSON est absent ou mal formé.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $token = trim((string) $request->headers->get('X-CSRF-Token'));
        if ('' === $token) {
            $token = trim((string) ($payload['_token'] ?? ''));
        }

        if (!$this->isCsrfTokenValid(self::CREATE_TOKEN_ID, $token)) {
            return new JsonResponse(
                ['error' => 'Session expirée. Veuillez recharger la page puis réessayer.'],
                Response::HTTP_FORBIDDEN
            );
        }

        $name = trim((string) ($payload['name'] ?? ''));

        if ('' === $name) {
            return new JsonResponse(
                ['error' => 'Le nom de la catégorie est obligatoire.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if (mb_strlen($name) > self::MAX_NAME_LENGTH) {
            return new JsonResponse(
                ['error' => sprintf('Le nom de la catégorie ne peut pas dépasser %d caractères.', self::MAX_NAME_LENGTH)],
                Response::HTTP_BAD_REQUEST
            );
        }

        $existing = $this->repository->findOneByTranslatedName($name);
        if ($existing instanceof Category) {
            return $this->existingCategoryResponse($existing);
        }

        $description = isset($payload['description']) && '' !== trim((string) $payload['description'])
            ? trim((string) $payload['description'])
            : null;

        $category = new Category();
        $category->setSlug($this->uniqueSlug($this->slugger->slug($name)->lower()));
        $category->setPosition($this->repository->findMaxPosition() + 1);
        $category->setIsActive(true);

        foreach (LocaleConfig::getSupportedLocales() as $locale) {
            $translation = new CategoryTranslation();
            $translation->setLocale($locale);
            $translation->setName($name);
            $translation->setDescription($description);
            $category->addTranslation($translation);
        }

        try {
            $this->entityManager->persist($category);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $exception) {
            $this->entityManager->clear();

            // Un doublon a été créé entre-temps : on le réutilise au lieu d'échouer.
            $recovered = $this->repository->findOneByTranslatedName($name)
                ?? $this->repository->findOneBy(['slug' => $category->getSlug()]);

            if ($recovered instanceof Category) {
                return $this->existingCategoryResponse($recovered);
            }

            error_log('Category creation conflict: ' . $exception->getMessage());

            return new JsonResponse(
                ['error' => 'Cette catégorie existe déjà mais n\'a pas pu être récupérée. Réessayez.'],
                Response::HTTP_CONFLICT
            );
        } catch (\Throwable $exception) {
            $this->entityManager->clear();
            error_log('Category creation error: ' . $exception->getMessage() . "\n" . $exception->getTraceAsString());

            return new JsonResponse(
                ['error' => "La catégorie n'a pas pu être enregistrée. Réessayez ou signalez le problème à l'administrateur."],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return new JsonResponse($this->serializeCategory($category) + ['existed' => false], Response::HTTP_CREATED);
    }

    private function serializeCategory(Category $category): array
    {
        return [
            'id' => $category->getId(),
            'name' => $category->getNameTranslated('fr') ?? $category->getSlug(),
            'slug' => $category->getSlug(),
            'icon' => $category->getIcon(),
        ];
    }

    private function existingCategoryResponse(Category $category): JsonResponse
    {
        return new JsonResponse($this->serializeCategory($category) + ['existed' => true]);
    }

    private function uniqueSlug(string $baseSlug): string
    {
        $slug = $baseSlug;
        $suffix = 2;
        while (null !== $this->repository->findOneBy(['slug' => $slug])) {
            $slug = $baseSlug . '-' . $suffix++;
        }

        return $slug;
    }
}
