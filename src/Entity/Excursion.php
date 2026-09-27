<?php

namespace App\Entity;

use App\Config\LocaleConfig;
use App\Repository\ExcursionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExcursionRepository::class)]
#[ORM\Table(name: 'excursion')]
#[ORM\UniqueConstraint(name: 'UNIQ_EXCURSION_SLUG', columns: ['slug'])]
#[UniqueEntity(fields: ['slug'], message: 'Cette excursion possède déjà ce slug.')]
class Excursion
{
    public const TYPE_PRIVATE = 'private';
    public const TYPE_GROUP = 'group';
    public const TYPE_VIP = 'vip';
    public const TYPE_CUSTOM = 'custom';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_DRAFT = 'draft';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'excursions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'excursions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Destination $destination = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::TYPE_PRIVATE, self::TYPE_GROUP, self::TYPE_VIP, self::TYPE_CUSTOM])]
    private string $type = self::TYPE_PRIVATE;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DRAFT])]
    private string $status = self::STATUS_DRAFT;

    #[ORM\Column]
    private bool $isBestSeller = false;

    #[ORM\Column]
    private bool $isFeatured = false;

    #[ORM\Column(name: 'duration_hours', nullable: true)]
    private ?int $durationHours = null;

    #[ORM\Column(name: 'duration_minutes', nullable: true)]
    private ?int $durationMinutes = null;

    #[ORM\Column(name: 'start_time', type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(name: 'end_time', type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endTime = null;

    #[ORM\Column(name: 'price_adult', type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?string $priceAdult = null;

    #[ORM\Column(name: 'price_child', type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $priceChild = null;

    #[ORM\Column(name: 'price_converted', type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $priceConverted = null;

    #[ORM\Column(name: 'min_participants')]
    private int $minParticipants = 1;

    #[ORM\Column(name: 'max_participants')]
    private int $maxParticipants = 10;

    #[ORM\Column(name: 'cancellation_hours')]
    private int $cancellationHours = 24;

    #[ORM\Column(name: 'rating_average', type: Types::DECIMAL, precision: 3, scale: 2, nullable: true)]
    private ?string $ratingAverage = null;

    #[ORM\Column(name: 'rating_count')]
    private int $ratingCount = 0;

    #[ORM\Column(name: 'booking_count')]
    private int $bookingCount = 0;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    /** @var Collection<int, ExcursionTranslation> */
    #[ORM\OneToMany(mappedBy: 'excursion', targetEntity: ExcursionTranslation::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $translations;

    /** @var Collection<int, ExcursionImage> */
    #[ORM\OneToMany(mappedBy: 'excursion', targetEntity: ExcursionImage::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $images;

    /** @var Collection<int, ExcursionFeature> */
    #[ORM\OneToMany(mappedBy: 'excursion', targetEntity: ExcursionFeature::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $features;

    /** @var Collection<int, ExcursionItinerary> */
    #[ORM\OneToMany(mappedBy: 'excursion', targetEntity: ExcursionItinerary::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['stepNumber' => 'ASC'])]
    private Collection $itineraries;

    /** @var Collection<int, ExcursionInclusion> */
    #[ORM\OneToMany(mappedBy: 'excursion', targetEntity: ExcursionInclusion::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['type' => 'ASC', 'position' => 'ASC'])]
    private Collection $inclusions;

    /** @var Collection<int, ExcursionFaq> */
    #[ORM\OneToMany(mappedBy: 'excursion', targetEntity: ExcursionFaq::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $faqs;

    /** @var Collection<int, ExcursionBadge> */
    #[ORM\OneToMany(mappedBy: 'excursion', targetEntity: ExcursionBadge::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $badges;

    /** @var Collection<int, Review> */
    #[ORM\OneToMany(mappedBy: 'excursion', targetEntity: Review::class)]
    private Collection $reviews;

    /** @var Collection<int, Reservation> */
    #[ORM\OneToMany(mappedBy: 'excursion', targetEntity: Reservation::class)]
    private Collection $reservations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->features = new ArrayCollection();
        $this->itineraries = new ArrayCollection();
        $this->inclusions = new ArrayCollection();
        $this->faqs = new ArrayCollection();
        $this->badges = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getDestination(): ?Destination
    {
        return $this->destination;
    }

    public function setDestination(?Destination $destination): static
    {
        $this->destination = $destination;
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function isBestSeller(): bool
    {
        return $this->isBestSeller;
    }

    public function setBestSeller(bool $isBestSeller): static
    {
        $this->isBestSeller = $isBestSeller;
        return $this;
    }

    public function isFeatured(): bool
    {
        return $this->isFeatured;
    }

    public function setFeatured(bool $isFeatured): static
    {
        $this->isFeatured = $isFeatured;
        return $this;
    }

    public function getDurationHours(): ?int
    {
        return $this->durationHours;
    }

    public function setDurationHours(?int $durationHours): static
    {
        $this->durationHours = $durationHours;
        return $this;
    }

    public function getDurationMinutes(): ?int
    {
        return $this->durationMinutes;
    }

    public function setDurationMinutes(?int $durationMinutes): static
    {
        $this->durationMinutes = $durationMinutes;
        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeInterface $startTime): static
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeInterface $endTime): static
    {
        $this->endTime = $endTime;
        return $this;
    }

    public function getPriceAdult(): ?string
    {
        return $this->priceAdult;
    }

    public function setPriceAdult(string $priceAdult): static
    {
        $this->priceAdult = $priceAdult;
        return $this;
    }

    public function getPriceChild(): ?string
    {
        return $this->priceChild;
    }

    public function setPriceChild(?string $priceChild): static
    {
        $this->priceChild = $priceChild;
        return $this;
    }

    public function getPriceConverted(): ?string
    {
        return $this->priceConverted;
    }

    public function setPriceConverted(?string $priceConverted): static
    {
        $this->priceConverted = $priceConverted;
        return $this;
    }

    public function getMinParticipants(): int
    {
        return $this->minParticipants;
    }

    public function setMinParticipants(int $minParticipants): static
    {
        $this->minParticipants = $minParticipants;
        return $this;
    }

    public function getMaxParticipants(): int
    {
        return $this->maxParticipants;
    }

    public function setMaxParticipants(int $maxParticipants): static
    {
        $this->maxParticipants = $maxParticipants;
        return $this;
    }

    public function getCancellationHours(): int
    {
        return $this->cancellationHours;
    }

    public function setCancellationHours(int $cancellationHours): static
    {
        $this->cancellationHours = $cancellationHours;
        return $this;
    }

    public function getRatingAverage(): ?string
    {
        return $this->ratingAverage;
    }

    public function setRatingAverage(?string $ratingAverage): static
    {
        $this->ratingAverage = $ratingAverage;
        return $this;
    }

    public function getRatingCount(): int
    {
        return $this->ratingCount;
    }

    public function setRatingCount(int $ratingCount): static
    {
        $this->ratingCount = $ratingCount;
        return $this;
    }

    public function getBookingCount(): int
    {
        return $this->bookingCount;
    }

    public function setBookingCount(int $bookingCount): static
    {
        $this->bookingCount = $bookingCount;
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

    /** @return Collection<int, ExcursionTranslation> */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    /** @return Collection<int, ExcursionImage> */
    public function getImages(): Collection
    {
        return $this->images;
    }

    /** @return Collection<int, ExcursionFeature> */
    public function getFeatures(): Collection
    {
        return $this->features;
    }

    /** @return Collection<int, ExcursionItinerary> */
    public function getItineraries(): Collection
    {
        return $this->itineraries;
    }

    /** @return Collection<int, ExcursionInclusion> */
    public function getInclusions(): Collection
    {
        return $this->inclusions;
    }

    /** @return Collection<int, ExcursionFaq> */
    public function getFaqs(): Collection
    {
        return $this->faqs;
    }

    /** @return Collection<int, ExcursionBadge> */
    public function getBadges(): Collection
    {
        return $this->badges;
    }

    /** @return Collection<int, Review> */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    /** @return Collection<int, Reservation> */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addTranslation(ExcursionTranslation $translation): static
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setExcursion($this);
        }
        return $this;
    }

    public function removeTranslation(ExcursionTranslation $translation): static
    {
        if ($this->translations->removeElement($translation) && $translation->getExcursion() === $this) {
            $translation->setExcursion(null);
        }
        return $this;
    }

    public function addImage(ExcursionImage $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setExcursion($this);
        }
        return $this;
    }

    public function removeImage(ExcursionImage $image): static
    {
        if ($this->images->removeElement($image) && $image->getExcursion() === $this) {
            $image->setExcursion(null);
        }
        return $this;
    }

    public function addFeature(ExcursionFeature $feature): static
    {
        if (!$this->features->contains($feature)) {
            $this->features->add($feature);
            $feature->setExcursion($this);
        }
        return $this;
    }

    public function removeFeature(ExcursionFeature $feature): static
    {
        if ($this->features->removeElement($feature) && $feature->getExcursion() === $this) {
            $feature->setExcursion(null);
        }
        return $this;
    }

    public function addItinerary(ExcursionItinerary $itinerary): static
    {
        if (!$this->itineraries->contains($itinerary)) {
            $this->itineraries->add($itinerary);
            $itinerary->setExcursion($this);
        }
        return $this;
    }

    public function removeItinerary(ExcursionItinerary $itinerary): static
    {
        if ($this->itineraries->removeElement($itinerary) && $itinerary->getExcursion() === $this) {
            $itinerary->setExcursion(null);
        }
        return $this;
    }

    public function addInclusion(ExcursionInclusion $inclusion): static
    {
        if (!$this->inclusions->contains($inclusion)) {
            $this->inclusions->add($inclusion);
            $inclusion->setExcursion($this);
        }
        return $this;
    }

    public function removeInclusion(ExcursionInclusion $inclusion): static
    {
        if ($this->inclusions->removeElement($inclusion) && $inclusion->getExcursion() === $this) {
            $inclusion->setExcursion(null);
        }
        return $this;
    }

    public function addFaq(ExcursionFaq $faq): static
    {
        if (!$this->faqs->contains($faq)) {
            $this->faqs->add($faq);
            $faq->setExcursion($this);
        }
        return $this;
    }

    public function removeFaq(ExcursionFaq $faq): static
    {
        if ($this->faqs->removeElement($faq) && $faq->getExcursion() === $this) {
            $faq->setExcursion(null);
        }
        return $this;
    }

    public function addBadge(ExcursionBadge $badge): static
    {
        if (!$this->badges->contains($badge)) {
            $this->badges->add($badge);
            $badge->setExcursion($this);
        }
        return $this;
    }

    public function removeBadge(ExcursionBadge $badge): static
    {
        if ($this->badges->removeElement($badge) && $badge->getExcursion() === $this) {
            $badge->setExcursion(null);
        }
        return $this;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setExcursion($this);
        }
        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review) && $review->getExcursion() === $this) {
            $review->setExcursion(null);
        }
        return $this;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setExcursion($this);
        }
        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation) && $reservation->getExcursion() === $this) {
            $reservation->setExcursion(null);
        }
        return $this;
    }

    public function getTranslation(string $locale): ?ExcursionTranslation
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

    public function getTitleTranslated(string $locale): ?string
    {
        return $this->getTranslation($locale)?->getTitle()
            ?? $this->getTranslation('fr')?->getTitle();
    }

    public function getDescriptionTranslated(string $locale): ?string
    {
        return $this->getTranslation($locale)?->getDescription()
            ?? $this->getTranslation('fr')?->getDescription();
    }

    public function getLocationTranslated(string $locale): ?string
    {
        return $this->getTranslation($locale)?->getLocation()
            ?? $this->getTranslation('fr')?->getLocation();
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }
}
