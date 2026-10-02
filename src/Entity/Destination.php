<?php

namespace App\Entity;

use App\Config\LocaleConfig;
use App\Repository\DestinationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DestinationRepository::class)]
#[ORM\Table(name: 'destination')]
#[ORM\UniqueConstraint(name: 'UNIQ_DESTINATION_SLUG', columns: ['slug'])]
#[UniqueEntity(fields: ['slug'], message: 'Cette destination possède déjà ce slug.')]
class Destination
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $region = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 8, nullable: true)]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 8, nullable: true)]
    private ?string $longitude = null;

    #[ORM\Column(name: 'is_active')]
    private bool $isActive = true;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    /** @var Collection<int, DestinationTranslation> */
    #[ORM\OneToMany(mappedBy: 'destination', targetEntity: DestinationTranslation::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $translations;

    /** @var Collection<int, Excursion> */
    #[ORM\OneToMany(mappedBy: 'destination', targetEntity: Excursion::class)]
    private Collection $excursions;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->excursions = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): static
    {
        $this->region = $region;
        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): static
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): static
    {
        $this->longitude = $longitude;
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
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

    /** @return Collection<int, DestinationTranslation> */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    /** @return Collection<int, Excursion> */
    public function getExcursions(): Collection
    {
        return $this->excursions;
    }

    public function addTranslation(DestinationTranslation $translation): static
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setDestination($this);
        }
        return $this;
    }

    public function removeTranslation(DestinationTranslation $translation): static
    {
        if ($this->translations->removeElement($translation) && $translation->getDestination() === $this) {
            $translation->setDestination(null);
        }
        return $this;
    }

    public function addExcursion(Excursion $excursion): static
    {
        if (!$this->excursions->contains($excursion)) {
            $this->excursions->add($excursion);
            $excursion->setDestination($this);
        }
        return $this;
    }

    public function removeExcursion(Excursion $excursion): static
    {
        if ($this->excursions->removeElement($excursion) && $excursion->getDestination() === $this) {
            $excursion->setDestination(null);
        }
        return $this;
    }

    public function getTranslation(string $locale): ?DestinationTranslation
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

    public function getNameTranslated(string $locale): ?string
    {
        return $this->getTranslation($locale)?->getName()
            ?? $this->getTranslation('fr')?->getName();
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }
}
