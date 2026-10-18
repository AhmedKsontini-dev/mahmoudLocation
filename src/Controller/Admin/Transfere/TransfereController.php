<?php

namespace App\Controller\Admin\Transfere;

use App\Config\LocaleConfig;
use App\Entity\Transfere;
use App\Entity\TransfereTranslation;
use App\Form\TransfereType;
use App\Repository\TransfereRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/transferts', name: 'admin_transfere_')]
class TransfereController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, TransfereRepository $repository): Response
    {
        $search = trim((string) $request->query->get('search', ''));
        $status = trim((string) $request->query->get('status', ''));
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        $pagination = $repository->findPaginatedTransfers($search, $status, $page, $limit);

        if ($page > $pagination['pages'] && $pagination['total'] > 0) {
            $redirectParams = ['page' => $pagination['pages']];
            if ($search !== '') {
                $redirectParams['search'] = $search;
            }
            if ($status !== '') {
                $redirectParams['status'] = $status;
            }
            return $this->redirectToRoute('admin_transfere_index', $redirectParams);
        }

        return $this->render('Backend/Transfere/index.html.twig', [
            'transferts' => $pagination['items'],
            'pagination' => $pagination,
            'search' => $search,
            'currentStatus' => $status,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, TransfereRepository $repository): Response
    {
        $transfere = new Transfere();
        $this->ensureTranslations($transfere);
        $form = $this->createForm(TransfereType::class, $transfere, ['show_description' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transfere->syncLegacyLocations();
            $transfere->setSlug($this->uniqueSlug($transfere, $slugger, $repository));
            $this->handleImageUpload($form, $transfere, $slugger);
            $entityManager->persist($transfere);
            $entityManager->flush();
            return $this->redirectToRoute('admin_transfere_index');
        }

        return $this->render('Backend/Transfere/form.html.twig', [
            'form' => $form,
            'title' => 'Nouveau transfert',
            'localeLabels' => LocaleConfig::LABELS,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Transfere $transfere, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, TransfereRepository $repository): Response
    {
        $this->ensureTranslations($transfere);

        // Stash non-FR/EN translations so the form never touches them
        $otherTranslations = [];
        foreach ($transfere->getTranslations() as $translation) {
            if (!in_array($translation->getLocale(), ['fr', 'en'], true)) {
                $otherTranslations[] = $translation;
            }
        }
        foreach ($otherTranslations as $t) {
            $transfere->getTranslations()->removeElement($t);
        }

        $form = $this->createForm(TransfereType::class, $transfere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Re-attach stashed translations before flushing
            foreach ($otherTranslations as $t) {
                $transfere->addTranslation($t);
            }
            $transfere->syncLegacyLocations();
            if ($transfere->getSlug() === null) {
                $transfere->setSlug($this->uniqueSlug($transfere, $slugger, $repository));
            }
            $this->handleImageUpload($form, $transfere, $slugger);
            $entityManager->flush();
            return $this->redirectToRoute('admin_transfere_index');
        }

        // Also re-attach for rendering (in case of validation errors or GET)
        foreach ($otherTranslations as $t) {
            $transfere->addTranslation($t);
        }

        return $this->render('Backend/Transfere/form.html.twig', [
            'form' => $form,
            'title' => 'Modifier le transfert',
            'localeLabels' => LocaleConfig::LABELS,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Transfere $transfere): Response
    {
        return $this->render('Backend/Transfere/show.html.twig', [
            'transfere' => $transfere,
            'localeLabels' => LocaleConfig::LABELS,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Transfere $transfere,
        EntityManagerInterface $entityManager,
        TransfereRepository $repository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $page = max(1, $request->request->getInt('page', $request->query->getInt('page', 1)));
        $search = trim((string) $request->request->get('search', $request->query->get('search', '')));
        $status = trim((string) $request->request->get('status', $request->query->get('status', '')));

        if ($this->isCsrfTokenValid('delete' . $transfere->getId(), (string) $request->request->get('_token'))) {
            try {
                $entityManager->remove($transfere);
                $entityManager->flush();
                $this->addFlash('success', 'Le transfert a été supprimé avec succès.');
            } catch (\Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException $e) {
                $this->addFlash('error', 'Impossible de supprimer le transfert car il est lié à des réservations.');
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        $totalRemaining = $repository->countFilteredTransfers($search, $status);
        $maxPage = max(1, (int) ceil($totalRemaining / 10));
        $targetPage = min($page, $maxPage);

        $redirectParams = [];
        if ($targetPage > 1) {
            $redirectParams['page'] = $targetPage;
        }
        if ($search !== '') {
            $redirectParams['search'] = $search;
        }
        if ($status !== '') {
            $redirectParams['status'] = $status;
        }

        return $this->redirectToRoute('admin_transfere_index', $redirectParams);
    }

    private function ensureTranslations(Transfere $transfere): void
    {
        $locales = [];
        foreach ($transfere->getTranslations() as $translation) {
            $locales[$translation->getLocale()] = true;
        }

        $desiredLocales = ['fr', 'en'];
        foreach ($desiredLocales as $locale) {
            if (isset($locales[$locale])) {
                continue;
            }

            $translation = (new TransfereTranslation())->setLocale($locale);
            if ($locale === 'fr') {
                $translation
                    ->setLieuDepart($transfere->getLieuDepart())
                    ->setLieuArrivee($transfere->getLieuArrivee());
            }
            $transfere->addTranslation($translation);
        }
    }

    private function uniqueSlug(Transfere $transfere, SluggerInterface $slugger, TransfereRepository $repository): string
    {
        $base = strtolower($slugger->slug($transfere->getLieuDepart().' '.$transfere->getLieuArrivee())->toString());
        $slug = $base;
        $suffix = 2;
        while (($existing = $repository->findOneBy(['slug' => $slug])) !== null && $existing !== $transfere) {
            $slug = $base.'-'.$suffix++;
        }
        return $slug;
    }

    private function handleImageUpload(\Symfony\Component\Form\FormInterface $form, Transfere $transfere, SluggerInterface $slugger): void
    {
        $imageFile = $form->get('imageFile')->getData();
        
        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/transferts';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if ($transfere->getImage()) {
                $oldImagePath = $this->getParameter('kernel.project_dir') . '/public' . $transfere->getImage();
                if (file_exists($oldImagePath) && is_file($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $imageFile->move($uploadDir, $newFilename);
            $transfere->setImage('/uploads/transferts/' . $newFilename);
        }
    }
}