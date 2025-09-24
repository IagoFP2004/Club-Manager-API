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

    #[Route('/players/{id}', name: 'player_delete', methods: ['DELETE'])]
    public function deletePlayer(EntityManagerInterface $entityManager, $id): Response
    {
        $player = $entityManager->getRepository(Player::class)->find($id);

        if(!$player){
            return $this->json(['error' => 'Player not found'], 404);
        }

        $entityManager->remove($player);
        $entityManager->flush();

        return $this->json(['message' => 'Player deleted successfully']);
    }

    #[Route('/players', name: 'player_create', methods: ['POST'])]
    public function createPlayer(EntityManagerInterface $entityManager, Request $request): Response
    {
        // Obtener los datos del JSON
        $body = $request->getContent();
        $jsonData = json_decode($body, true);

        // Verificar si el JSON es válido
        if (!$jsonData) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        // Obtener los datos del JSON
        $nombre = $jsonData['nombre'] ?? null;
        $apellidos = $jsonData['apellidos'] ?? null;
        $dorsal = $jsonData['dorsal'] ?? null;
        $salario = $jsonData['salario'] ?? null;
        $id_club = $jsonData['id_club'] ?? null;

        //Todos los campos son requeridos si un campo falta lanza la alerta
        if (empty($nombre) || empty($apellidos) || empty($dorsal) || empty($salario) || empty($id_club)) {
            return $this->json(['error' => 'Todos los campos son requeridos'], 400);
        }

        // Validar que el club existe
        $club = $entityManager->getRepository(Club::class)->find($id_club);
        if (!$club) {
            return $this->json(['error' => 'Club not found'], 404);
        }

        //Creamos al jugador y le asignamos los datos
        $player = new Player();
        $player->setNombre($nombre);
        $player->setApellidos($apellidos);
        $player->setDorsal($dorsal);
        $player->setSalario($salario);
        $player->setClub($club);

        //Guardamos el jugador en la base de datos
        $entityManager->persist($player);
        $entityManager->flush();

        //Devolvemos el mensaje de éxito
        return $this->json(['message' => 'Player created successfully']);
        
    }

    #[Route('/players/{id}', name: 'player_update', methods: ['PUT'])]
    public function updatePlayer(EntityManagerInterface $entityManager, $id, Request $request): Response
    {
        $player = $entityManager->getRepository(Player::class)->find($id);
        
        if(!$player){
            return $this->json(['error' => 'Player not found'], 404);
        }

        // Para ver todos los dorsales del club del jugador
        $club = $player->getClub();
        $playersDelClub = $entityManager->getRepository(Player::class)->findBy(['club' => $club]);
        
        //Guardamos los dorsales del club del jugador
        $dorsales = [];
        foreach($playersDelClub as $p){
            $dorsales[] = $p->getDorsal();
        }
        
        //Obtenemos los datos del JSON
        $body = $request->getContent();
        $jsonData = json_decode($body, true);

        if(empty($jsonData)){
            return $this->json(['error' => 'No hay datos para actualizar'], 400);
        }

        //Hacemos validaciones
        if(isset($jsonData['dni'])){
            return $this->json(['error' => 'El DNI no puede ser modificado'], 400);
        }
        if(isset($jsonData['nombre'])){
            $player->setNombre($jsonData['nombre']);
        }
        if(isset($jsonData['apellidos'])){
            $player->setApellidos($jsonData['apellidos']);
        }
        if(isset($jsonData['dorsal'])){
            $player->setDorsal($jsonData['dorsal']);
            if($jsonData['dorsal'] <= 0 || $jsonData['dorsal'] > 99) {
                return $this->json(['error' => 'El dorsal debe ser mayor que 0 y menor que 100'], 400);
            }else if(in_array($jsonData['dorsal'], $dorsales)){
                return $this->json(['error' => 'El dorsal ya existe en el club'], 400);
            }
        }
        if(isset($jsonData['salario'])){
            $player->setSalario($jsonData['salario']);
            if($jsonData['salario'] <= 0){
                return $this->json(['error' => 'El salario no puede ser 0 o negativo'], 400);
            }
        }
        if(isset($jsonData['id_club'])){
            $club = $entityManager->getRepository(Club::class)->find($jsonData['id_club']);
            if(!$club){
                return $this->json(['error' => 'Club not found'], 404);
            }
            $player->setClub($club);
        }

        //Actualizamos el jugador en la base de datos
        $entityManager->flush();

        //Devolvemos el mensaje de éxito
        return $this->json(['message' => 'Player updated successfully']);

    }    
    
}