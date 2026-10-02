<?php

namespace App\Entity;

use App\Config\LocaleConfig;
use App\Repository\ExcursionFaqRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExcursionFaqRepository::class)]
#[ORM\Table(name: 'excursion_faq')]
class ExcursionFaq
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'faqs')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Excursion $excursion = null;

    #[ORM\Column]
    private int $position = 0;

    #[ORM\Column(name: 'is_active')]
    private bool $isActive = true;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    /** @var Collection<int, ExcursionFaqTranslation> */
    #[ORM\OneToMany(mappedBy: 'excursionFaq', targetEntity: ExcursionFaqTranslation::class, cascade: ['persist'], orphanRemoval: true)]
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

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;
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

    /** @return Collection<int, ExcursionFaqTranslation> */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(ExcursionFaqTranslation $translation): static
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setExcursionFaq($this);
        }
        return $this;
    }

    public function removeTranslation(ExcursionFaqTranslation $translation): static
    {
        if ($this->translations->removeElement($translation) && $translation->getExcursionFaq() === $this) {
            $translation->setExcursionFaq(null);
        }
        return $this;
    }

    public function getTranslation(string $locale): ?ExcursionFaqTranslation
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

    public function getQuestionTranslated(string $locale): ?string
    {
        return $this->getTranslation($locale)?->getQuestion()
            ?? $this->getTranslation('fr')?->getQuestion();
    }

    public function getAnswerTranslated(string $locale): ?string
    {
        return $this->getTranslation($locale)?->getAnswer()
            ?? $this->getTranslation('fr')?->getAnswer();
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }
}
