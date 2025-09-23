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
        
}