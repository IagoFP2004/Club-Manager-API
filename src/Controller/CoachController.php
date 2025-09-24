<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Coach;
use App\Entity\Club;

class CoachController extends AbstractController
{
    //Funcion que muestra el listado
    #[Route('/coaches', name: 'coach_list', methods: ['GET'])]
    public function listCoaches(EntityManagerInterface $entityManager): Response
    {
        $coaches = $entityManager->getRepository(Coach::class)->findAll();
        
        // Si no hay coaches, devolver array vacío con mensaje
        if (empty($coaches)) {
            return $this->json([
                'coaches' => [],
                'message' => 'No hay coaches registrados',
            ]);
        }
        
        // Convertir entidades a arrays para serialización
        $coachesData = [];
        foreach ($coaches as $coach) {
            $coachesData[] = [
                'id' => $coach->getId(),
                'dni' => $coach->getDni(),
                'nombre' => $coach->getNombre(),
                'apellidos' => $coach->getApellidos(),
                'sueldo' => $coach->getSueldo()
            ];
        }
        
        return $this->json([
            'coaches' => $coachesData,
        ]);
    }

    #[Route('/coaches/{dni}', name: 'coach_get', methods: ['GET'])]
    public function getCoach(EntityManagerInterface $entityManager, $dni): Response
    {
        $coach = $entityManager->getRepository(Coach::class)->findOneBy(['dni' => $dni]);

        if(!$coach){
            return $this->json(['error' => 'Coach not found'], 404);
        }

        $data = [
            'dni' => $coach->getDni(),
            'nombre' => $coach->getNombre(),
            'apellidos' => $coach->getApellidos(),
            'sueldo' => $coach->getSueldo(),
            'club' => $coach->getClub()->getNombre()
        ];

        return $this->json($data);
    }

    #[Route('/coaches', name: 'coach_insert', methods: ['POST'])]
    public function createCoach(EntityManagerInterface $entityManager, Request $request): Response
    {
        $errores = [];
        
        // Obtener datos del JSON
        $body = $request->getContent();
        $data = json_decode($body, true);

        if (!$data) {
            return $this->json(['error' => 'JSON inválido'], 400);
        }

        $dni = $data['dni'] ?? null;
        $nombre = $data['nombre'] ?? null;
        $apellidos = $data['apellidos'] ?? null;
        $sueldo = $data['sueldo'] ?? null;
        $id_club = $data['id_club'] ?? null;

        // Verificar que todos los campos estén presentes
        if (empty($dni) || empty($nombre) || empty($apellidos) || empty($sueldo) || empty($id_club)) {
            return $this->json(['error' => 'Todos los campos son requeridos'], 400);
        }

        // Verificar si el DNI ya existe
        $existingCoach = $entityManager->getRepository(Coach::class)->findOneBy(['dni' => $dni]);
        if ($existingCoach) {
            $errores['dni'] = 'El DNI ya existe';
        }

        // Verificar si el club existe
        $club = $entityManager->getRepository(Club::class)->find($id_club);
        if (!$club) {
            $errores['id_club'] = 'El club no existe';
        } else {
            // Verificar si el club ya tiene un entrenador
            $existingCoachInClub = $entityManager->getRepository(Coach::class)->findOneBy(['club' => $club]);
            if ($existingCoachInClub) {
                $errores['id_club'] = 'Este club ya tiene un entrenador asignado';
            }
        }

        if (!empty($errores)) {
            return $this->json(['error' => $errores], 400);
        }

        // Crear nuevo coach
        $coach = new Coach();
        $coach->setDni($dni);
        $coach->setNombre($nombre);
        $coach->setApellidos($apellidos);
        $coach->setSueldo($sueldo);
        $coach->setClub($club);

        $entityManager->persist($coach);
        $entityManager->flush();

        return $this->json([
            'message' => 'Coach created successfully'
        ]);
    }

    //Funcion para eliminar un coach
    #[Route('/coaches/{dni}', name: 'coach_delete', methods: ['DELETE'])]
    public function deleteCoach(EntityManagerInterface $entityManager, $dni): Response
    {
        $coach = $entityManager->getRepository(Coach::class)->findOneBy(['dni' => $dni]);

        if(!$coach){
            return $this->json(['error' => 'Coach not found'], 404);
        }

        $entityManager->remove($coach);
        $entityManager->flush();

        return $this->json([
            'message' => 'Coach deleted successfully'
        ]);
    }

    //Funcion para actualizar un coach
    #[Route('/coaches/{dni}', name: 'coach_update', methods: ['PUT'])]
    public function updateCoach(EntityManagerInterface $entityManager, $dni, Request $request): Response
    {
        $coach = $entityManager->getRepository(Coach::class)->findOneBy(['dni' => $dni]);
        
        if(!$coach){
            return $this->json(['error' => 'Coach not found'], 404);
        }

        $body = $request->getContent();
        $jsonData = json_decode($body, true);
        
        if(!$jsonData){
            return $this->json(['error' => 'JSON inválido'], 400);
        }

        // Verificar si se intenta cambiar el DNI
        if(isset($jsonData['dni'])){
            return $this->json(['error' => 'El DNI no puede ser modificado'], 400);
        }

        // Actualizar campos si están presentes
        if(isset($jsonData['nombre'])){
            $coach->setNombre($jsonData['nombre']);
        }
        
        if(isset($jsonData['apellidos'])){
            $coach->setApellidos($jsonData['apellidos']);
        }
        
        if(isset($jsonData['sueldo'])){
            if($jsonData['sueldo'] <= 0){
                return $this->json(['error' => 'El sueldo no puede ser 0 o negativo'], 400);
            }else{
                $coach->setSueldo($jsonData['sueldo']);
            }
        }
        
        if(isset($jsonData['id_club'])){
            $club = $entityManager->getRepository(Club::class)->find($jsonData['id_club']);
            if(!$club){
                return $this->json(['error' => 'El club no existe'], 400);
            }
            
            // Verificar si el club ya tiene otro entrenador (excluyendo el actual)
            $existingCoachInClub = $entityManager->getRepository(Coach::class)->findOneBy(['club' => $club]);
            if($existingCoachInClub && $existingCoachInClub->getDni() !== $dni){
                return $this->json(['error' => 'Este club ya tiene un entrenador asignado'], 400);
            }
            
            $coach->setClub($club);
        }

        $entityManager->flush();

        return $this->json([
            'message' => 'Coach updated successfully'
        ]);
    }
}