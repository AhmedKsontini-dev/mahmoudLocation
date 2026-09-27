<?php

namespace App\Controller\Admin;

use App\Entity\Saison;
use App\Form\SaisonType;
use App\Repository\SaisonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/saisons', name: 'admin_saison_')]
class SaisonController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(SaisonRepository $repository): Response
    {
        return $this->render('Backend/Saison/index.html.twig', [
            'saisons' => $repository->findBy([], ['id' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $saison = new Saison();
        $form = $this->createForm(SaisonType::class, $saison);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->disableOtherActiveSeasons($saison, $entityManager);
            $entityManager->persist($saison);
            $entityManager->flush();
            return $this->redirectToRoute('admin_saison_index');
        }

        return $this->render('Backend/Saison/form.html.twig', [
            'form' => $form,
            'title' => 'Nouvelle saison',
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Saison $saison, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SaisonType::class, $saison);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->disableOtherActiveSeasons($saison, $entityManager);
            $entityManager->flush();
            return $this->redirectToRoute('admin_saison_index');
        }

        return $this->render('Backend/Saison/form.html.twig', [
            'form' => $form,
            'title' => 'Modifier la saison',
        ]);
    }

    private function disableOtherActiveSeasons(Saison $current, EntityManagerInterface $entityManager): void
    {
        if (!$current->isActif()) {
            return;
        }

        foreach ($entityManager->getRepository(Saison::class)->findBy(['actif' => true]) as $saison) {
            if ($saison !== $current) {
                $saison->setActif(false);
            }
        }
    }
}
