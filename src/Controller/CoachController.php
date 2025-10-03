<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Coach;
use App\Entity\Club;
use Knp\Component\Pager\PaginatorInterface;

class CoachController extends AbstractController
{
    // Constante con todos los caracteres especiales prohibidos
    public const ESPECIAL_CHARS = [',', '.', ';', ':', '!', '?', '¡', '¿', '"', "'", '-', '_', '+', '#', '$', '%', '&', '/', '(', ')', '=', '*', '^', '~', '`', '{', '}', '[', ']', '|', '\\', '@','<','>'];

    /**
     * Verifica si un string contiene caracteres especiales prohibidos
     */
    private function contieneCaracteresEspeciales(string $texto): bool
    {
        foreach(self::ESPECIAL_CHARS as $caracter){
            if(str_contains($texto, $caracter)){
                return true;
            }
        }
        return false;
    }

    /**
     * Verifica si un string tiene espacios en blanco al inicio
     */
    private function tieneEspaciosAlInicio(string $texto): bool
    {
        return $texto !== ltrim($texto);
    }

    //Funcion que muestra el listado
    #[Route('/coaches', name: 'coach_list', methods: ['GET', 'OPTIONS'])]
    public function listCoaches(EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request): Response
    {
        // Manejar CORS preflight
        if ($request->getMethod() === 'OPTIONS') {
            return new Response('', 200, [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
            ]);
        }

        // Obtener todos los coaches o filtrar por nombre
        $queryBuilder = $entityManager->getRepository(Coach::class)->createQueryBuilder('c');
        
        $nombre = $request->query->get('nombre');
        
        if($nombre){
            $queryBuilder->where('c.nombre LIKE :nombre')
                        ->setParameter('nombre', '%' . $nombre . '%');
        }
        
        $query = $queryBuilder->getQuery();
        
        // Paginar los resultados
        $coaches = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), // página actual
            $request->query->getInt('pageSize', 5) // elementos por página
        );
        
        // Si no hay coaches, devolver array vacío con mensaje
        if (!$coaches || $coaches->getTotalItemCount() === 0) {
            $pageSize = $request->query->getInt('pageSize', 5);
            return $this->json([
                'coaches' => [],
                'message' => 'No hay coaches registrados',
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => $pageSize,
                    'total_items' => 0,
                    'total_pages' => 0,
                    'has_next_page' => false,
                    'has_prev_page' => false,
                    'next_page' => null,
                    'prev_page' => null
                ]
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
                'id_club' => $club?->getIdClub() ?? 'Sin club'
            ];
        }
        
        $response = $this->json([
            'coaches' => $coachesData,
            'pagination' => [
                'current_page' => $coaches->getCurrentPageNumber(),
                'per_page' => $coaches->getItemNumberPerPage(),
                'total_items' => $coaches->getTotalItemCount(),
                'total_pages' => $coaches->getPageCount(),
                'has_next_page' => $coaches->getCurrentPageNumber() < $coaches->getPageCount(),
                'has_prev_page' => $coaches->getCurrentPageNumber() > 1,
                'next_page' => $coaches->getCurrentPageNumber() < $coaches->getPageCount() ? $coaches->getCurrentPageNumber() + 1 : null,
                'prev_page' => $coaches->getCurrentPageNumber() > 1 ? $coaches->getCurrentPageNumber() - 1 : null
            ]
        ]);
        
        // Añadir headers CORS
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        
        return $response;
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
            'id' => $coach->getId(),
            'dni' => $coach->getDni(),
            'nombre' => $coach->getNombre(),
            'apellidos' => $coach->getApellidos(),
            'salario' => $coach->getSalario(),
            'id_club' => $club ? $club->getIdClub() : 'Sin club'
        ];

        return $this->json($data);
    }

    #[Route('/coaches', name: 'coach_insert', methods: ['POST'])]
    public function createCoach(EntityManagerInterface $entityManager, Request $request): Response
    {
        $errors = [];
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
            $errors['error'] = 'Todos los campos son requeridos';
        }

        // Validar espacios al inicio en campos de texto
        if ($this->tieneEspaciosAlInicio($nombre) || $this->tieneEspaciosAlInicio($apellidos)) {
            $errors['nombre'] = 'El nombre o apellidos no pueden empezar con espacios en blanco';
        }else if($this->contieneCaracteresEspeciales($nombre) || $this->contieneCaracteresEspeciales($apellidos)){
            $errors['nombre'] = 'El nombre o apellidos no pueden contener caracteres especiales';
        }else if(preg_match('/\d/', $nombre) || preg_match('/\d/', $apellidos)){
            $errors['nombre'] = 'El nombre o apellidos no pueden contener números';
        }else if(strlen($nombre) < 2 || strlen($nombre) > 50){
            $errors['nombre'] = 'El nombre debe tener entre 2 y 50 caracteres';
        }else if(strlen($apellidos) < 2 || strlen($apellidos) > 50){
            $errors['apellidos'] = 'Los apellidos deben tener entre 2 y 50 caracteres';
        }
        
        // Verificar si el DNI ya existe
        $existingCoach = $entityManager->getRepository(Coach::class)->findOneBy(['dni' => $dni]);
        if ($existingCoach) {
            return $this->json(['error' => 'El DNI ya existe'], 400);
        }else if(!preg_match('/^[0-9]{8}[A-Z]$/', $dni)){
            return $this->json(['error' => 'El DNI no es válido. Formato: 8 dígitos seguidos de una letra mayúscula'], 400);
        }

        // Verificar si el club existe (solo si se proporciona)
        $club = null;
        if (!empty($id_club)) {
            $club = $entityManager->getRepository(Club::class)->findOneBy(['id_club' => $id_club]);
            if (!$club) {
                $errors['id_club'] = 'El club no existe';
            } else {
                // Verificar si el club ya tiene un entrenador
                $existingCoachInClub = $entityManager->getRepository(Coach::class)->findOneBy(['club' => $club]);
                if ($existingCoachInClub) {
                    $errors['id_club'] = 'Este club ya tiene un entrenador asignado';
                }
            }
        }
        // Validar presupuesto del club
        if ($club) {
            $presupuestoRestante = $club->getPresupuestoRestante();
            if ($presupuestoRestante <= $salario) {
                $errors['salario'] = 'El Club no tiene presupuesto suficiente. Presupuesto restante: ' . $presupuestoRestante;
            }
        }

        if (!empty($errors)) {
            return $this->json(['error' => $errors], 400);
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


    //Funcion para actualizar un coach
    #[Route('/coaches/{id}', name: 'coach_update', methods: ['PUT'])]
    public function updateCoach(EntityManagerInterface $entityManager, $id, Request $request): JsonResponse
    {
        $errors = [];

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
            $dniEnviado = $jsonData['dni'] ?? $jsonData['Dni'] ?? $jsonData['DNI'];
            $dniActual = $coach->getDni();
            
            if($dniEnviado !== $dniActual){
                return $this->json(['error' => 'El DNI no puede ser modificado'], 400);
            }
        }

        // Actualizar campos si están presentes
        if(isset($jsonData['nombre']) || isset($jsonData['Nombre']) || isset($jsonData['NOMBRE'])){
            $nombre = $jsonData['nombre'] ?? $jsonData['Nombre'] ?? $jsonData['NOMBRE'];
            
            
            if ($this->tieneEspaciosAlInicio($nombre)) {
                $errors['nombre'] = 'El nombre no puede empezar con espacios en blanco';
            }else if ($this->contieneCaracteresEspeciales($nombre)) {
                $errors['nombre'] = 'El nombre no puede contener caracteres especiales';
            }else if(preg_match('/\d/', $nombre)){
                $errors['nombre'] = 'El nombre no puede contener números';
            }else if(strlen($nombre) < 2 || strlen($nombre) > 50){
                $errors['nombre'] = 'El nombre debe tener entre 2 y 50 caracteres';
            }else{
                $coach->setNombre($nombre);
            }
        }
        
        if(isset($jsonData['apellidos']) || isset($jsonData['Apellidos']) || isset($jsonData['APELLIDOS'])){
            $apellidos = $jsonData['apellidos'] ?? $jsonData['Apellidos'] ?? $jsonData['APELLIDOS'];
            
            
            if ($this->tieneEspaciosAlInicio($apellidos)) {
                $errors['apellidos'] = 'Los apellidos no pueden empezar con espacios en blanco';
            }else if ($this->contieneCaracteresEspeciales($apellidos)) {
                $errors['apellidos'] = 'Los apellidos no pueden contener caracteres especiales';
            }else if(preg_match('/\d/', $apellidos)){
                $errors['apellidos'] = 'Los apellidos no pueden contener números';
            }else if(strlen($apellidos) < 2 || strlen($apellidos) > 50){
                $errors['apellidos'] = 'Los apellidos deben tener entre 2 y 50 caracteres';
            }else{
                $coach->setApellidos($apellidos);
            }
        }
        
        if(isset($jsonData['salario']) || isset($jsonData['Salario']) || isset($jsonData['SALARIO'])){
            $salario = $jsonData['salario'] ?? $jsonData['Salario'] ?? $jsonData['SALARIO'];
            if($salario <= 0){
                $errors['salario'] = 'El salario no puede ser 0 o negativo';
            }else if(!is_numeric($salario)){
                $errors['salario'] = 'El salario debe ser un número';
            }else{
                // Validar presupuesto del club si el entrenador tiene club
                if ($coach->getClub()) {
                    $presupuestoRestante = $coach->getClub()->getPresupuestoRestante();
                    $salarioActual = (float)$coach->getSalario();
                    $presupuestoDisponible = $presupuestoRestante + $salarioActual;
                    
                    if ($presupuestoDisponible < $salario) {
                        $errors['salario'] = 'El Club no tiene presupuesto suficiente. Presupuesto disponible: ' . $presupuestoDisponible;
                    }else{
                        $coach->setSalario($salario);
                    }
                }else{
                    $coach->setSalario($salario);
                }
            }
        }
        
        if(isset($jsonData['id_club']) || isset($jsonData['Id_club']) || isset($jsonData['ID_CLUB'])){
            $id_club = $jsonData['id_club'] ?? $jsonData['Id_club'] ?? $jsonData['ID_CLUB'];
            
            if(empty($id_club) || $id_club === null || $id_club === '' || $id_club === 'null') {
                // Si id_club está vacío, quitar el club del entrenador
                $coach->setClub(null);
            } else {
                // Si id_club tiene valor, buscar y asignar el club
                $club = $entityManager->getRepository(Club::class)->findOneBy(['id_club' => $id_club]);
                if(!$club){
                    $errors['id_club'] = 'El club no existe';
                }
                
                // Verificar si el club ya tiene otro entrenador (excluyendo el actual)
                $existingCoachInClub = $entityManager->getRepository(Coach::class)->findOneBy(['club' => $club]);
                if($existingCoachInClub && $existingCoachInClub->getId() != (int)$id){
                    $errors['id_club'] = 'Este club ya tiene un entrenador asignado';
                }

                // Validar presupuesto del club
                $salarioEntrenador = (float)$coach->getSalario();
                $presupuestoRestante = $club->getPresupuestoRestante();
                
                // Si el entrenador ya tiene un club, sumar su salario actual al presupuesto disponible
                if ($coach->getClub() && $coach->getClub()->getId() !== $club->getId()) {
                    // El entrenador viene de otro club, usar presupuesto restante del nuevo club
                    if ($presupuestoRestante <= $salarioEntrenador) {
                        $errors['salario'] = 'El Club no tiene presupuesto suficiente. Presupuesto restante: ' . $presupuestoRestante . ', Salario del entrenador: ' . $salarioEntrenador;
                    }
                } elseif ($coach->getClub() && $coach->getClub()->getId() === $club->getId()) {
                    // El entrenador ya está en este club, no hay problema de presupuesto
                } else {
                    // El entrenador no tiene club, usar presupuesto restante
                    if ($presupuestoRestante < $salarioEntrenador) {
                        $errors['salario'] = 'El Club no tiene presupuesto suficiente. Presupuesto restante: ' . $presupuestoRestante . ', Salario del entrenador: ' . $salarioEntrenador;
                    }
                }
                
                $coach->setClub($club);
            }
        }

        if (!empty($errors)) {
            return $this->json(['error' => $errors], 400);
        }

        $entityManager->flush();
        
        return $this->json([
            'message' => 'Coach updated successfully'
        ]);
    }
}