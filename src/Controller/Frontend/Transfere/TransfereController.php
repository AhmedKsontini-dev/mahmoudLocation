<?php

namespace App\Controller\Frontend\Transfere;

use App\Entity\Transfere;
use App\Repository\TransfereRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

class TransfereController extends AbstractController
{
    #[Route('/transfert/{slug}', name: 'app_transfert_show', methods: ['GET'])]
    #[Route('/{_locale}/transfert/{slug}', name: 'app_transfert_show_locale', requirements: ['_locale' => 'fr|en|it|es|de'], methods: ['GET'])]
    public function show(string $slug, Request $request, TransfereRepository $repository): RedirectResponse
    {
        $transfere = $repository->findOneBy(['slug' => $slug, 'actif' => true]);
        if (!$transfere instanceof Transfere) {
            throw $this->createNotFoundException();
        }

        return $this->redirectToRoute('app_transfer_show_locale', [
            '_locale' => $request->getLocale(),
            'slug' => $transfere->getSlug(),
        ]);
    }
}