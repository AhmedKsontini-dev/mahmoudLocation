<?php

namespace App\Controller\Admin\Blog;

use App\Config\LocaleConfig;
use App\Entity\BlogCategory;
use App\Entity\BlogCategoryTranslation;
use App\Form\BlogCategoryType;
use App\Repository\BlogCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/blog/categories', name: 'admin_blog_category_')]
class BlogCategoryController extends AbstractController
{
    public function __construct(
        private BlogCategoryRepository $repository,
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $categories = $this->repository->findBy([], ['position' => 'ASC']);

        return $this->render('Backend/Blog/Category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $category = new BlogCategory();
        foreach (LocaleConfig::getSupportedLocales() as $locale) {
            $translation = new BlogCategoryTranslation();
            $translation->setLocale($locale);
            $category->addTranslation($translation);
        }

        $form = $this->createForm(BlogCategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->prepareCategory($form, $category)) {
                try {
                    $this->entityManager->persist($category);
                    $this->entityManager->flush();
                    $this->addFlash('success', 'La catégorie du blog a été créée avec succès.');

                    return $this->redirectToRoute('admin_blog_category_index');
                } catch (\Throwable $exception) {
                    $this->logger->error('Blog category creation failed.', ['exception' => $exception]);
                    $form->addError(new FormError('Impossible d’enregistrer la catégorie. Vérifiez les informations et réessayez. Les détails ont été consignés dans les logs.'));
                }
            }
        }

        return $this->render('Backend/Blog/Category/form.html.twig', [
            'form' => $form,
            'title' => 'Nouvelle catégorie de blog',
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BlogCategory $category): Response
    {
        $form = $this->createForm(BlogCategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->prepareCategory($form, $category)) {
                try {
                    $this->entityManager->flush();
                    $this->addFlash('success', 'La catégorie du blog a été modifiée avec succès.');

                    return $this->redirectToRoute('admin_blog_category_index');
                } catch (\Throwable $exception) {
                    $this->logger->error('Blog category update failed.', ['exception' => $exception]);
                    $form->addError(new FormError('Impossible d’enregistrer les modifications. Vérifiez les informations et réessayez. Les détails ont été consignés dans les logs.'));
                }
            }
        }

        return $this->render('Backend/Blog/Category/form.html.twig', [
            'form' => $form,
            'title' => 'Modifier la catégorie du blog',
            'category' => $category,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, BlogCategory $category): Response
    {
        if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($category);
            $this->entityManager->flush();
            $this->addFlash('success', 'La catégorie du blog a été supprimée.');
        }

        return $this->redirectToRoute('admin_blog_category_index');
    }

    private function prepareCategory(FormInterface $form, BlogCategory $category): bool
    {
        $frenchName = null;
        $frenchTranslationForm = null;

        foreach ($category->getTranslations() as $index => $translation) {
            if ($translation->getLocale() === 'fr') {
                $frenchName = trim((string) $translation->getName());
                $frenchTranslationForm = $form->get('translations')->get((string) $index);
                break;
            }
        }

        if ($frenchName === null || $frenchName === '') {
            $frenchTranslationForm?->get('name')->addError(new FormError('Le nom de la catégorie en français est obligatoire.'));
            return false;
        }

        $slug = trim((string) $category->getSlug());
        if ($slug === '') {
            $slug = (string) $this->slugger->slug($frenchName)->lower();
        }

        $existingCategory = $this->repository->findOneBy(['slug' => $slug]);
        if ($existingCategory !== null && $existingCategory !== $category) {
            $form->get('slug')->addError(new FormError('Ce slug est déjà utilisé par une autre catégorie.'));
            return false;
        }

        $category->setSlug($slug);

        foreach ($category->getTranslations()->toArray() as $translation) {
            if (
                trim((string) $translation->getName()) === ''
                && trim((string) $translation->getDescription()) === ''
            ) {
                $category->removeTranslation($translation);
            } else {
                $translation->setName(trim((string) $translation->getName()));
            }
        }

        return true;
    }
}
