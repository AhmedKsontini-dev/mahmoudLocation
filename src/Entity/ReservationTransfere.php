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
    #[Assert\NotNull(message: 'reservation.transfer.transfere_required')]
    private ?Transfere $transfere = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'reservation.transfer.name_required')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'reservation.transfer.name_short', maxMessage: 'reservation.transfer.name_long')]
    #[Assert\Regex(
        pattern: '/^[\p{L}\p{M}]+(?:[\s\-\x27\x{2019}]+[\p{L}\p{M}]+)*$/u',
        message: 'reservation.transfer.name_invalid',
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    private ?string $prenom = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'reservation.transfer.email_required')]
    #[Assert\Email(message: 'reservation.transfer.email_invalid')]
    #[Assert\Length(max: 180, maxMessage: 'reservation.transfer.email_long')]
    private ?string $email = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'reservation.transfer.phone_required')]
    #[Assert\Length(max: 50, maxMessage: 'reservation.transfer.phone_long')]
    #[Assert\Regex(
        pattern: '/^\+?[0-9](?:[\s().\-]?[0-9]){5,14}$/u',
        message: 'reservation.transfer.phone_invalid',
    )]
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
    #[Assert\NotNull(message: 'reservation.transfer.date_required')]
    #[Assert\GreaterThanOrEqual('today', message: 'reservation.transfer.date_past', groups: ['frontend_booking'])]
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
    #[Assert\Range(min: 1, max: 9, notInRangeMessage: 'reservation.transfer.passengers_invalid')]
    private int $nombrePassagers = 1;

    #[ORM\Column]
    #[Assert\Range(min: 0, max: 9, notInRangeMessage: 'reservation.transfer.luggage_invalid')]
    private int $nombreBagages = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 2000, maxMessage: 'reservation.transfer.message_long')]
    private ?string $message = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Regex(pattern: '/^$|^[A-Za-z0-9][A-Za-z0-9 \-]{0,48}$/', message: 'reservation.transfer.flight_invalid')]
    private ?string $numeroVol = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100, maxMessage: 'reservation.transfer.airline_invalid')]
    #[Assert\Regex(pattern: '/[\r\n\x00-\x1F\x7F]/', match: false, message: 'reservation.transfer.airline_invalid')]
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
                $context->buildViolation('reservation.transfer.return_required')
                    ->atPath($field)
                    ->addViolation();
            }
        }

        if ($this->pickupDate !== null && $this->returnPickupDate !== null && $this->returnPickupDate < $this->pickupDate) {
            $context->buildViolation('reservation.transfer.return_before_departure')
                ->atPath('returnPickupDate')
                ->addViolation();
        }
    }

    /**
     * Réservation frontend uniquement : si le départ a lieu aujourd'hui, l'heure
     * de départ saisie doit encore être dans le futur (règle métier : aucune avance
     * minimale n'est imposée par le projet). Les dates purement passées sont déjà
     * rejetées par la contrainte GreaterThanOrEqual('today').
     */
    #[Assert\Callback(groups: ['frontend_booking'])]
    public function validatePickupTimeNotPast(ExecutionContextInterface $context): void
    {
        if ($this->pickupDate === null || $this->pickupTime === null) {
            return;
        }

        $today = new DateTimeImmutable('today');
        if ($this->pickupDate->format('Y-m-d') !== $today->format('Y-m-d')) {
            return;
        }

        $departure = $this->pickupDate->setTime((int) $this->pickupTime->format('H'), (int) $this->pickupTime->format('i'));
        if ($departure < new DateTimeImmutable('now')) {
            $context->buildViolation('reservation.transfer.time_past')
                ->atPath('pickupTime')
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