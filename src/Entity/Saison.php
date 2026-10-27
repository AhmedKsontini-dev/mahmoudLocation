<?php

namespace App\Entity;

use App\Repository\SaisonRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: SaisonRepository::class)]
#[ORM\Table(name: 'saison')]
class Saison
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $nom = null;

    /// Héritage : anciennes périodes au format date complète (jj/mm/aaaa).
    /// Conservées pour ne perdre aucune donnée historique (l'année est gardée),
    /// mais elles ne pilotent plus le calcul tarifaire, désormais récurrent.
    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateDebut = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateFin = null;

    /// Période récurrente : uniquement jour + mois, valable chaque année.
    #[ORM\Column(name: 'date_debut_jour', type: Types::SMALLINT)]
    #[Assert\NotNull(message: 'Le jour de début est obligatoire.')]
    #[Assert\Range(min: 1, max: 31, message: 'Le jour de début doit être compris entre 1 et 31.')]
    private ?int $dateDebutJour = null;

    #[ORM\Column(name: 'date_debut_mois', type: Types::SMALLINT)]
    #[Assert\NotNull(message: 'Le mois de début est obligatoire.')]
    #[Assert\Range(min: 1, max: 12, message: 'Le mois de début doit être compris entre 1 et 12.')]
    private ?int $dateDebutMois = null;

    #[ORM\Column(name: 'date_fin_jour', type: Types::SMALLINT)]
    #[Assert\NotNull(message: 'Le jour de fin est obligatoire.')]
    #[Assert\Range(min: 1, max: 31, message: 'Le jour de fin doit être compris entre 1 et 31.')]
    private ?int $dateFinJour = null;

    #[ORM\Column(name: 'date_fin_mois', type: Types::SMALLINT)]
    #[Assert\NotNull(message: 'Le mois de fin est obligatoire.')]
    #[Assert\Range(min: 1, max: 12, message: 'Le mois de fin doit être compris entre 1 et 12.')]
    private ?int $dateFinMois = null;

    #[ORM\Column]
    private bool $actif = true;

    /// Valide la cohérence du calendrier (jour réel pour le mois saisi).
    /// Une année bissextile de référence (2024) est utilisée afin d'autoriser
    /// le 29 février pour une saison récurrente ; les dates impossibles
    /// (30/02, 31/02, 31/04…) sont rejetées.
    #[Assert\Callback]
    public function validatePeriod(ExecutionContextInterface $context): void
    {
        $sides = [
            'début' => [$this->dateDebutMois, $this->dateDebutJour, 'dateDebutJour'],
            'fin' => [$this->dateFinMois, $this->dateFinJour, 'dateFinJour'],
        ];

        foreach ($sides as $label => [$month, $day, $property]) {
            if ($month === null || $day === null) {
                continue;
            }
            if (!checkdate($month, $day, 2024)) {
                $context->buildViolation(sprintf('La date de %s (%02d/%02d) est invalide.', $label, $day, $month))
                    ->atPath($property)
                    ->addViolation();
            }
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeImmutable
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeImmutable $dateDebut): static
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeImmutable
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeImmutable $dateFin): static
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getDateDebutJour(): ?int
    {
        return $this->dateDebutJour;
    }

    public function setDateDebutJour(?int $dateDebutJour): static
    {
        $this->dateDebutJour = $dateDebutJour;
        return $this;
    }

    public function getDateDebutMois(): ?int
    {
        return $this->dateDebutMois;
    }

    public function setDateDebutMois(?int $dateDebutMois): static
    {
        $this->dateDebutMois = $dateDebutMois;
        return $this;
    }

    public function getDateFinJour(): ?int
    {
        return $this->dateFinJour;
    }

    public function setDateFinJour(?int $dateFinJour): static
    {
        $this->dateFinJour = $dateFinJour;
        return $this;
    }

    public function getDateFinMois(): ?int
    {
        return $this->dateFinMois;
    }

    public function setDateFinMois(?int $dateFinMois): static
    {
        $this->dateFinMois = $dateFinMois;
        return $this;
    }

    public function isActif(): bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;
        return $this;
    }
}
