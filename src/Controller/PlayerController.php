<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Connection;

//Clase controladora de players
class PlayerController extends AbstractController
{
    #[Route('/player', name: 'player_list', methods: ['GET'])] //Ruta para listar players
    public function listPlayers(Connection $connection): Response
    {
        $sql = "SELECT nombre,apellidos,dorsal,id_club,salario FROM player"; //Consulta para listar players
        $players = $connection->fetchAllAssociative($sql);// Ejecuta la consulta y devuelve los resultados
        //Devuelve los resultados en formato JSON
        return $this->json([
            'players' => $players,
        ]);
    }

    #[Route('/player', name: 'player_insert', methods: ['POST'])] //Ruta para insertar players
    public function createPlayer(Connection $connection, Request $request): Response //Metodo para insertar players
    {
        $sql = "INSERT INTO player (nombre,apellidos,dorsal,id_club,salario)
        VALUES (:nombre, :apellidos, :dorsal, :id_club, :salario)";
        $connection->executeStatement($sql, [ //Ejecuta la consulta y devuelve los resultados
            //Parametros de la consulta
            'nombre' => $request->request->get('nombre'),
            'apellidos' => $request->request->get('apellidos'),
            'dorsal' => $request->request->get('dorsal'),
            'id_club' => $request->request->get('id_club'),
            'salario' => $request->request->get('salario')
        ]);
        //Devuelve los resultados en formato JSON
        return $this->json([ //Devuelve los resultados en formato JSON
            'message' => 'Player created successfully'
        ]);
    }

    #[Route('/player/{id_player}', name: 'player_delete', methods: ['DELETE'])]
    public function deletePlayer(Connection $connection, $id_player): Response
    {
        $sql = "DELETE FROM player WHERE id_player = :id_player"; //Consulta para eliminar players
        $connection->executeStatement($sql, [ //Ejecuta la consulta y devuelve los resultados
            'id_player' => $id_player
        ]);
        //Devuelve los resultados en formato JSON
        return $this->json([ //Devuelve los resultados en formato JSON
            'message' => 'Player deleted successfully'
        ]);
    }

    #[Route('/player/{id_player}', name: 'player_update', methods: ['PUT'])]//Ruta para actualizar players
    public function updatePlayer(Connection $connection, $id_player, Request $request): Response//Metodo para actualizar players
    {
        // Obtener datos del JSON
        $body = $request->getContent();//Obtener datos del JSON
        $jsonData = json_decode($body, true);//Decodificar el JSON
        
        // Construir la consulta UPDATE solo con los campos que se envían
        $updateFields = [];//Construir la consulta UPDATE solo con los campos que se envían
        $data = ['id_player' => $id_player];
        //Construir la consulta UPDATE solo con los campos que se envían
        if (isset($jsonData['nombre'])) {
            $updateFields[] = 'nombre = :nombre';
            $data['nombre'] = $jsonData['nombre'];
        }
        if (isset($jsonData['apellidos'])) {
            $updateFields[] = 'apellidos = :apellidos';
            $data['apellidos'] = $jsonData['apellidos'];
        }
        if (isset($jsonData['dorsal'])) {
            $updateFields[] = 'dorsal = :dorsal';
            $data['dorsal'] = $jsonData['dorsal'];
        }
        if (isset($jsonData['id_club'])) {
            $updateFields[] = 'id_club = :id_club';
            $data['id_club'] = $jsonData['id_club'];
        }
        if (isset($jsonData['salario'])) {
            $updateFields[] = 'salario = :salario';
            $data['salario'] = $jsonData['salario'];
        }
        
        if (empty($updateFields)) {
            return $this->json(['error' => 'No hay campos para actualizar'], 400);
        }
        
        $sql = "UPDATE player SET " . implode(', ', $updateFields) . " WHERE id_player = :id_player";//consulta para actualizar players
        $result = $connection->executeStatement($sql, $data);//Ejecuta la consulta y devuelve los resultados
        //Devuelve los resultados en formato JSON
        return $this->json([
            'message' => 'Player updated successfully',
        ]);
    }

    #[Route('/player/{id_player}', name: 'player_get', methods: ['GET'])]//Ruta para obtener un player en concreto
    public function getPlayer(Connection $connection, $id_player): Response//Metodo para obtener un player en concreto
    {
        $sql = "SELECT nombre,apellidos,dorsal,id_club,salario FROM player WHERE id_player = :id_player";//consulta para obtener un player en concreto
        $player = $connection->fetchAssociative($sql, ['id_player' => $id_player]);//Ejecuta la consulta y devuelve los resultados
        //Devuelve los resultados en formato JSON
        //Si no se encuentra el player, devuelve false y si lo encuentra, devuelve el player
        if (!$player) {
            return $this->json(false);
        }
        
        return $this->json($player);
    }
}