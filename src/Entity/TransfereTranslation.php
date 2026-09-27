<?php

namespace App\Entity;

use App\Repository\TransfereTranslationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: TransfereTranslationRepository::class)]
#[ORM\Table(name: 'transfere_translation')]
#[ORM\UniqueConstraint(name: 'UNIQ_TRANSFERE_TRANSLATION_LOCALE', columns: ['transfere_id', 'locale'])]
class TransfereTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Transfere $transfere = null;

    #[ORM\Column(length: 5)]
    #[Assert\Choice(callback: [\App\Config\LocaleConfig::class, 'getSupportedLocales'])]
    private string $locale = 'fr';

    #[ORM\Column(name: 'lieu_depart', length: 255, nullable: true)]
    private ?string $lieuDepart = null;

    #[ORM\Column(name: 'lieu_arrivee', length: 255, nullable: true)]
    private ?string $lieuArrivee = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $duree = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTransfere(): ?Transfere
    {
        return $this->transfere;
    }

    public function setTransfere(?Transfere $transfere): static
    {
        $this->transfere = $transfere;
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

    public function getLieuDepart(): ?string
    {
        return $this->lieuDepart;
    }

    public function setLieuDepart(?string $lieuDepart): static
    {
        $this->lieuDepart = $lieuDepart;
        return $this;
    }

    public function getLieuArrivee(): ?string
    {
        return $this->lieuArrivee;
    }

    public function setLieuArrivee(?string $lieuArrivee): static
    {
        $this->lieuArrivee = $lieuArrivee;
        return $this;
    }

    public function getDuree(): ?string
    {
        return $this->duree;
    }

    public function setDuree(?string $duree): static
    {
        $this->duree = $duree;
        return $this;
    }

    #[Assert\Callback]
    public function validateFrenchFields(ExecutionContextInterface $context): void
    {
        if ($this->locale !== 'fr') {
            return;
        }

        if (trim((string) $this->lieuDepart) === '') {
            $context->buildViolation('Ce champ est obligatoire en français.')
                ->atPath('lieuDepart')
                ->addViolation();
        }
        if (trim((string) $this->lieuArrivee) === '') {
            $context->buildViolation('Ce champ est obligatoire en français.')
                ->atPath('lieuArrivee')
                ->addViolation();
        }
        if (trim((string) $this->duree) === '') {
            $context->buildViolation('La durée est obligatoire en français.')
                ->atPath('duree')
                ->addViolation();
        }
    }
}
