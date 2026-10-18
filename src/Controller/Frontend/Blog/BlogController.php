<?php

namespace App\Controller\Frontend\Blog;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\BlogArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    #[Route('/{_locale}/blog', name: 'app_blog_index', requirements: ['_locale' => 'fr|en|it|es|de'], defaults: ['_locale' => 'fr'], methods: ['GET'])]
    public function index(Request $request, BlogArticleRepository $blogArticleRepository): Response
    {
        $locale = $request->getLocale();
        $articles = $blogArticleRepository->findPublishedByLocale($locale);
        $featured = null;

        foreach ($articles as $article) {
            if ($article->isFeatured()) {
                $featured = $article;
                break;
            }
        }

        if ($featured === null && count($articles) > 0) {
            $featured = $articles[0];
        }

        return $this->render('Frontend/Blog/index.html.twig', [
            'registrationForm' => $this->createForm(RegistrationFormType::class, new User())->createView(),
            'articles' => $articles,
            'featuredArticle' => $featured,
            'locale' => $locale,
        ]);
    }

    #[Route('/{_locale}/blog/{slug}', name: 'app_blog_show', requirements: ['_locale' => 'fr|en|it|es|de', 'slug' => '[a-z0-9-]+'], defaults: ['_locale' => 'fr'], methods: ['GET'])]
    public function show(string $slug, Request $request, BlogArticleRepository $blogArticleRepository): Response
    {
        $locale = $request->getLocale();
        $article = $blogArticleRepository->findOnePublishedBySlug($slug, $locale);

        if (!$article) {
            throw $this->createNotFoundException('Article introuvable.');
        }

        $relatedArticles = $blogArticleRepository->findPublishedByLocale($locale);
        $relatedArticles = array_values(array_filter($relatedArticles, static fn ($related) => $related->getId() !== $article->getId()));

        return $this->render('Frontend/Blog/show.html.twig', [
            'article' => $article,
            'relatedArticles' => array_slice($relatedArticles, 0, 3),
            'locale' => $locale,
        ]);
    }
}

