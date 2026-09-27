<?php

namespace App\Service;

use App\Entity\CurrencyRate;
use App\Repository\CurrencyRateRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CurrencyService
{
    public const SESSION_KEY = 'location_currency';
    public const DEFAULT_CODE = 'TND';

    public function __construct(
        private readonly CurrencyRateRepository $repository,
        private readonly RequestStack $requestStack,
    ) {
    }

    /** @return list<CurrencyRate> */
    public function getCurrencies(): array
    {
        return $this->repository->findBy([], ['id' => 'ASC']);
    }

    public function getCurrent(): CurrencyRate
    {
        $code = $this->requestStack->getSession()->get(self::SESSION_KEY, self::DEFAULT_CODE);
        return $this->repository->findOneBy(['code' => $code])
            ?? $this->repository->findOneBy(['code' => self::DEFAULT_CODE]);
    }

    public function setCurrent(string $code): bool
    {
        $currency = $this->repository->findOneBy(['code' => strtoupper($code)]);
        if (!$currency) {
            return false;
        }

        $this->requestStack->getSession()->set(self::SESSION_KEY, $currency->getCode());
        return true;
    }

    public function convert(string|int|float $amount, ?CurrencyRate $currency = null): float
    {
        $currency ??= $this->getCurrent();
        return round((float) $amount * (float) $currency->getRate(), 2);
    }

    public function format(string|int|float $amount, ?CurrencyRate $currency = null): string
    {
        $currency ??= $this->getCurrent();
        return number_format($this->convert($amount, $currency), 2, ',', ' ').' '.$currency->getCode();
    }
}