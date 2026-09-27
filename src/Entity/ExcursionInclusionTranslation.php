<?php

namespace App\Entity;

use App\Repository\ExcursionInclusionTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: ExcursionInclusionTranslationRepository::class)]
#[ORM\Table(name: 'excursion_inclusion_translation')]
#[ORM\UniqueConstraint(name: 'UNIQ_EXCURSION_INCLUSION_TRANSLATION_LOCALE', columns: ['excursion_inclusion_id', 'locale'])]
class ExcursionInclusionTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ExcursionInclusion $excursionInclusion = null;

    #[ORM\Column(length: 5)]
    #[Assert\Choice(callback: [\App\Config\LocaleConfig::class, 'getSupportedLocales'])]
    private string $locale = 'fr';

    #[ORM\Column(length: 500)]
    private ?string $text = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExcursionInclusion(): ?ExcursionInclusion
    {
        return $this->excursionInclusion;
    }

    public function setExcursionInclusion(?ExcursionInclusion $excursionInclusion): static
    {
        $this->excursionInclusion = $excursionInclusion;
        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): static
    {
        $this->locale = $locale;
        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $this->text = $text;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    #[Assert\Callback]
    public function validateFrenchFields(ExecutionContextInterface $context): void
    {
        if ($this->locale !== 'fr') {
            return;
        }

        if (trim((string) $this->text) === '') {
            $context->buildViolation('Ce champ est obligatoire en français.')
                ->atPath('text')
                ->addViolation();
        }
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }
}
