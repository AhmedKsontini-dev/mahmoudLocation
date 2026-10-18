<?php

namespace App\Controller\Admin\Blog;

use App\Config\LocaleConfig;
use App\Entity\BlogArticle;
use App\Entity\BlogArticleImage;
use App\Entity\BlogArticleTranslation;
use App\Entity\BlogArticleVideo;
use App\Form\BlogArticleType;
use App\Repository\BlogArticleRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/blog/articles', name: 'admin_blog_article_')]
class BlogArticleController extends AbstractController
{
    public function __construct(
        private BlogArticleRepository $repository,
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $articles = $this->repository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('Backend/Blog/Article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $article = new BlogArticle();
        foreach (LocaleConfig::getSupportedLocales() as $locale) {
            $translation = new BlogArticleTranslation();
            $translation->setLocale($locale);
            $article->addTranslation($translation);
        }

        $form = $this->createForm(BlogArticleType::class, $article);
        $form->handleRequest($request);
        $this->addPostSizeError($request, $form);

        if ($form->isSubmitted()) {
            $this->prepareMainSlug($form, $article);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->handleMainImageUpload($form, $article);
                $this->handleGalleryImages($form, $article);
                $this->handleVideos($form, $article);
                $this->persistAndPrepareArticle($article);
                $this->entityManager->persist($article);
                $this->entityManager->flush();
                $this->addFlash('success', 'L’article du blog a été créé avec succès.');

                return $this->redirectToRoute('admin_blog_article_index');
            } catch (UniqueConstraintViolationException $exception) {
                $this->logger->error('Blog article creation violated a database unique constraint.', ['exception' => $exception]);
                $this->addFrenchSlugError($form, 'Ce slug existe déjà. Choisissez un autre slug.');
            } catch (\InvalidArgumentException $exception) {
                $this->logger->warning('Blog article creation was rejected.', ['exception' => $exception]);
                $form->addError(new FormError($exception->getMessage()));
            } catch (\Throwable $exception) {
                $this->logger->error('Blog article creation failed.', ['exception' => $exception]);
                $form->addError(new FormError('Impossible d’enregistrer l’article. Vérifiez les champs et médias puis réessayez. Les détails ont été consignés dans les logs.'));
            }
        }

        return $this->render('Backend/Blog/Article/form.html.twig', [
            'form' => $form,
            'title' => 'Nouvel article de blog',
            'article' => $article,
            'errorSummary' => $this->collectFormErrors($form->createView()),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BlogArticle $article): Response
    {
        $form = $this->createForm(BlogArticleType::class, $article);
        $form->handleRequest($request);
        $this->addPostSizeError($request, $form);

        if ($form->isSubmitted()) {
            $this->prepareMainSlug($form, $article);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->handleMainImageUpload($form, $article);
                $this->handleGalleryImages($form, $article);
                $this->handleVideos($form, $article);
                $this->persistAndPrepareArticle($article);
                $this->entityManager->flush();
                $this->addFlash('success', 'L’article du blog a été modifié avec succès.');

                return $this->redirectToRoute('admin_blog_article_index');
            } catch (UniqueConstraintViolationException $exception) {
                $this->logger->error('Blog article update violated a database unique constraint.', ['exception' => $exception]);
                $this->addFrenchSlugError($form, 'Ce slug existe déjà. Choisissez un autre slug.');
            } catch (\InvalidArgumentException $exception) {
                $this->logger->warning('Blog article update was rejected.', ['exception' => $exception]);
                $form->addError(new FormError($exception->getMessage()));
            } catch (\Throwable $exception) {
                $this->logger->error('Blog article update failed.', ['exception' => $exception]);
                $form->addError(new FormError('Impossible d’enregistrer les modifications. Vérifiez les champs et médias puis réessayez. Les détails ont été consignés dans les logs.'));
            }
        }

        return $this->render('Backend/Blog/Article/form.html.twig', [
            'form' => $form,
            'title' => 'Modifier l’article de blog',
            'article' => $article,
            'errorSummary' => $this->collectFormErrors($form->createView()),
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, BlogArticle $article): Response
    {
        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            foreach ($article->getGalleryImages() as $image) {
                $this->deletePhysicalFile($image->getImagePath());
            }
            foreach ($article->getVideos() as $video) {
                $this->deletePhysicalFile($video->getVideoPath());
            }
            $this->deletePhysicalFile($article->getImage());
            $this->entityManager->remove($article);
            $this->entityManager->flush();
            $this->addFlash('success', 'L’article du blog a été supprimé.');
        }

        return $this->redirectToRoute('admin_blog_article_index');
    }

    private function handleMainImageUpload(FormInterface $form, BlogArticle $article): void
    {
        $mainImageFile = $form->get('mainImageFile')->getData();
        $deleteMainImage = (bool) $form->get('deleteMainImage')->getData();

        if ($deleteMainImage) {
            $this->deletePhysicalFile($article->getImage());
            $article->setImage(null);
            return;
        }

        if (!$mainImageFile instanceof UploadedFile) {
            return;
        }

        $article->setImage($this->moveUploadedImage($mainImageFile, $article->getImage()));
    }

    private function handleGalleryImages(FormInterface $form, BlogArticle $article): void
    {
        $galleryForm = $form->get('galleryImages');
        $galleryImages = $article->getGalleryImages()->toArray();

        foreach ($galleryImages as $index => $galleryImage) {
            $entryForm = $galleryForm->get((string) $index);
            if (!$entryForm) {
                continue;
            }

            $deleteImage = (bool) $entryForm->get('delete')->getData();
            if ($deleteImage) {
                $this->deletePhysicalFile($galleryImage->getImagePath());
                $article->removeGalleryImage($galleryImage);
                continue;
            }

            $uploadedFile = $entryForm->get('imageFile')->getData();
            if ($uploadedFile instanceof UploadedFile) {
                $newPath = $this->moveUploadedImage($uploadedFile, $galleryImage->getImagePath());
                $galleryImage->setImagePath($newPath);
                $galleryImage->setImageAlt($entryForm->get('imageAlt')->getData() ?? $galleryImage->getImageAlt());
                $galleryImage->setPosition((int) ($entryForm->get('position')->getData() ?? 0));
            }

            if (!$galleryImage->getImagePath() && !$uploadedFile) {
                $article->removeGalleryImage($galleryImage);
            }
        }
    }

    private function handleVideos(FormInterface $form, BlogArticle $article): void
    {
        $videosForm = $form->get('videos');
        $videos = $article->getVideos()->toArray();

        foreach ($videos as $index => $video) {
            $entryForm = $videosForm->get((string) $index);
            if (!$entryForm) {
                continue;
            }

            $deleteVideo = (bool) $entryForm->get('delete')->getData();
            if ($deleteVideo) {
                $this->deletePhysicalFile($video->getVideoPath());
                $article->removeVideo($video);
                continue;
            }

            $uploadedFile = $entryForm->get('videoFile')->getData();
            $externalUrl = $entryForm->get('externalUrl')->getData();
            if ($uploadedFile instanceof UploadedFile) {
                $newPath = $this->moveUploadedVideo($uploadedFile, $video->getVideoPath());
                $video->setVideoPath($newPath);
            }

            $video->setTitle($entryForm->get('title')->getData() ?: $video->getTitle());
            $video->setExternalUrl($externalUrl ?: $video->getExternalUrl());
            $video->setPosition((int) ($entryForm->get('position')->getData() ?? 0));

            if (!$video->getVideoPath() && !$video->getExternalUrl()) {
                $article->removeVideo($video);
            }
        }
    }

    private function prepareMainSlug(FormInterface $form, BlogArticle $article): void
    {
        if ($article->getSlug() !== null) {
            return;
        }

        foreach ($article->getTranslations() as $index => $translation) {
            if ($translation->getLocale() !== 'fr' || trim((string) $translation->getTitle()) === '') {
                continue;
            }

            $slugSource = trim((string) $translation->getSlug()) ?: (string) $translation->getTitle();
            $slug = (string) $this->slugger->slug($slugSource)->lower();
            $existingArticle = $this->repository->findOneBy(['slug' => $slug]);

            if ($existingArticle !== null) {
                $form->get('translations')->get((string) $index)->get('slug')->addError(
                    new FormError('Ce slug existe déjà. Choisissez un autre slug.'),
                );
                return;
            }

            $translation->setSlug($slug);
            $article->setSlug($slug);
            return;
        }
    }

    private function persistAndPrepareArticle(BlogArticle $article): void
    {
        foreach ($article->getTranslations()->toArray() as $translation) {
            if (trim((string) $translation->getTitle()) === '' && !$this->hasMeaningfulContent($translation->getContent())) {
                $article->removeTranslation($translation);
                continue;
            }

            if (trim((string) $translation->getTitle()) === '' || !$this->hasMeaningfulContent($translation->getContent())) {
                throw new \InvalidArgumentException(sprintf(
                    'Le titre et le contenu sont obligatoires pour la traduction %s.',
                    $this->getFrenchLocaleLabel($translation->getLocale()),
                ));
            }

            $slug = $translation->getSlug() ?: $translation->getTitle();
            $translation->setSlug($this->slugger->slug($slug)->lower());
            $translation->setContent(BlogArticle::sanitizeHtml((string) $translation->getContent()));
        }

        $primaryTranslation = $article->getTranslations()->filter(static fn ($translation) => trim((string) $translation->getTitle()) !== '')->first();
        if (!$article->getSlug()) {
            $article->setSlug($this->slugger->slug($primaryTranslation?->getTitle() ?? $article->getTitleTranslated('fr') ?? 'article')->lower());
        }

        if (!$article->getPublishedAt() && $article->getStatus() === 'published') {
            $article->setPublishedAt(new \DateTime());
        }

        if ($article->getStatus() !== 'published' && $article->getPublishedAt()) {
            $article->setPublishedAt(null);
        }

        if (!$article->getReadTime()) {
            $article->setReadTime(5);
        }
    }

    private function hasMeaningfulContent(?string $content): bool
    {
        $text = html_entity_decode(strip_tags((string) $content), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(str_replace("\u{00A0}", ' ', $text)) !== '';
    }

    private function getFrenchLocaleLabel(string $locale): string
    {
        return match ($locale) {
            'fr' => 'français',
            'en' => 'anglais',
            'es' => 'espagnol',
            'de' => 'allemand',
            'it' => 'italien',
            default => $locale,
        };
    }

    private function addFrenchSlugError(FormInterface $form, string $message): void
    {
        foreach ($form->get('translations') as $index => $translationForm) {
            if ($translationForm->getData() instanceof BlogArticleTranslation && $translationForm->getData()->getLocale() === 'fr') {
                $translationForm->get('slug')->addError(new FormError($message));
                return;
            }
        }

        $form->addError(new FormError($message));
    }

    private function addPostSizeError(Request $request, FormInterface $form): void
    {
        if (!$request->isMethod('POST')) {
            return;
        }

        $requestSize = (int) $request->server->get('CONTENT_LENGTH', 0);
        $postLimit = $this->parseSizeToBytes((string) ini_get('post_max_size'));

        if ($requestSize > 0 && $postLimit > 0 && $requestSize > $postLimit) {
            $form->addError(new FormError('La requête dépasse la taille maximale autorisée par le serveur. Réduisez la taille des fichiers et réessayez.'));
        }
    }

    /**
     * @return list<array{target: string, field: string, message: string, section: string, locale: ?string}>
     */
    private function collectFormErrors(
        FormView $view,
        string $section = 'general',
        ?string $scope = null,
        ?string $parentName = null,
        ?string $locale = null,
    ): array
    {
        $name = (string) ($view->vars['name'] ?? '');
        if (in_array($name, ['mainImageFile', 'galleryImages', 'videos'], true)) {
            $section = 'media';
        } elseif ($name === 'translations') {
            $section = 'translations';
        }

        $data = $view->vars['data'] ?? null;
        if ($data instanceof BlogArticleTranslation) {
            $locale = $data->getLocale();
            $scope = ucfirst($this->getFrenchLocaleLabel($locale));
        } elseif ($parentName === 'galleryImages') {
            $scope = sprintf('Galerie d’images %d', (int) $name + 1);
        } elseif ($parentName === 'videos') {
            $scope = sprintf('Vidéo %d', (int) $name + 1);
        }

        $errors = [];
        $label = $view->vars['label'] ?? null;
        if (!is_string($label) || $label === '') {
            $label = ucfirst(preg_replace('/([a-z])([A-Z])/', '$1 $2', $name) ?: 'Formulaire');
        }

        foreach ($view->vars['errors'] ?? [] as $error) {
            $errors[] = [
                'target' => (string) ($view->vars['id'] ?? ''),
                'field' => $scope !== null ? $scope . ' — ' . $label : $label,
                'message' => $error->getMessage(),
                'section' => $section,
                'locale' => $locale,
            ];
        }

        foreach ($view as $child) {
            $errors = [...$errors, ...$this->collectFormErrors($child, $section, $scope, $name, $locale)];
        }

        return $errors;
    }

    private function moveUploadedImage(UploadedFile $file, ?string $oldRelativePath = null): string
    {
        $relativePath = $this->storeUploadedFile($file, 'public/uploads/blog', ['jpg', 'jpeg', 'png', 'webp'], ['image/jpeg', 'image/png', 'image/webp']);
        $this->deletePhysicalFile($oldRelativePath);

        return $relativePath;
    }

    private function moveUploadedVideo(UploadedFile $file, ?string $oldRelativePath = null): string
    {
        $relativePath = $this->storeUploadedFile($file, 'public/uploads/blog/videos', ['mp4', 'webm', 'mov', 'avi', 'm4v', 'mpeg'], ['video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo', 'video/mpeg']);
        $this->deletePhysicalFile($oldRelativePath);

        return $relativePath;
    }

    private function storeUploadedFile(UploadedFile $file, string $directory, array $allowedExtensions, array $allowedMimeTypes): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension());
        if (!in_array($extension, $allowedExtensions, true)) {
            throw new \InvalidArgumentException('Extension de fichier non autorisée.');
        }

        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            throw new \InvalidArgumentException('Type MIME du fichier non autorisé.');
        }

        $maxBytes = $this->resolveUploadLimitBytes();
        if ($file->getSize() > $maxBytes) {
            throw new \InvalidArgumentException('Le fichier est trop volumineux. Taille maximale autorisée : ' . $this->formatBytes($maxBytes));
        }

        $baseDirectory = $this->getParameter('kernel.project_dir') . '/' . $directory;
        if (!is_dir($baseDirectory)) {
            mkdir($baseDirectory, 0777, true);
        }

        $safeBaseName = $this->slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) ?: 'media')->lower();
        $filename = sprintf('%s-%s.%s', $safeBaseName, uniqid('', true), $extension);
        $file->move($baseDirectory, $filename);

        return '/' . str_replace('public/', '', $directory) . '/' . $filename;
    }

    private function deletePhysicalFile(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }

        $fullPath = $this->getParameter('kernel.project_dir') . '/public' . $relativePath;
        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }

    private function isImageFileValid(UploadedFile $file): bool
    {
        $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension());

        return in_array($file->getMimeType(), $allowedMime, true)
            && in_array($extension, $allowedExt, true)
            && $file->getSize() <= $this->resolveUploadLimitBytes();
    }

    private function resolveUploadLimitBytes(): int
    {
        $uploadMax = $this->parseSizeToBytes((string) ini_get('upload_max_filesize'));
        $postMax = $this->parseSizeToBytes((string) ini_get('post_max_size'));
        $max = min($uploadMax, $postMax);

        return $max > 0 ? $max : 5 * 1024 * 1024;
    }

    private function parseSizeToBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (int) substr($value, 0, -1);

        return match ($unit) {
            'k' => $number * 1024,
            'm' => $number * 1024 * 1024,
            'g' => $number * 1024 * 1024 * 1024,
            default => (int) $value,
        };
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $value = $bytes;
        $unitIndex = 0;

        while ($value >= 1024 && $unitIndex < count($units) - 1) {
            $value /= 1024;
            $unitIndex++;
        }

        return round($value, 2) . ' ' . $units[$unitIndex];
    }
}
