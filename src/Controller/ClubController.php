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
use Knp\Component\Pager\PaginatorInterface;

class ClubController extends AbstractController
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

    #[Route('/clubs', name: 'club_list', methods: ['GET', 'OPTIONS'])]
    public function listClubs(EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request): Response
    {
        // Manejar CORS preflight
        if ($request->getMethod() === 'OPTIONS') {
            return new Response('', 200, [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
            ]);
        }

        // Obtener todos los clubs o filtrar por nombre
        $queryBuilder = $entityManager->getRepository(Club::class)->createQueryBuilder('c');
        
        $nombre = $request->query->get('nombre');
        
        if($nombre){
            $queryBuilder->where('c.nombre LIKE :nombre')
                        ->setParameter('nombre', '%' . $nombre . '%');
        }
        
        $query = $queryBuilder->getQuery();
        
        // Paginar los resultados
        $clubs = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), // página actual
            $request->query->getInt('pageSize', 5) // elementos por página
        );

        if(!$clubs){
            return $this->json(['message' => 'No hay clubs registrados'], 400);
        }

        $data = [];
        foreach($clubs as $club){
            // Obtener entrenadores del club
            $entrenador = '';
            if($club->getCoaches()->count() > 0){
                $coach = $club->getCoaches()->first();
                if($coach) {
                    $entrenador = $coach->getNombre() . ' ' . $coach->getApellidos();
                }
            }
            
            // Obtener jugadores del club
            $jugadores = [];
            foreach($club->getPlayers() as $player){
                $jugadores[] = $player->getNombre() . ' ' . $player->getApellidos();
            }

            $data[] = [
                'id' => $club->getId(),
                'id_club' => $club->getIdClub(),
                'nombre' => $club->getNombre(),
                'fundacion' => $club->getFundacion(),
                'ciudad' => $club->getCiudad(),
                'estadio' => $club->getEstadio(),
                'presupuesto' => $club->getPresupuesto(),
                'presupuesto_restante' => $club->getPresupuestoRestante(),
                'entrenador' => $entrenador,
                'jugadores' => !empty($jugadores) ? $jugadores : 'Sin jugadores'
            ];
        }

        $response = $this->json([
            'clubs' => $data,
            'pagination' => [
                'current_page' => $clubs->getCurrentPageNumber(),
                'per_page' => $clubs->getItemNumberPerPage(),
                'total_items' => $clubs->getTotalItemCount(),
                'total_pages' => $clubs->getPageCount(),
                'has_next_page' => $clubs->getCurrentPageNumber() < $clubs->getPageCount(),
                'has_prev_page' => $clubs->getCurrentPageNumber() > 1,
                'next_page' => $clubs->getCurrentPageNumber() < $clubs->getPageCount() ? $clubs->getCurrentPageNumber() + 1 : null,
                'prev_page' => $clubs->getCurrentPageNumber() > 1 ? $clubs->getCurrentPageNumber() - 1 : null
            ]
        ]);
        
        // Añadir headers CORS
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        
        return $response;
    }

    #[Route('/clubs/{id}', name: 'club_get', methods: ['GET'])]
    public function getClub(EntityManagerInterface $entityManager, $id): Response
    {
        $club = $entityManager->getRepository(Club::class)->findOneBy(['id' => $id]);

        if(!$club){
            return $this->json(['error' => 'Club not found'], 404);
        }

        // Obtener entrenadores del club
        $entrenadores = [];
        foreach($club->getCoaches() as $coach){
            if($coach) {
                $entrenadores[] = $coach->getNombre() . ' ' . $coach->getApellidos();
            }
        }

        // Obtener jugadores del club
        $jugadores = [];
        foreach($club->getPlayers() as $player){
            $jugadores[] = $player->getNombre() . ' ' . $player->getApellidos();
        }

        $data = [
            'id' => $club->getId(),
            'id_club' => $club->getIdClub(),
            'nombre' => $club->getNombre(),
            'fundacion' => $club->getFundacion(),
            'ciudad' => $club->getCiudad(),
            'estadio' => $club->getEstadio(),
            'presupuesto' => $club->getPresupuesto(),
            'presupuesto_restante' => $club->getPresupuestoRestante(),
            'entrenador' => !empty($entrenadores) ? $entrenadores : 'Sin entrenadores',
            'jugadores' => !empty($jugadores) ? $jugadores : 'Sin jugadores'
        ];

        return $this->json($data);
    }

    #[Route('/clubs/{id}', name: 'club_delete', methods: ['DELETE'])]
    public function deleteClub(EntityManagerInterface $entityManager, $id): Response
    {
        $club = $entityManager->getRepository(Club::class)->findOneBy(['id' => $id]);
        
        if(!$club){
            return $this->json(['error' => 'Club not found'], 404);
        }

        // Primero desasociar todos los entrenadores del club
        $coaches = $entityManager->getRepository(Coach::class)->findBy(['club' => $club]);
        foreach($coaches as $coach) {
            $coach->setClub(null);
        }

        // Desasociar todos los jugadores del club
        $players = $entityManager->getRepository(Player::class)->findBy(['club' => $club]);
        foreach($players as $player) {
            $player->setClub(null);
        }

        // Ahora eliminar el club
        $entityManager->remove($club);
        $entityManager->flush();

        return $this->json(['message' => 'Club deleted successfully']);
    }

    #[Route('/clubs', name: 'club_create', methods: ['POST'])]
    public function createClub(EntityManagerInterface $entityManager, Request $request): Response
    {

        $errors = [];

        $body = $request->getContent();
        $jsonData = json_decode($body, true);
        
        if(!$jsonData){
            $errors['json'] = 'Invalid JSON';
        }

        $id_club = $jsonData['id_club'] ?? null;
        $nombre = $jsonData['nombre'] ?? null;
        $fundacion = $jsonData['fundacion'] ?? null;
        $ciudad = $jsonData['ciudad'] ?? null;
        $estadio = $jsonData['estadio'] ?? null;
        $presupuesto = $jsonData['presupuesto'] ?? null;

        if(empty($id_club) || empty($nombre) || empty($fundacion) || empty($ciudad) || empty($estadio) || empty($presupuesto)){
            $errors['error'] = 'Todos los campos son requeridos';
        }

        if(empty($id_club)){
            $errors['id_club'] = 'El id_club es requerido';
        }else if(strlen($id_club) < 3 || strlen($id_club) > 5){
            $errors['id_club'] = 'El id_club debe tener entre 3 y 5 caracteres';
        }else if($entityManager->getRepository(Club::class)->findOneBy(['id_club' => $id_club])){
            $errors['id_club'] = 'El id_club ya existe';
        }

        $nombresClubs = [];

        $clubs = $entityManager->getRepository(Club::class)->findAll();
        foreach($clubs as $club){
            $nombresClubs[] = $club->getNombre();
        }

        if(empty($nombre)){
            $errors['nombre'] = 'El nombre es requerido';
        }else if(strlen($nombre) < 2 || strlen($nombre) > 50){
            $errors['nombre'] = 'El nombre debe tener entre 2 y 50 caracteres';
        }else if($this->tieneEspaciosAlInicio($nombre)){
            $errors['nombre'] = 'El nombre no puede empezar con espacios en blanco';
        }else if($this->contieneCaracteresEspeciales($nombre)){
            $errors['nombre'] = 'El nombre no puede contener caracteres especiales';
        }else if(preg_match('/\d/', $nombre)){
            $errors['nombre'] = 'El nombre no puede contener números';
        }else if(in_array(strtolower($nombre), array_map('strtolower', $nombresClubs))){
            $errors['nombre'] = 'El nombre del club ya existe';
        }

        if(empty($fundacion)){
            $errors['fundacion'] = 'La fundacion es requerida';
        }else if($fundacion < 1857 || $fundacion > date('Y')){
            $errors['fundacion'] = 'La fundacion debe ser entre 1800 y 2025';
        }

        if(empty($ciudad)){
            $errors['ciudad'] = 'La ciudad es requerida';
        }else if(strlen($ciudad) < 3 || strlen($ciudad) > 50){
            $errors['ciudad'] = 'La ciudad debe tener entre 3 y 50 caracteres';
        }else if($this->tieneEspaciosAlInicio($ciudad)){
            $errors['ciudad'] = 'La ciudad no puede empezar con espacios en blanco';
        }else if($this->contieneCaracteresEspeciales($ciudad)){
            $errors['ciudad'] = 'La ciudad no puede contener caracteres especiales';
        }

        if(empty($estadio)){
            $errors['estadio'] = 'El estadio es requerido';
        }else if(strlen($estadio) < 2 || strlen($estadio) > 50){
            $errors['estadio'] = 'El estadio debe tener entre 2 y 50 caracteres';
        }else if($this->tieneEspaciosAlInicio($estadio)){
            $errors['estadio'] = 'El estadio no puede empezar con espacios en blanco';
        }else if($this->contieneCaracteresEspeciales($estadio)){
            $errors['estadio'] = 'El estadio no puede contener caracteres especiales';
        }else if(preg_match('/\d/', $estadio)){
            $errors['estadio'] = 'El estadio no puede contener números';
        }

        if(empty($presupuesto)){
            $errors['presupuesto'] = 'El presupuesto es requerido';
        }else if(!is_numeric($presupuesto)){
            $errors['presupuesto'] = 'El presupuesto debe ser un número';
        }else if($presupuesto <= 0){
            $errors['presupuesto'] = 'El presupuesto no puede ser 0 o negativo';
        }

        if(!empty($errors)){
            return $this->json(['error' => $errors], 400);
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

        $errors = [];

        $club = $entityManager->getRepository(Club::class)->findOneBy(['id' => $id]);
        
        if(!$club){
            $errors['club'] = 'Club not found';
        }

        $body = $request->getContent();
        $jsonData = json_decode($body, true);

        if(!$jsonData){
            $errors['json'] = 'No hay datos para actualizar';
        }

        if(isset($jsonData['id_club'])){
            $id_club_enviado = $jsonData['id_club'];
            $id_club_actual = $club->getIdClub();
            if($id_club_enviado !== $id_club_actual){
                $errors['id_club'] = 'El id_club no puede ser modificado';
            }
        }

        $nombresClubs = [];

        $clubs = $entityManager->getRepository(Club::class)->findAll();
        foreach($clubs as $club){
            $nombresClubs[] = $club->getNombre();
        }

        if(isset($jsonData['nombre'])){
            if(strlen($jsonData['nombre']) < 2 || strlen($jsonData['nombre']) > 50){
                $errors['nombre'] = 'El nombre debe tener entre 2 y 50 caracteres';
            }else if($this->tieneEspaciosAlInicio($jsonData['nombre'])){
                $errors['nombre'] = 'El nombre no puede empezar con espacios en blanco';
            }else if($this->contieneCaracteresEspeciales($jsonData['nombre'])){
                $errors['nombre'] = 'El nombre no puede contener caracteres especiales';
            }else if(preg_match('/\d/', $jsonData['nombre'])){
                $errors['nombre'] = 'El nombre no puede contener números';
            }else if(in_array(strtolower($jsonData['nombre']), array_map('strtolower', $nombresClubs))){
                $errors['nombre'] = 'El nombre del club ya existe';
            }else if(trim($jsonData['nombre']) === ''){
                $errors['nombre'] = 'El nombre no puede estar vacío';
            }else{
                $club->setNombre($jsonData['nombre']);
            }
            
        }
        if(isset($jsonData['fundacion'])){
            if($jsonData['fundacion'] < 1857 || $jsonData['fundacion'] > date('Y')){
                $errors['fundacion'] = 'La fundacion debe ser entre 1800 y 2025';
            }else{
                $club->setFundacion($jsonData['fundacion']);
            }
        }
        if(isset($jsonData['ciudad'])){
            if(strlen($jsonData['ciudad']) < 3 || strlen($jsonData['ciudad']) > 50){
                $errors['ciudad'] = 'La ciudad debe tener entre 3 y 50 caracteres';
            }else if($this->tieneEspaciosAlInicio($jsonData['ciudad'])){
                $errors['ciudad'] = 'La ciudad no puede empezar con espacios en blanco';
            }else if($this->contieneCaracteresEspeciales($jsonData['ciudad'])){
                $errors['ciudad'] = 'La ciudad no puede contener caracteres especiales';
            }else{
                $club->setCiudad($jsonData['ciudad']);
            }
        }
        if(isset($jsonData['estadio'])){
            if(strlen($jsonData['estadio']) < 2 || strlen($jsonData['estadio']) > 50){
                $errors['estadio'] = 'El estadio debe tener entre 2 y 50 caracteres';
            }else if($this->tieneEspaciosAlInicio($jsonData['estadio'])){
                $errors['estadio'] = 'El estadio no puede empezar con espacios en blanco';
            }else if($this->contieneCaracteresEspeciales($jsonData['estadio'])){
                $errors['estadio'] = 'El estadio no puede contener caracteres especiales';
            }else if(preg_match('/\d/', $jsonData['estadio'])){
                $errors['estadio'] = 'El estadio no puede contener números';
            }else{
                $club->setEstadio($jsonData['estadio']);
            }
        }
        if(isset($jsonData['presupuesto'])){
            if(!is_numeric($jsonData['presupuesto'])){
                $errors['presupuesto'] = 'El presupuesto debe ser un número';
            }else if($jsonData['presupuesto'] <= 0){
                $errors['presupuesto'] = 'El presupuesto no puede ser 0 o negativo';
            }else{
                $club->setPresupuesto($jsonData['presupuesto']);
            }
        }

        // Manejar el campo entrenador
        if(isset($jsonData['entrenador']) || isset($jsonData['Entrenador']) || isset($jsonData['ENTRENADOR'])){
            $entrenador = $jsonData['entrenador'] ?? $jsonData['Entrenador'] ?? $jsonData['ENTRENADOR'];
            
            if(empty($entrenador) || $entrenador === "null" || $entrenador === null) {
                // Si entrenador está vacío o es "null", quitar el entrenador del club
                $coachActual = $entityManager->getRepository(Coach::class)->findOneBy(['club' => $club]);
                if($coachActual) {
                    $coachActual->setClub(null);
                }
            } else {
                // Si entrenador tiene valor, buscar y asignar el entrenador
                $coach = $entityManager->getRepository(Coach::class)->createQueryBuilder('c')
                    ->where('c.nombre LIKE :name OR c.apellidos LIKE :name OR CONCAT(c.nombre, \' \', c.apellidos) LIKE :name')
                    ->setParameter('name', '%' . $entrenador . '%')
                    ->getQuery()
                    ->getResult();

                if(empty($coach)){
                    $errors['entrenador'] = 'No se encontró ningún entrenador con ese nombre';
                } else if(count($coach) > 1){
                    $errors['entrenador'] = 'Se encontraron múltiples entrenadores con ese nombre. Especifica más detalles.';
                } else {
                    $coachEncontrado = $coach[0];
                    
                    // Verificar si el club ya tiene un entrenador
                    $existingCoach = $entityManager->getRepository(Coach::class)->findOneBy(['club' => $club]);
                    if($existingCoach && $existingCoach->getId() !== $coachEncontrado->getId()){
                        $errors['entrenador'] = 'Este club ya tiene un entrenador asignado';
                    }
                    
                    // Verificar si el entrenador ya está en otro club
                    if($coachEncontrado->getClub() && $coachEncontrado->getClub()->getIdClub() !== $club->getIdClub()){
                        $errors['entrenador'] = 'Este entrenador ya está asignado a otro club';
                    }
                    
                    if(empty($errores['entrenador'])) {
                        // Quitar el entrenador actual del club si existe
                        if($existingCoach) {
                            $existingCoach->setClub(null);
                        }
                        // Asignar el nuevo entrenador
                        $coachEncontrado->setClub($club);
                    }
                }
            }
        }

        if(!empty($errors)){
            return $this->json(['error' => $errors], 400);
        }
         
        $entityManager->flush();
        
        return $this->json(['message' => 'Club updated successfully']);
    }
}
