<?php

namespace App\Entity;

use App\Config\LocaleConfig;
use App\Repository\ExcursionBadgeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExcursionBadgeRepository::class)]
#[ORM\Table(name: 'excursion_badge')]
class ExcursionBadge
{
    public const BADGE_BEST_SELLER = 'best_seller';
    public const BADGE_AVAILABLE_NOW = 'available_now';
    public const BADGE_PRIVATE_GUIDED = 'private_guided';
    public const BADGE_CUSTOM = 'custom';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'badges')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Excursion $excursion = null;

    #[ORM\Column(name: 'badge_type', length: 20)]
    #[Assert\Choice(choices: [self::BADGE_BEST_SELLER, self::BADGE_AVAILABLE_NOW, self::BADGE_PRIVATE_GUIDED, self::BADGE_CUSTOM])]
    private string $badgeType = self::BADGE_CUSTOM;

    #[ORM\Column(name: 'is_active')]
    private bool $isActive = true;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    /** @var Collection<int, ExcursionBadgeTranslation> */
    #[ORM\OneToMany(mappedBy: 'excursionBadge', targetEntity: ExcursionBadgeTranslation::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExcursion(): ?Excursion
    {
        return $this->excursion;
    }

    public function setExcursion(?Excursion $excursion): static
    {
        $this->excursion = $excursion;
        return $this;
    }

    public function getBadgeType(): string
    {
        return $this->badgeType;
    }

    public function setBadgeType(string $badgeType): static
    {
        $this->badgeType = $badgeType;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
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

    /** @return Collection<int, ExcursionBadgeTranslation> */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(ExcursionBadgeTranslation $translation): static
    {
        if ($this->translations->contains($translation)) {
            return $this;
        }

        // Une seule traduction par locale (contrainte UNIQUE excursion_badge_id + locale) :
        // une traduction existante pour la même locale est remplacée, jamais dupliquée.
        foreach ($this->translations as $existing) {
            if ($existing->getLocale() === $translation->getLocale()) {
                $this->removeTranslation($existing);
                break;
            }
        }

        $this->translations->add($translation);
        $translation->setExcursionBadge($this);

        return $this;
    }

    public function removeTranslation(ExcursionBadgeTranslation $translation): static
    {
        if ($this->translations->removeElement($translation) && $translation->getExcursionBadge() === $this) {
            $translation->setExcursionBadge(null);
        }
        return $this;
    }

    public function getTranslation(string $locale): ?ExcursionBadgeTranslation
    {
        foreach ($this->translations as $translation) {
            if ($translation->getLocale() === $locale) {
                return $translation;
            }
        }

        if ($locale !== 'fr') {
            foreach ($this->translations as $translation) {
                if ($translation->getLocale() === 'fr') {
                    return $translation;
                }
            }
        }

        return $this->translations->first() ?: null;
    }

    public function getTextTranslated(string $locale): ?string
    {
        return $this->getTranslation($locale)?->getText()
            ?? $this->getTranslation('fr')?->getText();
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }
}
