<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ORM\Table(name: 'reservation')]
class Reservation
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    public const PAYMENT_STATUS_PENDING = 'pending';
    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_REFUNDED = 'refunded';

    public const PAYMENT_METHOD_CASH = 'cash';
    public const PAYMENT_METHOD_CARD = 'card';
    public const PAYMENT_METHOD_TRANSFER = 'transfer';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Excursion $excursion = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank]
    #[Assert\GreaterThan('today')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(name: 'adults_count')]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private int $adultsCount = 1;

    #[ORM\Column(name: 'children_count')]
    #[Assert\PositiveOrZero]
    private int $childrenCount = 0;

    #[ORM\Column(name: 'pickup_region', length: 255, nullable: true)]
    private ?string $pickupRegion = null;

    #[ORM\Column(name: 'pickup_address', type: Types::TEXT, nullable: true)]
    private ?string $pickupAddress = null;

    #[ORM\Column(name: 'contact_name', length: 255)]
    #[Assert\NotBlank]
    private ?string $contactName = null;

    #[ORM\Column(name: 'contact_phone', length: 50)]
    #[Assert\NotBlank]
    private ?string $contactPhone = null;

    #[ORM\Column(name: 'contact_email', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $contactEmail = null;

    #[ORM\Column(name: 'total_price', type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?string $totalPrice = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::STATUS_PENDING, self::STATUS_CONFIRMED, self::STATUS_CANCELLED, self::STATUS_COMPLETED])]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(name: 'payment_status', length: 20)]
    #[Assert\Choice(choices: [self::PAYMENT_STATUS_PENDING, self::PAYMENT_STATUS_PAID, self::PAYMENT_STATUS_REFUNDED])]
    private string $paymentStatus = self::PAYMENT_STATUS_PENDING;

    #[ORM\Column(name: 'payment_method', length: 20, nullable: true)]
    #[Assert\Choice(choices: [self::PAYMENT_METHOD_CASH, self::PAYMENT_METHOD_CARD, self::PAYMENT_METHOD_TRANSFER])]
    private ?string $paymentMethod = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

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

    public function getExcursion(): ?Excursion
    {
        return $this->excursion;
    }

    public function setExcursion(?Excursion $excursion): static
    {
        $this->excursion = $excursion;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getAdultsCount(): int
    {
        return $this->adultsCount;
    }

    public function setAdultsCount(int $adultsCount): static
    {
        $this->adultsCount = $adultsCount;
        return $this;
    }

    public function getChildrenCount(): int
    {
        return $this->childrenCount;
    }

    public function setChildrenCount(int $childrenCount): static
    {
        $this->childrenCount = $childrenCount;
        return $this;
    }

    public function getPickupRegion(): ?string
    {
        return $this->pickupRegion;
    }

    public function setPickupRegion(?string $pickupRegion): static
    {
        $this->pickupRegion = $pickupRegion;
        return $this;
    }

    public function getPickupAddress(): ?string
    {
        return $this->pickupAddress;
    }

    public function setPickupAddress(?string $pickupAddress): static
    {
        $this->pickupAddress = $pickupAddress;
        return $this;
    }

    public function getContactName(): ?string
    {
        return $this->contactName;
    }

    public function setContactName(string $contactName): static
    {
        $this->contactName = $contactName;
        return $this;
    }

    public function getContactPhone(): ?string
    {
        return $this->contactPhone;
    }

    public function setContactPhone(string $contactPhone): static
    {
        $this->contactPhone = $contactPhone;
        return $this;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(string $contactEmail): static
    {
        $this->contactEmail = $contactEmail;
        return $this;
    }

    public function getTotalPrice(): ?string
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(string $totalPrice): static
    {
        $this->totalPrice = $totalPrice;
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

    public function getPaymentStatus(): string
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(string $paymentStatus): static
    {
        $this->paymentStatus = $paymentStatus;
        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?string $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
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

    public function getTotalParticipants(): int
    {
        return $this->adultsCount + $this->childrenCount;
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }
}
