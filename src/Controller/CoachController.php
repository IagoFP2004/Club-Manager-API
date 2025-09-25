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
            $club = $coach->getClub();
            $coachesData[] = [
                'id' => $coach->getId(),
                'dni' => $coach->getDni(),
                'nombre' => $coach->getNombre(),
                'apellidos' => $coach->getApellidos(),
                'salario' => $coach->getSalario(),
                'club' => $club ? $club->getNombre() : "Sin club"
            ];
        }
        
        return $this->json([
            'coaches' => $coachesData,
        ]);
    }

    #[Route('/coaches/{id}', name: 'coach_get', methods: ['GET'])]
    public function getCoach(EntityManagerInterface $entityManager, $id): Response
    {
        $coach = $entityManager->getRepository(Coach::class)->find($id);

        if(!$coach){
            return $this->json(['error' => 'Coach not found'], 404);
        }

        $club = $coach->getClub();
        $data = [
            'dni' => $coach->getDni(),
            'nombre' => $coach->getNombre(),
            'apellidos' => $coach->getApellidos(),
            'salario' => $coach->getSalario(),
            'club' => $club ? $club->getNombre() : "Sin club"
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

        $dni = $data['dni'] ?? $data['Dni'] ?? $data['DNI'] ?? null;
        $nombre = $data['nombre'] ?? $data['Nombre'] ?? $data['NOMBRE'] ?? null;
        $apellidos = $data['apellidos'] ?? $data['Apellidos'] ?? $data['APELLIDOS'] ?? null;
        $salario = $data['salario'] ?? $data['Salario'] ?? $data['SALARIO'] ?? null;
        $id_club = $data['id_club'] ?? $data['Id_club'] ?? $data['ID_CLUB'] ?? null;

        // Verificar que todos los campos estén presentes (id_club es opcional)
        if (empty($dni) || empty($nombre) || empty($apellidos) || empty($salario)) {
            return $this->json(['error' => 'Todos los campos son requeridos'], 400);
        }

        // Verificar si el DNI ya existe
        $existingCoach = $entityManager->getRepository(Coach::class)->findOneBy(['dni' => $dni]);
        if ($existingCoach) {
            $errores['dni'] = 'El DNI ya existe';
        }

        // Verificar si el club existe (solo si se proporciona)
        $club = null;
        if (!empty($id_club)) {
            $club = $entityManager->getRepository(Club::class)->findOneBy(['id_club' => $id_club]);
            if (!$club) {
                $errores['id_club'] = 'El club no existe';
            } else {
                // Verificar si el club ya tiene un entrenador
                $existingCoachInClub = $entityManager->getRepository(Coach::class)->findOneBy(['club' => $club]);
                if ($existingCoachInClub) {
                    $errores['id_club'] = 'Este club ya tiene un entrenador asignado';
                }
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
        $coach->setSalario($salario);
        $coach->setClub($club);

        $entityManager->persist($coach);
        $entityManager->flush();

        return $this->json([
            'message' => 'Coach created successfully'
        ]);
    }

    //Funcion para eliminar un coach
    #[Route('/coaches/{id}', name: 'coach_delete', methods: ['DELETE'])]
    public function deleteCoach(EntityManagerInterface $entityManager, $id): Response
    {
        $coach = $entityManager->getRepository(Coach::class)->find($id);

        if(!$coach){
            return $this->json(['error' => 'Coach not found'], 404);
        }

        $entityManager->remove($coach);
        $entityManager->flush();

        return $this->json([
            'message' => 'Coach deleted successfully'
        ]);
    }

    //Funcion para quitar un entrenador de su equipo
    #[Route('/coaches/{id}/remove-from-team', name: 'coach_remove_from_team', methods: ['PATCH'])]
    public function removeCoachFromTeam(EntityManagerInterface $entityManager, $id): Response
    {
        $coach = $entityManager->getRepository(Coach::class)->find($id);

        if(!$coach){
            return $this->json(['error' => 'Coach not found'], 404);
        }

        $clubAnterior = $coach->getClub();
        $coach->setClub(null);
        $entityManager->flush();

        $mensaje = $clubAnterior 
            ? "Entrenador removido del equipo " . $clubAnterior->getNombre()
            : "El entrenador no estaba en ningún equipo";

        return $this->json([
            'message' => $mensaje,
            'coach' => [
                'id' => $coach->getId(),
                'nombre' => $coach->getNombre(),
                'apellidos' => $coach->getApellidos(),
                'club' => null
            ]
        ]);
    }

    //Funcion para actualizar un coach
    #[Route('/coaches/{id}', name: 'coach_update', methods: ['PUT'])]
    public function updateCoach(EntityManagerInterface $entityManager, $id, Request $request): Response
    {
        $coach = $entityManager->getRepository(Coach::class)->find($id);
        
        if(!$coach){
            return $this->json(['error' => 'Coach not found'], 404);
        }

        $body = $request->getContent();
        $jsonData = json_decode($body, true);
        
        if(!$jsonData){
            return $this->json(['error' => 'JSON inválido'], 400);
        }

        // Verificar si se intenta cambiar el DNI
        if(isset($jsonData['dni']) || isset($jsonData['Dni']) || isset($jsonData['DNI'])){
            return $this->json(['error' => 'El DNI no puede ser modificado'], 400);
        }

        // Actualizar campos si están presentes
        if(isset($jsonData['nombre']) || isset($jsonData['Nombre']) || isset($jsonData['NOMBRE'])){
            $nombre = $jsonData['nombre'] ?? $jsonData['Nombre'] ?? $jsonData['NOMBRE'];
            $coach->setNombre($nombre);
        }
        
        if(isset($jsonData['apellidos']) || isset($jsonData['Apellidos']) || isset($jsonData['APELLIDOS'])){
            $apellidos = $jsonData['apellidos'] ?? $jsonData['Apellidos'] ?? $jsonData['APELLIDOS'];
            $coach->setApellidos($apellidos);
        }
        
        if(isset($jsonData['salario']) || isset($jsonData['Salario']) || isset($jsonData['SALARIO'])){
            $salario = $jsonData['salario'] ?? $jsonData['Salario'] ?? $jsonData['SALARIO'];
            if($salario <= 0){
                return $this->json(['error' => 'El salario no puede ser 0 o negativo'], 400);
            }else{
                $coach->setSalario($salario);
            }
        }
        
        if(isset($jsonData['id_club']) || isset($jsonData['Id_club']) || isset($jsonData['ID_CLUB'])){
            $id_club = $jsonData['id_club'] ?? $jsonData['Id_club'] ?? $jsonData['ID_CLUB'];
            
            if(empty($id_club)) {
                // Si id_club está vacío, quitar el club del entrenador
                $coach->setClub(null);
            } else {
                // Si id_club tiene valor, buscar y asignar el club
                $club = $entityManager->getRepository(Club::class)->findOneBy(['id_club' => $id_club]);
                if(!$club){
                    return $this->json(['error' => 'El club no existe'], 400);
                }
                
                // Verificar si el club ya tiene otro entrenador (excluyendo el actual)
                $existingCoachInClub = $entityManager->getRepository(Coach::class)->findOneBy(['club' => $club]);
                if($existingCoachInClub && $existingCoachInClub->getId() !== $id){
                    return $this->json(['error' => 'Este club ya tiene un entrenador asignado'], 400);
                }
                
                $coach->setClub($club);
            }
        }

        $entityManager->flush();

        return $this->json([
            'message' => 'Coach updated successfully'
        ]);
    }
}