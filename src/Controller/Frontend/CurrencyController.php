<?php

namespace App\Controller\Frontend;

use App\Service\CurrencyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class CurrencyController extends AbstractController
{
    #[Route('/currency/{code}', name: 'app_currency_set', requirements: ['code' => 'TND|EUR|USD'], methods: ['GET'])]
    public function set(string $code, CurrencyService $currencyService): JsonResponse
    {
        if (!$currencyService->setCurrent($code)) {
            throw $this->createNotFoundException();
        }

        return $this->json(['code' => strtoupper($code)]);
    }
}