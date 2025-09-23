<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Club;

class ClubController extends AbstractController
{
    #[Route('/clubs', name: 'club_list', methods: ['GET'])]//Ruta para listar clubs
    public function listClubs(EntityManagerInterface $entityManager): Response//Metodo para listar clubs
    {
        $clubs = $entityManager->getRepository(Club::class)->findAll();//Obtiene todos los clubs
        //Devuelve los resultados en formato JSON
        return $this->json([
            'clubs' => $clubs
        ]);
    }

    #[Route('/clubs', name: 'club_insert', methods: ['POST'])]//Ruta para insertar clubs
    public function createClub(EntityManagerInterface $entityManager, Request $request): Response//Metodo para insertar clubs
    {
        $errores = [];
        // Obtener todos los campos
        $id_club = $request->request->get('id_club');
        $nombre = $request->request->get('nombre');
        $fundacion = $request->request->get('fundacion');
        $ciudad = $request->request->get('ciudad');
        $estadio = $request->request->get('estadio');
        $presupuesto = $request->request->get('presupuesto');

        // Verificar que todos los campos estén presentes
        if (empty($id_club) || empty($nombre) || empty($fundacion) || empty($ciudad) || empty($estadio) || ($presupuesto === null || $presupuesto === '')) {
            return $this->json(['error' => 'Todos los campos son requeridos'], 400);
        }

        // Validar presupuesto
        if (!is_numeric($presupuesto)) {
            $errores['presupuesto'] = "El presupuesto debe ser un número válido";
        } elseif ($presupuesto == 0) {
            $errores['presupuesto'] = "El presupuesto del club no puede ser 0";
        } elseif ($presupuesto < 0) {
            $errores['presupuesto'] = "El presupuesto no puede ser negativo";
        }

        // Verificar si el club ya existe
        $existingClub = $entityManager->getRepository(Club::class)->find($id_club);
        if ($existingClub) {
            $errores['id_club'] = 'El club ya existe';
        }

        // Verificar si el nombre ya existe
        $existingClubName = $entityManager->getRepository(Club::class)->findOneBy(['nombre' => $nombre]);
        if ($existingClubName) {
            $errores['nombre'] = 'El nombre del club ya existe';
        }

        // Verificar si el estadio ya existe
        $existingClubEstadio = $entityManager->getRepository(Club::class)->findOneBy(['estadio' => $estadio]);
        if ($existingClubEstadio) {
            $errores['estadio'] = 'El estadio ya existe';
        }

        if (!empty($errores)) {
            return $this->json(['error' => $errores], 400);
        }

        // Crear nuevo club
        $club = new Club();
        $club->setIdClub($id_club);
        $club->setNombre($nombre);
        $club->setFundacion($fundacion);
        $club->setCiudad($ciudad);
        $club->setEstadio($estadio);
        $club->setPresupuesto($presupuesto);

        $entityManager->persist($club);
        $entityManager->flush();

        //Devuelve los resultados en formato JSON
        return $this->json([
            'message' => 'Club created successfully'
        ]);
    }

    #[Route('/clubs/{id_club}', name: 'club_update', methods: ['PUT'])]//Ruta para actualizar clubs
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
        if (isset($jsonData['presupuesto'])) {
            $updateFields[] = 'presupuesto = :presupuesto';
            $data['presupuesto'] = $jsonData['presupuesto'];
        }
        if($jsonData['presupuesto'] <= 0){
           return $this->json(['error' => 'El presupuesto no puede ser 0 o negativo'], 400);
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

    #[Route('/clubs/{id_club}', name: 'club_delete', methods: ['DELETE'])]//Ruta para eliminar clubs
    public function deleteClub(Connection $connection, $id_club): Response//Metodo para eliminar clubs
    {
        $sql = "DELETE FROM club WHERE id_club = :id_club";//Consulta para eliminar clubs
        $connection->executeStatement($sql, ['id_club' => $id_club]);//Ejecuta la consulta y devuelve los resultados
        //Devuelve los resultados en formato JSON
        return $this->json([
            'message' => 'Club deleted successfully'
        ]);
    }

    #[Route('/clubs/{id_club}', name: 'club_get', methods: ['GET'])]//Ruta para obtener un club en concreto
    public function getClub(EntityManagerInterface $entityManager, $id_club): Response//Metodo para obtener un club en concreto
    {
        $club = $entityManager->getRepository(Club::class)->find($id_club);//Obtiene el club por ID

        if (!$club) {//Si no se encuentra el club, devuelve error
            return $this->json(['error' => 'Club not found'], 404);
        }

        //Devuelve los resultados en formato JSON
        return $this->json($club);
    }
        
}