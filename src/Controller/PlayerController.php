<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Player;
use App\Entity\Club;

//Clase controladora de players
class PlayerController extends AbstractController
{
    #[Route('/players', name: 'player_list', methods: ['GET'])] //Ruta para listar players
    public function listPlayers(EntityManagerInterface $entityManager): Response
    {
        $players = $entityManager->getRepository(Player::class)->findAll();//Obtiene todos los players
        //Devuelve los resultados en formato JSON
        return $this->json([
            'players' => $players,
        ]);
    }

    #[Route('/players', name: 'player_insert', methods: ['POST'])] //Ruta para insertar players
    public function createPlayer(EntityManagerInterface $entityManager, Request $request): Response //Metodo para insertar players
    {
        $errores = [];
        // Obtener todos los campos
        $nombre = $request->request->get('nombre');
        $apellidos = $request->request->get('apellidos');
        $dorsal = $request->request->get('dorsal');
        $id_club = $request->request->get('id_club');
        $salario = $request->request->get('salario');

        // Verificar que todos los campos estén presentes
        if (empty($nombre) || empty($apellidos) || empty($dorsal) || empty($id_club) || empty($salario)) {
            return $this->json(['error' => 'Todos los campos son requeridos'], 400);
        }

        // Verificar si el club existe
        $club = $entityManager->getRepository(Club::class)->find($id_club);
        if (!$club) {
            $errores['id_club'] = 'El club no existe';
        }

        // Verificar si el dorsal ya existe en el club
        $existingPlayer = $entityManager->getRepository(Player::class)->findOneBy([
            'dorsal' => $dorsal,
            'club' => $club
        ]);
        if ($existingPlayer) {
            $errores['dorsal'] = 'El dorsal ya existe en este club';
        }

        if (!empty($errores)) {
            return $this->json(['error' => $errores], 400);
        }

        // Crear nuevo player
        $player = new Player();
        $player->setNombre($nombre);
        $player->setApellidos($apellidos);
        $player->setDorsal($dorsal);
        $player->setClub($club);
        $player->setSalario($salario);

        $entityManager->persist($player);
        $entityManager->flush();

        //Devuelve los resultados en formato JSON
        return $this->json([
            'message' => 'Player created successfully'
        ]);
    }

    #[Route('/players/{id}', name: 'player_delete', methods: ['DELETE'])]
    public function deletePlayer(EntityManagerInterface $entityManager, $id): Response
    {
        $player = $entityManager->getRepository(Player::class)->find($id);
        
        if (!$player) {
            return $this->json(['error' => 'Player not found'], 404);
        }

        $entityManager->remove($player);
        $entityManager->flush();
        
        //Devuelve los resultados en formato JSON
        return $this->json([
            'message' => 'Player deleted successfully'
        ]);
    }

    #[Route('/players/{id}', name: 'player_update', methods: ['PUT'])]//Ruta para actualizar players
    public function updatePlayer(EntityManagerInterface $entityManager, $id, Request $request): Response//Metodo para actualizar players
    {
        $player = $entityManager->getRepository(Player::class)->find($id);
        
        if (!$player) {
            return $this->json(['error' => 'Player not found'], 404);
        }

        // Obtener datos del JSON
        $body = $request->getContent();
        $jsonData = json_decode($body, true);
        
        if (isset($jsonData['nombre'])) {
            $player->setNombre($jsonData['nombre']);
        }
        if (isset($jsonData['apellidos'])) {
            $player->setApellidos($jsonData['apellidos']);
        }
        if (isset($jsonData['dorsal'])) {
            $player->setDorsal($jsonData['dorsal']);
        }
        if (isset($jsonData['id_club'])) {
            $club = $entityManager->getRepository(Club::class)->find($jsonData['id_club']);
            if ($club) {
                $player->setClub($club);
            }
        }
        if (isset($jsonData['salario'])) {
            $player->setSalario($jsonData['salario']);
        }

        $entityManager->flush();
        
        //Devuelve los resultados en formato JSON
        return $this->json([
            'message' => 'Player updated successfully'
        ]);
    }

    #[Route('/players/{id}', name: 'player_get', methods: ['GET'])]//Ruta para obtener un player en concreto
    public function getPlayer(EntityManagerInterface $entityManager, $id): Response//Metodo para obtener un player en concreto
    {
        $player = $entityManager->getRepository(Player::class)->find($id);//Obtiene el player por ID

        if (!$player) {//Si no se encuentra el player, devuelve error
            return $this->json(['error' => 'Player not found'], 404);
        }

        //Devuelve los resultados en formato JSON
        return $this->json($player);
    }
}