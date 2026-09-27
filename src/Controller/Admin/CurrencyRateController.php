<?php

namespace App\Controller\Admin;

use App\Entity\CurrencyRate;
use App\Form\CurrencyRateType;
use App\Repository\CurrencyRateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/devises', name: 'admin_currency_')]
class CurrencyRateController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(CurrencyRateRepository $repository): Response
    {
        return $this->render('Backend/CurrencyRate/index.html.twig', [
            'currencies' => $repository->findBy([], ['id' => 'ASC']),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(CurrencyRate $currency, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CurrencyRateType::class, $currency);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('admin_currency_index');
        }

        return $this->render('Backend/CurrencyRate/form.html.twig', [
            'form' => $form,
            'title' => 'Modifier le taux '.$currency->getCode(),
        ]);
    }
}