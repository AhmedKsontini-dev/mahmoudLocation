<?php

namespace App\Controller\Admin;

use App\Config\LocaleConfig;
use App\Entity\Excursion;
use App\Entity\ExcursionTranslation;
use App\Entity\ExcursionItinerary;
use App\Entity\ExcursionItineraryTranslation;
use App\Entity\ExcursionImage;
use App\Entity\ExcursionImageTranslation;
use App\Entity\ExcursionFeature;
use App\Entity\ExcursionFeatureTranslation;
use App\Entity\ExcursionInclusion;
use App\Entity\ExcursionInclusionTranslation;
use App\Entity\ExcursionFaq;
use App\Entity\ExcursionFaqTranslation;
use App\Entity\ExcursionBadge;
use App\Entity\ExcursionBadgeTranslation;
use App\Form\ExcursionType;
use App\Repository\ExcursionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/excursions', name: 'admin_excursion_')]
class ExcursionController extends AbstractController
{
    public function __construct(
        private ExcursionRepository $repository,
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $search = $request->query->get('search', '');
        $status = $request->query->get('status', '');
        $category = $request->query->get('category', '');

        $queryBuilder = $this->repository->createQueryBuilder('e')
            ->leftJoin('e.category', 'c')
            ->leftJoin('e.translations', 't')
            ->addSelect('c', 't');

        if ($search) {
            $queryBuilder->andWhere('t.title LIKE :search OR e.slug LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($status) {
            $queryBuilder->andWhere('e.status = :status')
                ->setParameter('status', $status);
        }

        if ($category) {
            $queryBuilder->andWhere('e.category = :category')
                ->setParameter('category', $category);
        }

        $excursions = $queryBuilder
            ->orderBy('e.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('Backend/Excursion/index.html.twig', [
            'excursions' => $excursions,
            'search' => $search,
            'status' => $status,
            'category' => $category,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $excursion = new Excursion();
        $this->initializeTranslations($excursion);
        
        $form = $this->createForm(ExcursionType::class, $excursion, ['is_new' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    $this->entityManager->beginTransaction();
                    
                    $this->handleImageUploads($form, $excursion);
                    $this->generateSlugIfNeeded($excursion);
                    $this->cleanEmptyTranslations($excursion);
                    
                    $this->entityManager->persist($excursion);
                    $this->entityManager->flush();
                    
                    $this->entityManager->commit();
                    
                    $this->addFlash('success', 'L\'excursion a été créée avec succès.');
                    return $this->redirectToRoute('admin_excursion_index');
                } catch (\Exception $e) {
                    $this->entityManager->rollback();
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'enregistrement de l\'excursion: ' . $e->getMessage());
                    error_log('Excursion creation error: ' . $e->getMessage());
                }
            } else {
                $errorCount = $this->logFormErrors($form);
                
                $this->addFlash('error', sprintf('Veuillez corriger les %d erreur(s) dans le formulaire.', $errorCount));
            }
        }

        return $this->render('Backend/Excursion/form.html.twig', [
            'form' => $form,
            'excursion' => $excursion,
            'title' => 'Nouvelle excursion',
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Excursion $excursion): Response
    {
        $this->ensureTranslationsExist($excursion);
        
        $form = $this->createForm(ExcursionType::class, $excursion, ['is_new' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    $this->entityManager->beginTransaction();
                    
                    $this->handleImageUploads($form, $excursion);
                    $this->generateSlugIfNeeded($excursion);
                    $this->cleanEmptyTranslations($excursion);
                    
                    $this->entityManager->flush();
                    
                    $this->entityManager->commit();
                    
                    $this->addFlash('success', 'L\'excursion a été modifiée avec succès.');
                    return $this->redirectToRoute('admin_excursion_index');
                } catch (\Exception $e) {
                    $this->entityManager->rollback();
                    $this->addFlash('error', 'Une erreur est survenue lors de la modification de l\'excursion: ' . $e->getMessage());
                    error_log('Excursion edit error: ' . $e->getMessage());
                }
            } else {
                $errorCount = $this->logFormErrors($form);
                
                $this->addFlash('error', sprintf('Veuillez corriger les %d erreur(s) dans le formulaire.', $errorCount));
            }
        }

        return $this->render('Backend/Excursion/form.html.twig', [
            'form' => $form,
            'excursion' => $excursion,
            'title' => 'Modifier l\'excursion',
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Excursion $excursion): Response
    {
        return $this->render('Backend/Excursion/show.html.twig', [
            'excursion' => $excursion,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Excursion $excursion): Response
    {
        if ($this->isCsrfTokenValid('delete' . $excursion->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($excursion);
            $this->entityManager->flush();
            $this->addFlash('success', 'L\'excursion a été supprimée avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('admin_excursion_index');
    }

    private function initializeTranslations(Excursion $excursion): void
    {
        $locales = LocaleConfig::getSupportedLocales();
        foreach ($locales as $locale) {
            $translation = new ExcursionTranslation();
            $translation->setLocale($locale);
            $excursion->addTranslation($translation);
        }
    }

    private function initializeSubEntityTranslations($entity, $translationClass, $localeMethod): void
    {
        $locales = LocaleConfig::getSupportedLocales();
        foreach ($locales as $locale) {
            $translation = new $translationClass();
            $translation->setLocale($locale);
            $entity->$localeMethod($translation);
        }
    }

    private function ensureSubEntityTranslations($entity, $translations, $translationClass, $localeMethod): void
    {
        $locales = LocaleConfig::getSupportedLocales();
        $existingLocales = $translations->map(fn($t) => $t->getLocale())->toArray();

        foreach ($locales as $locale) {
            if (!in_array($locale, $existingLocales)) {
                $translation = new $translationClass();
                $translation->setLocale($locale);
                $entity->$localeMethod($translation);
            }
        }
    }

    private function ensureTranslationsExist(Excursion $excursion): void
    {
        $locales = LocaleConfig::getSupportedLocales();
        $existingLocales = $excursion->getTranslations()->map(fn($t) => $t->getLocale())->toArray();

        foreach ($locales as $locale) {
            if (!in_array($locale, $existingLocales)) {
                $translation = new ExcursionTranslation();
                $translation->setLocale($locale);
                $excursion->addTranslation($translation);
            }
        }
    }

    private function handleImageUploads(FormInterface $form, Excursion $excursion): void
    {
        foreach ($form->get('images') as $imageForm) {
            $image = $imageForm->getData();
            if (!$image instanceof ExcursionImage) {
                continue;
            }

            $imageFile = $imageForm->get('imageFile')->getData();
            
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/excursions';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $imageFile->move($uploadDir, $newFilename);
                $image->setImagePath('/uploads/excursions/' . $newFilename);
            } elseif (!$image->getImagePath()) {
                $excursion->removeImage($image);
            }
        }
    }

    private function logFormErrors(FormInterface $form): int
    {
        $errorCount = 0;
        foreach ($form->getErrors(true) as $error) {
            $errorCount++;
            $origin = $error->getOrigin();
            $propertyPath = $origin ? $origin->getPropertyPath() : 'unknown';

            error_log(sprintf(
                'Form error #%d - Path: %s, Message: %s',
                $errorCount,
                $propertyPath,
                $error->getMessage()
            ));
        }

        return $errorCount;
    }

    private function generateSlugIfNeeded(Excursion $excursion): void
    {
        if (!$excursion->getSlug()) {
            $frenchTranslation = $excursion->getTranslation('fr');
            if ($frenchTranslation && $frenchTranslation->getTitle()) {
                $slug = $this->slugger->slug($frenchTranslation->getTitle())->lower();
                $excursion->setSlug($slug);
            }
        }
    }

    private function cleanEmptyTranslations(Excursion $excursion): void
    {
        // 1. Remove non-French excursion translations that have empty titles
        foreach ($excursion->getTranslations() as $translation) {
            if ($translation->getLocale() !== 'fr' && empty(trim($translation->getTitle() ?? ''))) {
                $excursion->removeTranslation($translation);
                if ($this->entityManager->contains($translation)) {
                    $this->entityManager->remove($translation);
                }
            }
        }

        // 2. Remove empty itinerary translations
        foreach ($excursion->getItineraries() as $itinerary) {
            foreach ($itinerary->getTranslations() as $translation) {
                if (empty(trim($translation->getTitle() ?? ''))) {
                    $itinerary->removeTranslation($translation);
                    if ($this->entityManager->contains($translation)) {
                        $this->entityManager->remove($translation);
                    }
                }
            }
        }

        // 3. Remove empty feature translations
        foreach ($excursion->getFeatures() as $feature) {
            foreach ($feature->getTranslations() as $translation) {
                if (empty(trim($translation->getTitle() ?? ''))) {
                    $feature->removeTranslation($translation);
                    if ($this->entityManager->contains($translation)) {
                        $this->entityManager->remove($translation);
                    }
                }
            }
        }

        // 4. Remove empty inclusion translations
        foreach ($excursion->getInclusions() as $inclusion) {
            foreach ($inclusion->getTranslations() as $translation) {
                if (empty(trim($translation->getText() ?? ''))) {
                    $inclusion->removeTranslation($translation);
                    if ($this->entityManager->contains($translation)) {
                        $this->entityManager->remove($translation);
                    }
                }
            }
        }

        // 5. Remove empty FAQ translations
        foreach ($excursion->getFaqs() as $faq) {
            foreach ($faq->getTranslations() as $translation) {
                if (empty(trim($translation->getQuestion() ?? '')) && empty(trim($translation->getAnswer() ?? ''))) {
                    $faq->removeTranslation($translation);
                    if ($this->entityManager->contains($translation)) {
                        $this->entityManager->remove($translation);
                    }
                }
            }
        }

        // 6. Remove empty badge translations
        foreach ($excursion->getBadges() as $badge) {
            foreach ($badge->getTranslations() as $translation) {
                if (empty(trim($translation->getText() ?? ''))) {
                    $badge->removeTranslation($translation);
                    if ($this->entityManager->contains($translation)) {
                        $this->entityManager->remove($translation);
                    }
                }
            }
        }
    }
}
