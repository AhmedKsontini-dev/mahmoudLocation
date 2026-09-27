<?php

namespace App\Entity;

use App\Enum\ReservationStatus;
use App\Enum\ReservationTransfereType;
use App\Repository\ReservationTransfereRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: ReservationTransfereRepository::class)]
#[ORM\Table(name: 'reservation_transfere')]
class ReservationTransfere
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?Transfere $transfere = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    private ?string $prenom = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $telephone = null;

    #[ORM\Column(type: Types::STRING, enumType: ReservationTransfereType::class)]
    #[Assert\NotNull]
    private ReservationTransfereType $transfereType = ReservationTransfereType::ONE_WAY;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $pickupLocation = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $dropoffLocation = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    private ?DateTimeImmutable $pickupDate = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    #[Assert\NotNull]
    private ?DateTimeImmutable $pickupTime = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $returnPickupLocation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $returnDropoffLocation = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $returnPickupDate = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $returnPickupTime = null;

    #[ORM\Column]
    #[Assert\Positive]
    private int $nombrePassagers = 1;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $nombreBagages = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $numeroVol = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $compagnieVol = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private string $prixTotal = '0.00';

    #[ORM\Column(length: 3)]
    private string $devise = 'TND';

    #[ORM\Column(type: Types::STRING, enumType: ReservationStatus::class)]
    private ReservationStatus $status = ReservationStatus::PENDING;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    #[Assert\Callback]
    public function validateReturn(ExecutionContextInterface $context): void
    {
        if ($this->transfereType !== ReservationTransfereType::RETURN) {
            return;
        }

        foreach (['returnPickupLocation' => $this->returnPickupLocation, 'returnDropoffLocation' => $this->returnDropoffLocation, 'returnPickupDate' => $this->returnPickupDate, 'returnPickupTime' => $this->returnPickupTime] as $field => $value) {
            if ($value === null || $value === '') {
                $context->buildViolation('Cette information est obligatoire pour un aller-retour.')
                    ->atPath($field)
                    ->addViolation();
            }
        }

        if ($this->pickupDate !== null && $this->returnPickupDate !== null && $this->returnPickupDate < $this->pickupDate) {
            $context->buildViolation('La date de retour doit être postérieure ou égale à la date aller.')
                ->atPath('returnPickupDate')
                ->addViolation();
        }
    }

    public function getId(): ?int { return $this->id; }
    public function getTransfere(): ?Transfere { return $this->transfere; }
    public function setTransfere(?Transfere $transfere): static { $this->transfere = $transfere; return $this; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }
    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(string $prenom): static { $this->prenom = $prenom; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }
    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(string $telephone): static { $this->telephone = $telephone; return $this; }
    public function getTransfereType(): ReservationTransfereType { return $this->transfereType; }
    public function setTransfereType(ReservationTransfereType $type): static { $this->transfereType = $type; return $this; }
    public function getPickupLocation(): ?string { return $this->pickupLocation; }
    public function setPickupLocation(string $value): static { $this->pickupLocation = $value; return $this; }
    public function getDropoffLocation(): ?string { return $this->dropoffLocation; }
    public function setDropoffLocation(string $value): static { $this->dropoffLocation = $value; return $this; }
    public function getPickupDate(): ?DateTimeImmutable { return $this->pickupDate; }
    public function setPickupDate(?DateTimeImmutable $value): static { $this->pickupDate = $value; return $this; }
    public function getPickupTime(): ?DateTimeImmutable { return $this->pickupTime; }
    public function setPickupTime(?DateTimeImmutable $value): static { $this->pickupTime = $value; return $this; }
    public function getReturnPickupLocation(): ?string { return $this->returnPickupLocation; }
    public function setReturnPickupLocation(?string $value): static { $this->returnPickupLocation = $value; return $this; }
    public function getReturnDropoffLocation(): ?string { return $this->returnDropoffLocation; }
    public function setReturnDropoffLocation(?string $value): static { $this->returnDropoffLocation = $value; return $this; }
    public function getReturnPickupDate(): ?DateTimeImmutable { return $this->returnPickupDate; }
    public function setReturnPickupDate(?DateTimeImmutable $value): static { $this->returnPickupDate = $value; return $this; }
    public function getReturnPickupTime(): ?DateTimeImmutable { return $this->returnPickupTime; }
    public function setReturnPickupTime(?DateTimeImmutable $value): static { $this->returnPickupTime = $value; return $this; }
    public function getNombrePassagers(): int { return $this->nombrePassagers; }
    public function setNombrePassagers(int $value): static { $this->nombrePassagers = $value; return $this; }
    public function getNombreBagages(): int { return $this->nombreBagages; }
    public function setNombreBagages(int $value): static { $this->nombreBagages = $value; return $this; }
    public function getMessage(): ?string { return $this->message; }
    public function setMessage(?string $value): static { $this->message = $value; return $this; }
    public function getNumeroVol(): ?string { return $this->numeroVol; }
    public function setNumeroVol(?string $value): static { $this->numeroVol = $value; return $this; }
    public function getCompagnieVol(): ?string { return $this->compagnieVol; }
    public function setCompagnieVol(?string $value): static { $this->compagnieVol = $value; return $this; }
    public function getPrixTotal(): string { return $this->prixTotal; }
    public function setPrixTotal(string $value): static { $this->prixTotal = $value; return $this; }
    public function getDevise(): string { return $this->devise; }
    public function setDevise(string $value): static { $this->devise = strtoupper($value); return $this; }
    public function getStatus(): ReservationStatus { return $this->status; }
    public function setStatus(ReservationStatus $value): static { $this->status = $value; return $this; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
}