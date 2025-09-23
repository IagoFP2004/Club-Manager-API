<?php

namespace App\Entity;

use App\Repository\ClubRepository;
use BcMath\Number;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClubRepository::class)]
class Club
{
    #[ORM\Id]
    #[ORM\Column(length: 5)]
    private ?string $id_club = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $fundacion = null;

    #[ORM\Column(length: 255)]
    private ?string $ciudad = null;

    #[ORM\Column(length: 255)]
    private ?string $estadio = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $entrenador = null;

    #[ORM\OneToMany(mappedBy: 'club', targetEntity: Player::class)]
    private Collection $players;

    public function __construct()
    {
        $this->players = new ArrayCollection();
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

    public function getEntrenador(): ?int
    {
        return $this->entrenador;
    }

    public function setEntrenador(int $entrenador): static
    {
        $this->entrenador = $entrenador;

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
}
