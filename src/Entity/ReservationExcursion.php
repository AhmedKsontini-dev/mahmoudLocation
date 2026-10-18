<?php

namespace App\Entity;

use App\Repository\ReservationExcursionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReservationExcursionRepository::class)]
#[ORM\Table(name: 'reservation_excursion')]
class ReservationExcursion
{
    public const STATUS_PENDING = 'en_attente';
    public const STATUS_CONFIRMED = 'confirmee';
    public const STATUS_CANCELLED = 'annulee';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservationExcursions')]
    #[ORM\JoinColumn(name: 'excursion_id', nullable: false, onDelete: 'RESTRICT')]
    #[Assert\NotNull]
    private ?Excursion $excursion = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Assert\Regex(pattern: '/^\+?[0-9\s().-]{7,25}$/', message: 'Veuillez saisir un numéro WhatsApp valide avec son indicatif international si nécessaire.')]
    private ?string $whatsapp = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?int $nombreAdultes = 1;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    private ?int $nombreEnfants = 0;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual('today', message: 'La date de l’excursion ne peut pas être passée.', groups: ['frontend_booking'])]
    private ?\DateTimeInterface $dateExcursion = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $heureDepart = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 2000)]
    private ?string $lieuPriseEnCharge = null;

    #[ORM\Column(length: 20, options: ['default' => self::STATUS_PENDING])]
    #[Assert\Choice(choices: [self::STATUS_PENDING, self::STATUS_CONFIRMED, self::STATUS_CANCELLED])]
    private string $statut = self::STATUS_PENDING;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getWhatsapp(): ?string
    {
        return $this->whatsapp;
    }

    public function setWhatsapp(?string $whatsapp): static
    {
        $this->whatsapp = $whatsapp;

        return $this;
    }

    public function getNombreAdultes(): ?int
    {
        return $this->nombreAdultes;
    }

    public function setNombreAdultes(?int $nombreAdultes): static
    {
        $this->nombreAdultes = $nombreAdultes;

        return $this;
    }

    public function getNombreEnfants(): ?int
    {
        return $this->nombreEnfants;
    }

    public function setNombreEnfants(?int $nombreEnfants): static
    {
        $this->nombreEnfants = $nombreEnfants;

        return $this;
    }

    public function getDateExcursion(): ?\DateTimeInterface
    {
        return $this->dateExcursion;
    }

    public function setDateExcursion(?\DateTimeInterface $dateExcursion): static
    {
        $this->dateExcursion = $dateExcursion;

        return $this;
    }

    public function getHeureDepart(): ?\DateTimeInterface
    {
        return $this->heureDepart;
    }

    public function setHeureDepart(?\DateTimeInterface $heureDepart): static
    {
        $this->heureDepart = $heureDepart;

        return $this;
    }

    public function getLieuPriseEnCharge(): ?string
    {
        return $this->lieuPriseEnCharge;
    }

    public function setLieuPriseEnCharge(?string $lieuPriseEnCharge): static
    {
        $this->lieuPriseEnCharge = $lieuPriseEnCharge;

        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getStatutLabel(): string
    {
        return match ($this->statut) {
            self::STATUS_CONFIRMED => 'Confirmée',
            self::STATUS_PENDING => 'En attente',
            self::STATUS_CANCELLED => 'Annulée',
            default => $this->statut,
        };
    }

    public function getStatutBadgeClass(): string
    {
        return match ($this->statut) {
            self::STATUS_CONFIRMED => 'badge-confirmed',
            self::STATUS_PENDING => 'badge-pending',
            self::STATUS_CANCELLED => 'badge-cancelled',
            default => 'badge-pending',
        };
    }

    public function getNomComplet(): string
    {
        return trim(($this->nom ?? '') . ' ' . ($this->prenom ?? ''));
    }

    public function getTotalParticipants(): int
    {
        return ($this->nombreAdultes ?? 0) + ($this->nombreEnfants ?? 0);
    }

    /**
     * @return array<string, string>
     */
    public static function getAvailableStatuses(): array
    {
        return [
            'En attente' => self::STATUS_PENDING,
            'Confirmée' => self::STATUS_CONFIRMED,
            'Annulée' => self::STATUS_CANCELLED,
        ];
    }
}
