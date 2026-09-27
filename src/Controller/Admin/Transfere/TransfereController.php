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
    public function index(TransfereRepository $repository): Response
    {
        return $this->render('Backend/Transfere/index.html.twig', ['transferts' => $repository->findAll()]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, TransfereRepository $repository): Response
    {
        $transfere = new Transfere();
        $this->ensureTranslations($transfere);
        $form = $this->createForm(TransfereType::class, $transfere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transfere->syncLegacyLocations();
            $transfere->setSlug($this->uniqueSlug($transfere, $slugger, $repository));
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
        $form = $this->createForm(TransfereType::class, $transfere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transfere->syncLegacyLocations();
            if ($transfere->getSlug() === null) {
                $transfere->setSlug($this->uniqueSlug($transfere, $slugger, $repository));
            }
            $entityManager->flush();
            return $this->redirectToRoute('admin_transfere_index');
        }

        return $this->render('Backend/Transfere/form.html.twig', [
            'form' => $form,
            'title' => 'Modifier le transfert',
            'localeLabels' => LocaleConfig::LABELS,
        ]);
    }

    private function ensureTranslations(Transfere $transfere): void
    {
        $locales = [];
        foreach ($transfere->getTranslations() as $translation) {
            $locales[$translation->getLocale()] = true;
        }

        foreach (LocaleConfig::SUPPORTED_LOCALES as $locale) {
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
}