<?php

namespace App\Entity;

use App\Config\LocaleConfig;
use App\Repository\ExcursionImageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExcursionImageRepository::class)]
#[ORM\Table(name: 'excursion_image')]
class ExcursionImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Excursion $excursion = null;

    #[ORM\Column(name: 'image_path', length: 255)]
    private ?string $imagePath = null;

    #[ORM\Column(name: 'image_alt', length: 255, nullable: true)]
    private ?string $imageAlt = null;

    #[ORM\Column]
    private int $position = 0;

    #[ORM\Column(name: 'is_main')]
    private bool $isMain = false;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    /** @var Collection<int, ExcursionImageTranslation> */
    #[ORM\OneToMany(mappedBy: 'excursionImage', targetEntity: ExcursionImageTranslation::class, cascade: ['persist'], orphanRemoval: true)]
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

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(string $imagePath): static
    {
        $this->imagePath = $imagePath;
        return $this;
    }

    public function getImageAlt(): ?string
    {
        return $this->imageAlt;
    }

    public function setImageAlt(?string $imageAlt): static
    {
        $this->imageAlt = $imageAlt;
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

    public function isMain(): bool
    {
        return $this->isMain;
    }

    public function setIsMain(bool $isMain): static
    {
        $this->isMain = $isMain;
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

    /** @return Collection<int, ExcursionImageTranslation> */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(ExcursionImageTranslation $translation): static
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setExcursionImage($this);
        }
        return $this;
    }

    public function removeTranslation(ExcursionImageTranslation $translation): static
    {
        if ($this->translations->removeElement($translation) && $translation->getExcursionImage() === $this) {
            $translation->setExcursionImage(null);
        }
        return $this;
    }

    public function getTranslation(string $locale): ?ExcursionImageTranslation
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

    public function getCaptionTranslated(string $locale): ?string
    {
        return $this->getTranslation($locale)?->getCaption()
            ?? $this->getTranslation('fr')?->getCaption();
    }

    public function getAltTextTranslated(string $locale): ?string
    {
        return $this->getTranslation($locale)?->getAltText()
            ?? $this->getTranslation('fr')?->getAltText()
            ?? $this->imageAlt;
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }
}
