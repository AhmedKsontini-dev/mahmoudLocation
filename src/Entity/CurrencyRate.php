<?php

namespace App\Entity;

use App\Repository\CurrencyRateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CurrencyRateRepository::class)]
#[ORM\Table(name: 'currency_rate')]
class CurrencyRate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 3, unique: true)]
    #[Assert\Choice(choices: ['TND', 'EUR', 'USD'])]
    private string $code = 'TND';

    #[ORM\Column(length: 10)]
    private string $symbol = 'DT';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 6)]
    #[Assert\PositiveOrZero]
    private string $rate = '1.000000';

    public function getId(): ?int { return $this->id; }
    public function getCode(): string { return $this->code; }
    public function setCode(string $code): static { $this->code = strtoupper($code); return $this; }
    public function getSymbol(): string { return $this->symbol; }
    public function setSymbol(string $symbol): static { $this->symbol = $symbol; return $this; }
    public function getRate(): string { return $this->rate; }
    public function setRate(string $rate): static { $this->rate = $rate; return $this; }
}