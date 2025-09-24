<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Club;
use App\Entity\Player;
use App\Entity\Coach;

class ClubController extends AbstractController
{
    #[Route('/clubs', name: 'club_list', methods: ['GET'])]
    public function listClubs(EntityManagerInterface $entityManager): Response
    {
        $clubs = $entityManager->getRepository(Club::class)->findAll();

        if(!$clubs){
            return $this->json(['message' => 'No hay clubs registrados'], 400);
        }

        $data = [];
        foreach($clubs as $club){
            // Obtener entrenadores del club
            $entrenador = '';
            if($club->getCoaches()->count() > 0){
                $coach = $club->getCoaches()->first();
                $entrenador = $coach->getNombre() . ' ' . $coach->getApellidos();
            }
            
            // Obtener jugadores del club
            $jugadores = [];
            foreach($club->getPlayers() as $player){
                $jugadores[] = $player->getNombre() . ' ' . $player->getApellidos();
            }

            $data[] = [
                'id_club' => $club->getIdClub(),
                'nombre' => $club->getNombre(),
                'fundacion' => $club->getFundacion(),
                'ciudad' => $club->getCiudad(),
                'estadio' => $club->getEstadio(),
                'presupuesto' => $club->getPresupuesto(),
                'entrenador' => $entrenador,
                'jugadores' => !empty($jugadores) ? $jugadores : 'Sin jugadores'
            ];
        }

        return $this->json(['clubs' => $data]);
    }

    #[Route('/clubs/{id}', name: 'club_get', methods: ['GET'])]
    public function getClub(EntityManagerInterface $entityManager, $id): Response
    {
        $club = $entityManager->getRepository(Club::class)->find($id);

        if(!$club){
            return $this->json(['error' => 'Club not found'], 404);
        }

        // Obtener entrenadores del club
        $entrenadores = [];
        foreach($club->getCoaches() as $coach){
            $entrenadores[] = $coach->getNombre() . ' ' . $coach->getApellidos();
        }

        // Obtener jugadores del club
        $jugadores = [];
        foreach($club->getPlayers() as $player){
            $jugadores[] = $player->getNombre() . ' ' . $player->getApellidos();
        }

        $data = [
            'id_club' => $club->getIdClub(),
            'nombre' => $club->getNombre(),
            'fundacion' => $club->getFundacion(),
            'ciudad' => $club->getCiudad(),
            'estadio' => $club->getEstadio(),
            'presupuesto' => $club->getPresupuesto(),
            'entrenador' => !empty($entrenadores) ? $entrenadores : 'Sin entrenadores',
            'jugadores' => !empty($jugadores) ? $jugadores : 'Sin jugadores'
        ];

        return $this->json(['club' => $data]);
    }

    #[Route('/clubs/{id}', name: 'club_delete', methods: ['DELETE'])]
    public function deleteClub(EntityManagerInterface $entityManager, $id): Response
    {
        $club = $entityManager->getRepository(Club::class)->find($id);
        
        if(!$club){
            return $this->json(['error' => 'Club not found'], 404);
        }

        $entityManager->remove($club);
        $entityManager->flush();

        return $this->json(['message' => 'Club deleted successfully']);
    }

    #[Route('/clubs', name: 'club_create', methods: ['POST'])]
    public function createClub(EntityManagerInterface $entityManager, Request $request): Response
    {
        $body = $request->getContent();
        $jsonData = json_decode($body, true);
        
        if(!$jsonData){
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        $id_club = $jsonData['id_club'] ?? null;
        $nombre = $jsonData['nombre'] ?? null;
        $fundacion = $jsonData['fundacion'] ?? null;
        $ciudad = $jsonData['ciudad'] ?? null;
        $estadio = $jsonData['estadio'] ?? null;
        $presupuesto = $jsonData['presupuesto'] ?? null;

        if(empty($id_club) || empty($nombre) || empty($fundacion) || empty($ciudad) || empty($estadio) || empty($presupuesto)){
            return $this->json(['error' => 'Todos los campos son requeridos'], 400);
        }

        $errores = [];

        if(empty($id_club)){
            $errores['id_club'] = 'El id_club es requerido';
        }else if(strlen($id_club) < 3 || strlen($id_club) > 5){
            $errores['id_club'] = 'El id_club debe tener entre 3 y 5 caracteres';
        }else if($entityManager->getRepository(Club::class)->findOneBy(['id_club' => $id_club])){
            $errores['id_club'] = 'El id_club ya existe';
        }
        if(empty($nombre)){
            $errores['nombre'] = 'El nombre es requerido';
        }
        if(empty($fundacion)){
            $errores['fundacion'] = 'La fundacion es requerida';
        }else if($fundacion < 1857 || $fundacion > date('Y')){
            $errores['fundacion'] = 'La fundacion debe ser entre 1800 y 2025';
        }
        if(empty($ciudad)){
            $errores['ciudad'] = 'La ciudad es requerida';
        }
        if(empty($estadio)){
            $errores['estadio'] = 'El estadio es requerido';
        }
        if(empty($presupuesto)){
            $errores['presupuesto'] = 'El presupuesto es requerido';
        }else if($presupuesto <= 0){
            $errores['presupuesto'] = 'El presupuesto no puede ser 0 o negativo';
        }

        if(!empty($errores)){
            return $this->json(['error' => $errores], 400);
        }

        $club = new Club();
        $club->setIdClub($id_club);
        $club->setNombre($nombre);
        $club->setFundacion($fundacion);
        $club->setCiudad($ciudad);
        $club->setEstadio($estadio);
        $club->setPresupuesto($presupuesto);

        $entityManager->persist($club);
        $entityManager->flush();

        return $this->json(['message' => 'Club created successfully']);
    }

    #[Route('/clubs/{id}', name: 'club_update', methods: ['PUT'])]
    public function updateClub(EntityManagerInterface $entityManager, $id, Request $request): Response
    {
        $club = $entityManager->getRepository(Club::class)->find($id);
        
        if(!$club){
            return $this->json(['error' => 'Club not found'], 404);
        }

        $body = $request->getContent();
        $jsonData = json_decode($body, true);

        $errores = [];
        if(!$jsonData){
            return $this->json(['error' => 'No hay datos para actualizar'], 400);
        }

        if(isset($jsonData['id_club'])){
            $errores['id_club'] = 'El id_club no puede ser modificado';
        }
        if(isset($jsonData['nombre'])){
            $club->setNombre($jsonData['nombre']);
        }
        if(isset($jsonData['fundacion'])){
            if($jsonData['fundacion'] < 1857 || $jsonData['fundacion'] > date('Y')){
                $errores['fundacion'] = 'La fundacion debe ser entre 1800 y 2025';
            }else{
                $club->setFundacion($jsonData['fundacion']);
            }
        }
        if(isset($jsonData['ciudad'])){
            $club->setCiudad($jsonData['ciudad']);
        }
        if(isset($jsonData['estadio'])){
            $club->setEstadio($jsonData['estadio']);
        }
        if(isset($jsonData['presupuesto'])){
            if($jsonData['presupuesto'] <= 0){
                $errores['presupuesto'] = 'El presupuesto no puede ser 0 o negativo';
            }else{
                $club->setPresupuesto($jsonData['presupuesto']);
            }
        }
        
        // Verificar si hay errores y devolverlos
        if(!empty($errores)){
            return $this->json(['error' => $errores], 400);
        }
        
        $entityManager->flush();

        return $this->json(['message' => 'Club updated successfully']);
    }

}
