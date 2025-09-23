<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Player;
use App\Entity\Club;

class PlayerController extends AbstractController
{
    #[Route('/players', name: 'player_list', methods: ['GET'])]
    public function listPlayers(EntityManagerInterface $entityManager): Response
    {
        $players = $entityManager->getRepository(Player::class)->findAll();

        if(!$players)
        {
            return $this->json(['message' => 'No hay players registrados'],400);
        }

        $data = [];
        foreach($players as $player)
        {
            $data[] = [
                'nombre' => $player->getNombre(),
                'apellidos' => $player->getApellidos(),
                'dorsal' => $player->getDorsal(),
                'salario' => $player->getSalario(),
                'club' => $player->getClub() ? $player->getClub()->getNombre() : null,
                'entrenador' =>$player->getClub()->getCoaches()->first()->getNombre() . ' ' . $player->getClub()->getCoaches()->first()->getApellidos()
            ];
        }

        return $this->json(['players' => $data]);
    }

    #[Route('/players/{id}', name: 'player_get', methods: ['GET'])]
    public function getPlayer(EntityManagerInterface $entityManager, $id): Response
    {
        $player = $entityManager->getRepository(Player::class)->find($id);

        if(!$player)
        {
            return $this->json(['error' => 'Player not found'], 404);
        }

        $data = [
            'nombre' => $player->getNombre(),
            'apellidos' => $player->getApellidos(),
            'dorsal' => $player->getDorsal(),
            'salario' => $player->getSalario(),
            'club' => $player->getClub()->getNombre(),
            'entrenador' => $player->getClub()->getCoaches()->first()->getNombre() . ' ' . $player->getClub()->getCoaches()->first()->getApellidos()
        ];

        return $this->json($data);
    }
}