<?php

namespace App\Entity;

use App\Config\LocaleConfig;
use App\Repository\ExcursionInclusionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExcursionInclusionRepository::class)]
#[ORM\Table(name: 'excursion_inclusion')]
class ExcursionInclusion
{
    public const TYPE_INCLUDED = 'included';
    public const TYPE_EXCLUDED = 'excluded';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inclusions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Excursion $excursion = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::TYPE_INCLUDED, self::TYPE_EXCLUDED])]
    private string $type = self::TYPE_INCLUDED;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $icon = null;

    #[ORM\Column]
    private int $position = 0;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    /** @var Collection<int, ExcursionInclusionTranslation> */
    #[ORM\OneToMany(mappedBy: 'excursionInclusion', targetEntity: ExcursionInclusionTranslation::class, cascade: ['persist'], orphanRemoval: true)]
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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;
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

    /** @return Collection<int, ExcursionInclusionTranslation> */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(ExcursionInclusionTranslation $translation): static
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setExcursionInclusion($this);
        }
        return $this;
    }

    public function removeTranslation(ExcursionInclusionTranslation $translation): static
    {
        if ($this->translations->removeElement($translation) && $translation->getExcursionInclusion() === $this) {
            $translation->setExcursionInclusion(null);
        }
        return $this;
    }

    public function getTranslation(string $locale): ?ExcursionInclusionTranslation
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
