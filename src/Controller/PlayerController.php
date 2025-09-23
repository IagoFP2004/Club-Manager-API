<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Connection;

class PlayerController extends AbstractController
{
    #[Route('/player', name: 'player_list', methods: ['GET'])]
    public function listPlayers(Connection $connection): Response
    {
        $sql = "SELECT * FROM player";
        $players = $connection->fetchAllAssociative($sql);
        
        return $this->json([
            'players' => $players,
        ]);
    }

    #[Route('/player', name: 'player_insert', methods: ['POST'])]
    public function createPlayer(Connection $connection, Request $request): Response
    {
        $sql = "INSERT INTO player (nombre,apellidos,dorsal,id_club,salario) 
        VALUES (:nombre, :apellidos, :dorsal, :id_club, :salario)";
        $connection->executeStatement($sql, [
            'nombre' => $request->request->get('nombre'),
            'apellidos' => $request->request->get('apellidos'),
            'dorsal' => $request->request->get('dorsal'),
            'id_club' => $request->request->get('id_club'),
            'salario' => $request->request->get('salario')
        ]);

        return $this->json([
            'message' => 'Player created successfully'
        ]);
    }

}