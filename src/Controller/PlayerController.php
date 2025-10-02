<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Player;
use App\Entity\Club;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Knp\Component\Pager\PaginatorInterface;

class PlayerController extends AbstractController
{

    public const ESPECIAL_CHARS = [
        ',', '.', ';', ':', '!', '?', '¡', '¿', '"', "'", '-', '_', '+', '#', '$', '%', '&', '/', '(', ')', '=', '*', '^', '~', '`', '{', '}', '[', ']', '|', '\\', '@','<','>'
    ];

    private function contieneCaracteresEspeciales(string $texto): bool
    {
        foreach (self::ESPECIAL_CHARS as $caracter) {
            if (str_contains($texto, $caracter)) {
                return true;
            }
        }
        return false;
    }

    #[Route('/players', name: 'player_list', methods: ['GET', 'OPTIONS'])]
    public function listPlayers(EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request): Response
    {
        // Manejar CORS preflight
        if ($request->getMethod() === 'OPTIONS') {
            return new Response('', 200, [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
            ]);
        }

        // Obtener todos los jugadores o filtrar por nombre
        $queryBuilder = $entityManager->getRepository(Player::class)->createQueryBuilder('p');
        
        $nombre = $request->query->get('nombre');
        $apellidos = $request->query->get('apellidos');
        if($nombre){
            $queryBuilder->where('p.nombre = :nombre')
                        ->setParameter('nombre', $nombre);
        }
        
        $query = $queryBuilder->getQuery();
        
        // Paginar los resultados
        $players = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), // página actual
            $request->query->getInt('pageSize', 10) // elementos por página
        );

        if(!$players)
        {
            return $this->json(['error' => 'No hay players registrados'],400);
        }

        $data = [];
        foreach($players as $player)
        {
            $data[] = [
                'id' => $player->getId(),
                'nombre' => $player->getNombre(),
                'apellidos' => $player->getApellidos(),
                'dorsal' => $player->getDorsal(),
                'salario' => $player->getSalario(),
                'id_club' => $player->getClub() ? $player->getClub()->getIdClub() : 'Sin club',
                'club' => $player->getClub() ? $player->getClub()->getNombre() : 'Sin club',
                'entrenador' => $player->getClub() && $player->getClub()->getCoaches()->count() > 0 
                    ? $player->getClub()->getCoaches()->first()->getNombre() . ' ' . $player->getClub()->getCoaches()->first()->getApellidos()
                    : 'Sin entrenador'
            ];
        }

        
        $response = $this->json([
            'players' => $data,
            'pagination' => [
                'current_page' => $players->getCurrentPageNumber(),
                'per_page' => $players->getItemNumberPerPage(),
                'total_items' => $players->getTotalItemCount(),
                'total_pages' => $players->getPageCount(),
                'has_next_page' => $players->getCurrentPageNumber() < $players->getPageCount(),
                'has_prev_page' => $players->getCurrentPageNumber() > 1,
                'next_page' => $players->getCurrentPageNumber() < $players->getPageCount() ? $players->getCurrentPageNumber() + 1 : null,
                'prev_page' => $players->getCurrentPageNumber() > 1 ? $players->getCurrentPageNumber() - 1 : null
            ]
        ]);
        
        // Añadir headers CORS
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        
        return $response;
    }

    #[Route('/players/{id}', name: 'player_get', methods: ['GET'])]
    public function getPlayer(EntityManagerInterface $entityManager, $id): Response
    {
        $player = $entityManager->getRepository(Player::class)->find($id);

        if(!$player)
        {
            return $this->json(['error' => 'Player not found'], 404);
        }

        $club = $player->getClub();
        $coach = $club && $club->getCoaches()->count() > 0 ? $club->getCoaches()->first() : null;
        
        $data = [
            'id' => $player->getId(),
            'nombre' => $player->getNombre(),
            'apellidos' => $player->getApellidos(),
            'dorsal' => $player->getDorsal(),
            'salario' => $player->getSalario(),
            'id_club' => $club ? $club->getIdClub() : 'Sin club',
            'club' => $club ? $club->getNombre() : 'Sin club',
            'entrenador' => $coach ? $coach->getNombre() . ' ' . $coach->getApellidos() : 'Sin entrenador'
        ];

        return $this->json($data);
    }

    #[Route('/players/{id}', name: 'player_delete', methods: ['DELETE'])]
    public function deletePlayer(EntityManagerInterface $entityManager, MailerInterface $mailer, $id): Response
    {
        $player = $entityManager->getRepository(Player::class)->find($id);

        if(!$player){
            return $this->json(['error' => 'Player not found'], 404);
        }

        // Guardar datos del jugador y club antes de eliminar
        $club = $player->getClub();

        $entityManager->remove($player);
        $entityManager->flush();

        //Enviamos el email solo si hay club
        if($club) {
            $this->sendEmailRemoved($player, $club);
        }
        
        return $this->json(['message' => 'Player deleted successfully']);
    }

    #[Route('/players', name: 'player_create', methods: ['POST'])]
    public function createPlayer(EntityManagerInterface $entityManager, MailerInterface $mailer, Request $request): Response
    {

        $errors = [];

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
        if (empty($nombre) || empty($apellidos) || empty($dorsal) || empty($salario)) {
            $errors['error'] = 'Todos los campos son requeridos';
        }

        // Validar caracteres especiales en nombre y apellidos (SIEMPRE)
        if($this->contieneCaracteresEspeciales($nombre) || $this->contieneCaracteresEspeciales($apellidos)){
            $errors['nombre/apellidos'] = 'El nombre o los apellidos no pueden contener caracteres especiales';
        }else if(preg_match('/\d/', $nombre)){
            $errors['nombre'] = 'El nombre no puede contener números';
        }else if(preg_match('/\d/', $apellidos)){
            $errors['apellidos'] = 'Los apellidos no pueden contener números';
        }else if(strlen($nombre) < 2 || strlen($nombre) > 50){
            $errors['nombre'] = 'El nombre debe tener entre 2 y 50 caracteres';
        }else if(strlen($apellidos) < 2 || strlen($apellidos) > 50){
            $errors['apellidos'] = 'Los apellidos deben tener entre 2 y 50 caracteres';
        }else if($nombre === " " || $apellidos === " "){
            $errors['nombre/apellidos'] = 'El nombre y los apellidos no pueden estar vacíos';
        }

        if(!is_numeric($salario)){
            $errors['salario'] = 'El salario debe ser un número';
        }else if($salario <= 0){
            $errors['salario'] = 'El salario no puede ser 0 o negativo';
        }

        // Validar que el club existe (si se proporciona)
        $club = null;
        if (!empty($id_club)) {
            $club = $entityManager->getRepository(Club::class)->findOneBy(['id_club' => $id_club]);
            if (!$club) {
                $errors['club'] = 'Club not found';
            }
        }
        
        $dorsales = [];
        
        // Solo validar dorsales y presupuesto si hay club
        if ($club) {
            //Guardamos los dorsales del club del jugador
            $playersDelClub = $entityManager->getRepository(Player::class)->findBy(['club' => $club]);
            foreach($playersDelClub as $p){
                $dorsales[] = $p->getDorsal();
            }

            $existeJugador = $entityManager->getRepository(Player::class)->findOneBy(['nombre' => $nombre, 'apellidos' => $apellidos, 'club' => $club]);
            if($existeJugador){
                $errors['jugador'] = 'El jugador ya existe en el club';
            }

            
            // Validar presupuesto del club
            $presupuestoRestante = $club->getPresupuestoRestante();
            if ($presupuestoRestante <= $salario) {
                $errors['presupuesto'] = 'El Club no tiene presupuesto suficiente. Presupuesto restante: ' . $presupuestoRestante;
            }
        }



        //Creamos al jugador y le asignamos los datos
        $player = new Player();
        $player->setNombre($nombre);
        $player->setApellidos($apellidos);
        if (!is_numeric($dorsal)) {
            $errors['dorsal'] = 'El dorsal debe ser un número';
        }
        
        if ($dorsal <= 0 || $dorsal > 99) {
            $errors['dorsal'] = 'El dorsal debe ser mayor que 0 y menor que 100';
        }
        
        if ($club && in_array($dorsal, $dorsales)) {
            $errors['dorsal'] = 'El dorsal ya existe en el club';
        }

        if(!empty($errors)){
            return $this->json(['error' => $errors], 400);
        }
        
        $player->setDorsal($dorsal);
        $player->setSalario($salario);
        $player->setClub($club);

        //Guardamos el jugador en la base de datos
        $entityManager->persist($player);
        $entityManager->flush();
        $this->sendEmailRegistered($player, $club);

        //Devolvemos el mensaje de éxito
        return $this->json(['message' => 'Player created successfully']);
        
    }

    #[Route('/players/{id}', name: 'player_update', methods: ['PUT'])]
    public function updatePlayer(EntityManagerInterface $entityManager, $id, Request $request): Response
    {

        $errors = [];

        $player = $entityManager->getRepository(Player::class)->find($id);
        
        if(!$player){
            $errors['player'] = 'Player not found';
        }

        //Obtenemos los datos del JSON
        $body = $request->getContent();
        $jsonData = json_decode($body, true);

        if(empty($jsonData)){
            $errors['json'] = 'No hay datos para actualizar';
        }

        $club = $player->getClub();
        $presupuesto_club = $club ? $club->getPresupuesto() : null;
        //Hacemos validaciones básicas
        if(isset($jsonData['dni'])){
            $errors['dni'] = 'El DNI no puede ser modificado';
        }
        
        // Validar caracteres especiales en nombre y apellidos (SIEMPRE)
        if (isset($jsonData['nombre'])) {
            if ($this->contieneCaracteresEspeciales($jsonData['nombre'])) {
                $errors['nombre'] = 'El nombre no puede contener caracteres especiales';
            }
            
            if (preg_match('/\d/', $jsonData['nombre'])) {
                $errors['nombre'] = 'El nombre no puede contener números';
            }

            if(strlen($jsonData['nombre']) < 2 || strlen($jsonData['nombre']) > 50){
                $errors['nombre'] = 'El nombre debe tener entre 2 y 50 caracteres';
            }

            if(nombre === " "){
                $errors['nombre'] = 'El nombre no puede estar vacío';
            }
            
            $player->setNombre($jsonData['nombre']);
        }
        
        if (isset($jsonData['apellidos'])) {
            if ($this->contieneCaracteresEspeciales($jsonData['apellidos'])) {
                $errors['apellidos'] = 'Los apellidos no pueden contener caracteres especiales';
            }
            
            if (preg_match('/\d/', $jsonData['apellidos'])) {
                $errors['apellidos'] = 'Los apellidos no pueden contener números';
            }

            if(strlen($jsonData['apellidos']) < 2 || strlen($jsonData['apellidos']) > 50){
                $errors['apellidos'] = 'Los apellidos deben tener entre 2 y 50 caracteres';
            }

            if(apellidos === " "){
                $errors['apellidos'] = 'Los apellidos no pueden estar vacíos';
            }
            
            $player->setApellidos($jsonData['apellidos']);
        }
        if (isset($jsonData['salario'])) {
            if ($jsonData['salario'] <= 0) {
                $errors['salario'] = 'El salario no puede ser 0 o negativo';
            }
            
            if (!is_numeric($jsonData['salario'])) {
                $errors['salario'] = 'El salario debe ser un número';
            }
            
            // Validar presupuesto del club (solo si el jugador tiene club)
            if ($player->getClub()) {
                $presupuestoRestante = $player->getClub()->getPresupuestoRestante();
                if ($presupuestoRestante <= $jsonData['salario']) {
                    $errors['presupuesto'] = 'El Club no tiene presupuesto suficiente. Presupuesto restante: ' . $presupuestoRestante;
                }
            }
            
            $player->setSalario($jsonData['salario']);
        }
        
        // Actualizar el club PRIMERO
        if(isset($jsonData['id_club'])){
            if(empty($jsonData['id_club'])) {
                // Si id_club está vacío, quitar el club del jugador
                $player->setClub(null);
            } else {
                // Si id_club tiene valor, buscar y asignar el club
                $club = $entityManager->getRepository(Club::class)->findOneBy(['id_club' => $jsonData['id_club']]);
                if(!$club){
                    $errors['club'] = 'Club not found';
                }
                
                // VALIDAR DORSAL cuando se cambia el club
                $dorsalActual = $player->getDorsal();
                if($dorsalActual) {
                    // Verificar si el dorsal actual ya existe en el nuevo club
                    $playersDelNuevoClub = $entityManager->getRepository(Player::class)->findBy(['club' => $club]);
                    $dorsalExiste = false;
                    
                    foreach($playersDelNuevoClub as $p){
                        if($p->getId() !== $player->getId() && $p->getDorsal() == $dorsalActual){
                            $dorsalExiste = true;
                            break;
                        }
                    }
                    
                    if($dorsalExiste){
                        $errors['dorsal'] = 'El dorsal ' . $dorsalActual . ' ya existe en el club ' . $club->getNombre().'por el jugador ' . $player->getNombre() . ' ' . $player->getApellidos();
                    }
                }
                
                // VALIDAR PRESUPUESTO antes de cambiar el club
                $salarioJugador = (float)$player->getSalario();
                $presupuestoRestante = $club->getPresupuestoRestante();
                
                if ($presupuestoRestante <= $salarioJugador) {
                    $errors['presupuesto'] = 'El Club no tiene presupuesto suficiente. Presupuesto restante: ' . $presupuestoRestante . ', Salario del jugador: ' . $salarioJugador;
                }
                
                $player->setClub($club);
            }
        }
        
        // Ahora validar el dorsal con el club que se va a asignar
        if(isset($jsonData['dorsal'])){
            if($jsonData['dorsal'] <= 0 || $jsonData['dorsal'] > 99) {
                $errors['dorsal'] = 'El dorsal debe ser mayor que 0 y menor que 100';
            }
            
            // Determinar qué club usar para la validación
            $clubParaValidar = null;
            
            // Si se está actualizando el club, usar el nuevo club
            if(isset($jsonData['id_club']) && !empty($jsonData['id_club'])) {
                $clubParaValidar = $entityManager->getRepository(Club::class)->findOneBy(['id_club' => $jsonData['id_club']]);
            } else {
                // Si no se está actualizando el club, usar el club actual
                $clubParaValidar = $player->getClub();
            }
            
            if($clubParaValidar) {
                // Verificar si el dorsal ya existe en otros jugadores del club
                $playersDelClub = $entityManager->getRepository(Player::class)->findBy(['club' => $clubParaValidar]);
                $dorsalExiste = false;
                
                foreach($playersDelClub as $p){
                    if($p->getId() !== $player->getId() && $p->getDorsal() == $jsonData['dorsal']){
                        $dorsalExiste = true;
                        break;
                    }
                }
                
                if($dorsalExiste){
                    $errors['dorsal'] = 'El dorsal ya existe en el club';
                }
            }
            
            $player->setDorsal($jsonData['dorsal']);
        }
        
        // Validar presupuesto final antes de guardar
        if ($player->getClub()) {
            $presupuestoRestante = $player->getClub()->getPresupuestoRestante();
            if ($presupuestoRestante < 0) {
                $errors['presupuesto'] = 'El Club no tiene presupuesto suficiente. Presupuesto restante: ' . $presupuestoRestante;
            }
        }
        
        // Guardar los cambios en la base de datos
        $entityManager->flush();

        //Devolvemos el mensaje de éxito
        return $this->json(['message' => 'Player updated successfully']);

    }    



    public function sendEmailRemoved(Object $player, ?Object $club):Response
    {
        $mail = new PHPMailer(true);

        try {
            //Configuración del servidor
            $mail->isSMTP();
            $mail->Host = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth = true;
            $mail->Username = '773668dbb758a8';
            $mail->Password = 'd5eff39568411d';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;//TLS
            $mail->Port = 587; //Puerto TLS
            
            //Remitente y destinatario
            $mail->setFrom('no-reply@gmail.com', 'No-reply');
            $mail->addAddress('LaLiga@gmail.com', 'LaLiga'); 

            //Contenido
            $mail->isHTML(true);
            $mail->Subject = 'Jugador eliminado';
            $clubNombre = $club ? $club->getNombre() : 'Sin club';
            $mail->Body = 'El jugador ' . $player->getNombre() . ' ' . $player->getApellidos() . ' ha sido dado de baja en el club ' . $clubNombre.' con el dorsal ' . '<br>'.$player->getDorsal().'<br>y con el salario ' . '<br>'.$player->getSalario();
            $mail->send();
            return $this->json(['message' => 'Email enviado correctamente']);
        }catch(Exception $e){
            return $this->json(['error' => 'Error al enviar el email'], 500);
        }
    }
    
    public function sendEmailRegistered(Object $player, ?Object $club):Response
    {
        $mail = new PHPMailer(true);

        try {
            //Configuración del servidor
            $mail->isSMTP();
            $mail->Host = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth = true;
            $mail->Username = '773668dbb758a8';
            $mail->Password = 'd5eff39568411d';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;//TLS
            $mail->Port = 587; //Puerto TLS
            
            //Remitente y destinatario
            $mail->setFrom('no-reply@gmail.com', 'No-reply');
            $mail->addAddress('LaLiga@gmail.com', 'LaLiga'); 

            //Contenido
            $mail->isHTML(true);
            $clubNombre = $club ? $club->getNombre() : 'Sin club';
            $mail->Subject = 'Jugador registrado - ' . $clubNombre;
            $mail->Body = 'El jugador ' . $player->getNombre() . ' ' . $player->getApellidos() . ' ha sido dado de alta en el club ' . $clubNombre.' con el dorsal ' . '<br>'.$player->getDorsal().'<br>y con el salario ' . '<br>'.$player->getSalario();
            $mail->send();
            return $this->json(['message' => 'Email enviado correctamente']);
        } catch(Exception $e) {
            return $this->json(['error' => 'Error al enviar el email: ' . $e->getMessage()], 500);
        }
    }
        
}