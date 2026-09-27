<?php

namespace App\Twig;

use App\Service\CurrencyService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class CurrencyExtension extends AbstractExtension
{
    public function __construct(private readonly CurrencyService $currencyService)
    {
    }

    public function getFilters(): array
    {
        return [new TwigFilter('price', [$this->currencyService, 'format'])];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('currency_options', [$this->currencyService, 'getCurrencies']),
            new TwigFunction('current_currency', [$this->currencyService, 'getCurrent']),
        ];
    }
}