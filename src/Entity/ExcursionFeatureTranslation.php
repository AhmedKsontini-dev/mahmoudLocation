<?php

namespace App\Entity;

use App\Repository\ExcursionFeatureTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: ExcursionFeatureTranslationRepository::class)]
#[ORM\Table(name: 'excursion_feature_translation')]
#[ORM\UniqueConstraint(name: 'UNIQ_EXCURSION_FEATURE_TRANSLATION_LOCALE', columns: ['excursion_feature_id', 'locale'])]
class ExcursionFeatureTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ExcursionFeature $excursionFeature = null;

    #[ORM\Column(length: 5)]
    #[Assert\Choice(callback: [\App\Config\LocaleConfig::class, 'getSupportedLocales'])]
    private string $locale = 'fr';

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $subtitle = null;

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

    public function getExcursionFeature(): ?ExcursionFeature
    {
        return $this->excursionFeature;
    }

    public function setExcursionFeature(?ExcursionFeature $excursionFeature): static
    {
        $this->excursionFeature = $excursionFeature;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): static
    {
        $this->subtitle = $subtitle;
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

        if (trim((string) $this->title) === '') {
            $context->buildViolation('Ce champ est obligatoire en français.')
                ->atPath('title')
                ->addViolation();
        }
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }
}
