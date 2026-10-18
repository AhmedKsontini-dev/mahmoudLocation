<?php

namespace App\Tests;

use App\Entity\CurrencyRate;
use App\Repository\CurrencyRateRepository;
use App\Service\CurrencyService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;

class CurrencyServiceTest extends TestCase
{
    public function testFormatsAmountsUsingTheSelectedCurrencySymbolAndNumberStyle(): void
    {
        $repository = $this->createStub(CurrencyRateRepository::class);
        $service = new CurrencyService($repository, new RequestStack());

        $tnd = (new CurrencyRate())->setCode('TND')->setSymbol('DT')->setRate('1.000000');
        $eur = (new CurrencyRate())->setCode('EUR')->setSymbol('€')->setRate('0.300000');
        $usd = (new CurrencyRate())->setCode('USD')->setSymbol('$')->setRate('0.350000');

        self::assertSame('120,000 DT', $service->format(120, $tnd));
        self::assertSame('36,00 €', $service->format(120, $eur));
        self::assertSame('$42.00', $service->format(120, $usd));
    }
}
