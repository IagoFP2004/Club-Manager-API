<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Connection;

class ClubController extends AbstractController
{
    #[Route('/club', name: 'club_list', methods: ['GET'])]//Ruta para listar clubs
    public function listClubs(Connection $connection): Response//Metodo para listar clubs
    {
        $sql = "SELECT id_club, nombre, fundacion, ciudad, estadio, entrenador, presupuesto FROM club";//Consulta para listar clubs
        $clubs = $connection->fetchAllAssociative($sql);//Ejecuta la consulta y devuelve los resultados
        //Devuelve los resultados en formato JSON
        return $this->json([
            'clubs' => $clubs
        ]);
    }

    #[Route('/club', name: 'club_insert', methods: ['POST'])]//Ruta para insertar clubs
    public function createClub(Connection $connection, Request $request): Response//Metodo para insertar clubs
    {

        $errores = [];
        // Obtener todos los campos
        $id_club = $request->request->get('id_club');
        $nombre = $request->request->get('nombre');
        $fundacion = $request->request->get('fundacion');
        $ciudad = $request->request->get('ciudad');
        $estadio = $request->request->get('estadio');
        $entrenador = $request->request->get('entrenador');
        $presupuesto = $request->request->get('presupuesto');

        // Verificar que todos los campos estén presentes
        if (empty($id_club) || empty($nombre) || empty($fundacion) || empty($ciudad) || empty($estadio) || empty($entrenador) || empty($presupuesto)) {
            return $this->json(['error' => 'Todos los campos son requeridos'], 400);
        }

        // Verificar si el club ya existe
        $sql = "SELECT id_club FROM club WHERE id_club = :id_club";
        $existingClub = $connection->fetchAssociative($sql, ['id_club' => $id_club]);
        
        if ($existingClub) {
           $errores['id_club'] = 'El club ya existe';
        }

        // Verificar si el nombre ya existe
        $sql = "SELECT nombre FROM club WHERE nombre = :nombre";
        $existingClubName = $connection->fetchAssociative($sql, ['nombre' => $nombre]);
        
        if ($existingClubName) {
            $errores['nombre'] = 'El nombre del club ya existe';
        }

        // Verificar si el estadio ya existe
        $sql = "SELECT estadio FROM club WHERE estadio = :estadio";
        $existingClubEstadio = $connection->fetchAssociative($sql, ['estadio' => $estadio]);
        
        if ($existingClubEstadio) {
           $errores['estadio'] = 'El estadio ya existe';
        }

        // Verificar si el entrenador ya existe
        $sql = "SELECT entrenador FROM club WHERE entrenador = :entrenador";
        $existingClubEntrenador = $connection->fetchAssociative($sql, ['entrenador' => $entrenador]);
        
        if ($existingClubEntrenador) {
            $errores['entrenador'] = 'El entrenador ya existe';
        }

        if (!empty($errores)) {
            return $this->json(['error' => $errores], 400);
        }

        $sql = "INSERT INTO club (id_club, nombre, fundacion, ciudad, estadio, entrenador, presupuesto) 
        VALUES (:id_club, :nombre, :fundacion, :ciudad, :estadio, :entrenador, :presupuesto)";//Consulta para insertar clubs
        $connection->executeStatement($sql, [//Ejecuta la consulta y devuelve los resultados
            'id_club' => $request->request->get('id_club'),
            'nombre' => $request->request->get('nombre'),
            'fundacion' => $request->request->get('fundacion'),
            'ciudad' => $request->request->get('ciudad'),
            'estadio' => $request->request->get('estadio'),
            'entrenador' => $request->request->get('entrenador'),
            'presupuesto' => $request->request->get('presupuesto')
        ]);
        //Devuelve los resultados en formato JSON
        return $this->json([
            'message' => 'Club created successfully'
        ]);
    }

    #[Route('/club/{id_club}', name: 'club_update', methods: ['PUT'])]//Ruta para actualizar clubs
    public function updateClub(Connection $connection, $id_club, Request $request): Response//Metodo para actualizar clubs
    {
        $body = $request->getContent();
        $jsonData = json_decode($body, true);
        $updateFields = [];
        $data = ['id_club' => $id_club];
        if (isset($jsonData['nombre'])) {
            $updateFields[] = 'nombre = :nombre';
            $data['nombre'] = $jsonData['nombre'];
        }
        if (isset($jsonData['fundacion'])) {
            $updateFields[] = 'fundacion = :fundacion';
            $data['fundacion'] = $jsonData['fundacion'];
        }
        if (isset($jsonData['ciudad'])) {
            $updateFields[] = 'ciudad = :ciudad';
            $data['ciudad'] = $jsonData['ciudad'];
        }
        if (isset($jsonData['estadio'])) {
            $updateFields[] = 'estadio = :estadio';
            $data['estadio'] = $jsonData['estadio'];
        }
        if (isset($jsonData['entrenador'])) {
            $updateFields[] = 'entrenador = :entrenador';
            $data['entrenador'] = $jsonData['entrenador'];
        }
        if (isset($jsonData['presupuesto'])) {
            $updateFields[] = 'presupuesto = :presupuesto';
            $data['presupuesto'] = $jsonData['presupuesto'];
        }

        if (empty($updateFields)) {
            return $this->json(['error' => 'No hay campos para actualizar'], 400);
        }

        $sql = "UPDATE club SET " . implode(', ', $updateFields) . " WHERE id_club = :id_club";//Consulta para actualizar clubs
        $result = $connection->executeStatement($sql, $data);//Ejecuta la consulta y devuelve los resultados
        //Devuelve los resultados en formato JSON
        return $this->json([
            'message' => 'Club updated successfully'
        ]);
    }

    #[Route('/club/{id_club}', name: 'club_delete', methods: ['DELETE'])]//Ruta para eliminar clubs
    public function deleteClub(Connection $connection, $id_club): Response//Metodo para eliminar clubs
    {
        $sql = "DELETE FROM club WHERE id_club = :id_club";//Consulta para eliminar clubs
        $connection->executeStatement($sql, ['id_club' => $id_club]);//Ejecuta la consulta y devuelve los resultados
        //Devuelve los resultados en formato JSON
        return $this->json([
            'message' => 'Club deleted successfully'
        ]);
    }

    #[Route('/club/{id_club}', name: 'club_get', methods: ['GET'])]//Ruta para obtener un club en concreto
    public function getClub(Connection $connection, $id_club): Response//Metodo para obtener un club en concreto
    {
        $sql = "SELECT * FROM club WHERE id_club = :id_club";//Consulta para obtener un club en concreto
        $club = $connection->fetchAssociative($sql, ['id_club' => $id_club]);//Ejecuta la consulta y devuelve los resultados

        if (!$club) {//Si no se encuentra el club, devuelve false
            return $this->json(false);
        }

        //Devuelve los resultados en formato JSON
        return $this->json($club);
    }
        
}