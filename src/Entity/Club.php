<?php

namespace App\Entity;

use App\Repository\ClubRepository;
use BcMath\Number;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

#[ORM\Entity(repositoryClass: ClubRepository::class)]
class Club
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(length: 5, unique: true)]
    private ?string $id_club = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $fundacion = null;

    #[ORM\Column(length: 255)]
    private ?string $ciudad = null;

    #[ORM\Column(length: 255)]
    private ?string $estadio = null;


    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    private ?string $presupuesto = null;

    #[ORM\OneToMany(mappedBy: 'club', targetEntity: Player::class, fetch: 'EAGER')]
    private Collection $players;

    #[ORM\OneToMany(mappedBy: 'club', targetEntity: Coach::class, fetch: 'EAGER')]
    private Collection $coaches;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->coaches = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdClub(): ?string
    {
        return $this->id_club;
    }

    public function setIdClub(string $id_club): static
    {
        $this->id_club = $id_club;

        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getFundacion(): ?int
    {
        return $this->fundacion;
    }

    public function setFundacion(int $fundacion): static
    {
        $this->fundacion = $fundacion;

        return $this;
    }

    public function getCiudad(): ?string
    {
        return $this->ciudad;
    }

    public function setCiudad(string $ciudad): static
    {
        $this->ciudad = $ciudad;

        return $this;
    }

    public function getEstadio(): ?string
    {
        return $this->estadio;
    }

    public function setEstadio(string $estadio): static
    {
        $this->estadio = $estadio;

        return $this;
    }


    public function getPresupuesto(): ?string
    {
        return $this->presupuesto;
    }

    public function setPresupuesto(string $presupuesto): static
    {
        $this->presupuesto = $presupuesto;

        return $this;
    }

    /**
     * @return Collection<int, Player>
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(Player $player): static
    {
        if (!$this->players->contains($player)) {
            $this->players->add($player);
            $player->setClub($this);
        }

        return $this;
    }

    public function removePlayer(Player $player): static
    {
        if ($this->players->removeElement($player)) {
            if ($player->getClub() === $this) {
                $player->setClub(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Coach>
     */
    public function getCoaches(): Collection
    {
        return $this->coaches;
    }

    public function addCoach(Coach $coach): static
    {
        if (!$this->coaches->contains($coach)) {
            $this->coaches->add($coach);
            $coach->setClub($this);
        }

        return $this;
    }

    public function removeCoach(Coach $coach): static
    {
        if ($this->coaches->removeElement($coach)) {
            if ($coach->getClub() === $this) {
                $coach->setClub(null);
            }
        }

        return $this;
    }

    public function getGastoJugadores(): float
    {
        $gastos = 0;

        foreach ($this->players as $player) {
            $gastos += (float)$player->getSalario();
        }

        return $gastos;
    }

    public function getGastosEntrenadores(): float
    {
        $gastos = 0;

        foreach ($this->coaches as $coach) {
            $gastos += (float)$coach->getSalario();
        }

        return $gastos;
    }

    function getPresupuestoRestante(): float
    {
        return (float)$this->presupuesto - ($this->getGastoJugadores() + $this->getGastosEntrenadores());
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
}
