<?php

namespace App\Entity;

use App\Repository\PlayerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    private ?string $apellidos = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $dorsal = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $salario = null;

    #[ORM\Column(length: 5, nullable: true)]
    private ?string $id_club = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getApellidos(): ?string
    {
        return $this->apellidos;
    }

    public function setApellidos(string $apellidos): static
    {
        $this->apellidos = $apellidos;
        return $this;
    }

    public function getDorsal(): ?int
    {
        return $this->dorsal;
    }

    public function setDorsal(int $dorsal): static
    {
        $this->dorsal = $dorsal;
        return $this;
    }

    public function getSalario(): ?string
    {
        return $this->salario;
    }

    public function setSalario(string $salario): static
    {
        $this->salario = $salario;
        return $this;
    }

    public function getIdClub(): ?string
    {
        return $this->id_club;
    }

    public function setIdClub(?string $id_club): static
    {
        $this->id_club = $id_club;
        return $this;
    }
}