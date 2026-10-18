<?php

namespace App\Entity;

use App\Config\LocaleConfig;
use App\Repository\TransfereRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TransfereRepository::class)]
#[ORM\Table(name: 'transfere')]
#[ORM\UniqueConstraint(name: 'UNIQ_TRANSFERE_SLUG', columns: ['slug'])]
#[UniqueEntity(fields: ['slug'], message: 'Ce transfert possède déjà ce slug.')]
class Transfere
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'lieu_depart', length: 255)]
    private ?string $lieuDepart = null;

    #[ORM\Column(name: 'lieu_arrivee', length: 255)]
    private ?string $lieuArrivee = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'prix_normal', type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?string $prixNormal = null;

    #[ORM\Column(name: 'prix_saison_forte', type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?string $prixSaisonForte = null;

    #[ORM\Column]
    private bool $actif = true;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    /** @var Collection<int, TransfereTranslation> */
    #[ORM\OneToMany(mappedBy: 'transfere', targetEntity: TransfereTranslation::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $translations;

    /** @var Collection<int, ReservationTransfere> */
    #[ORM\OneToMany(mappedBy: 'transfere', targetEntity: ReservationTransfere::class)]
    private Collection $reservations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getLieuDepart(): ?string { return $this->lieuDepart; }
    public function setLieuDepart(string $lieuDepart): static { $this->lieuDepart = $lieuDepart; return $this; }
    public function getLieuArrivee(): ?string { return $this->lieuArrivee; }
    public function setLieuArrivee(string $lieuArrivee): static { $this->lieuArrivee = $lieuArrivee; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getPrixNormal(): ?string { return $this->prixNormal; }
    public function setPrixNormal(string $prixNormal): static { $this->prixNormal = $prixNormal; return $this; }
    public function getPrixSaisonForte(): ?string { return $this->prixSaisonForte; }
    public function setPrixSaisonForte(string $prixSaisonForte): static { $this->prixSaisonForte = $prixSaisonForte; return $this; }
    public function isActif(): bool { return $this->actif; }
    public function setActif(bool $actif): static { $this->actif = $actif; return $this; }
    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }
    
    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): static { $this->image = $image; return $this; }

    /** @return Collection<int, TransfereTranslation> */
    public function getTranslations(): Collection { return $this->translations; }
    /** @return Collection<int, ReservationTransfere> */
    public function getReservations(): Collection { return $this->reservations; }

    public function addTranslation(TransfereTranslation $translation): static
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setTransfere($this);
        }
        return $this;
    }

    public function removeTranslation(TransfereTranslation $translation): static
    {
        if ($this->translations->removeElement($translation) && $translation->getTransfere() === $this) {
            $translation->setTransfere(null);
        }
        return $this;
    }

    public function getTranslation(string $locale): ?TransfereTranslation
    {
        $fallback = null;
        foreach ($this->translations as $translation) {
            $fallback ??= $translation;
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

        return $fallback;
    }

    public function getLieuDepartTranslated(string $locale): ?string
    {
        $translation = $this->getTranslation($locale);
        return $translation?->getLieuDepart()
            ?? $this->getTranslation('fr')?->getLieuDepart()
            ?? $this->lieuDepart;
    }

    public function getLieuArriveeTranslated(string $locale): ?string
    {
        $translation = $this->getTranslation($locale);
        return $translation?->getLieuArrivee()
            ?? $this->getTranslation('fr')?->getLieuArrivee()
            ?? $this->lieuArrivee;
    }

    public function getDureeTranslated(string $locale): ?string
    {
        return $this->getTranslation($locale)?->getDuree()
            ?? $this->getTranslation('fr')?->getDuree()
            ?? $this->getFirstTranslationValue('duree');
    }

    private function getFirstTranslationValue(string $field): ?string
    {
        foreach ($this->translations as $translation) {
            $value = $field === 'duree' ? $translation->getDuree() : null;
            if ($value !== null && $value !== '') {
                return $value;
            }
        }
        return null;
    }

    public function syncLegacyLocations(): void
    {
        $french = $this->getTranslation(LocaleConfig::SUPPORTED_LOCALES[0]);
        if ($french?->getLieuDepart() !== null) {
            $this->lieuDepart = $french->getLieuDepart();
        }
        if ($french?->getLieuArrivee() !== null) {
            $this->lieuArrivee = $french->getLieuArrivee();
        }
    }
}