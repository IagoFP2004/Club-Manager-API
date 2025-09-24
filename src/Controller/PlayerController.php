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
    #[Route('/players', name: 'player_list', methods: ['GET'])]
    public function listPlayers(EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request): Response
    {
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
            10 // elementos por página
        );

        if(!$players)
        {
            return $this->json(['message' => 'No hay players registrados'],400);
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
                'club' => $player->getClub() ? $player->getClub()->getNombre() : null,
                'entrenador' => $player->getClub() && $player->getClub()->getCoaches()->count() > 0 
                    ? $player->getClub()->getCoaches()->first()->getNombre() . ' ' . $player->getClub()->getCoaches()->first()->getApellidos()
                    : 'Sin entrenador'
            ];
        }

        return $this->json([
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
    }

    #[Route('/players/{id}', name: 'player_get', methods: ['GET'])]
    public function getPlayer(EntityManagerInterface $entityManager, $id): Response
    {
        $player = $entityManager->getRepository(Player::class)->find($id);

        if(!$player)
        {
            return $this->json(['error' => 'Player not found'], 404);
        }

        $data = [
            'nombre' => $player->getNombre(),
            'apellidos' => $player->getApellidos(),
            'dorsal' => $player->getDorsal(),
            'salario' => $player->getSalario(),
            'club' => $player->getClub()->getNombre(),
            'entrenador' => $player->getClub()->getCoaches()->first()->getNombre() . ' ' . $player->getClub()->getCoaches()->first()->getApellidos()
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

        //$this->sendEmailRemoved($player, $club);
        /*
            $email = (new Email())
                ->from($_ENV['MAILER_FROM_EMAIL'])
                ->to('iago.francisco@siweb.es')
                ->subject('Jugador registrado - ' . $club->getNombre())
                ->html('El jugador ' . $player->getNombre() . ' ' . $player->getApellidos(). ' ha sido dado de baja en el club ' . $club->getNombre());
            
            $mailer->send($email);
        */
        return $this->json(['message' => 'Player deleted successfully']);
    }

    #[Route('/players', name: 'player_create', methods: ['POST'])]
    public function createPlayer(EntityManagerInterface $entityManager, MailerInterface $mailer, Request $request): Response
    {
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
        if (empty($nombre) || empty($apellidos) || empty($dorsal) || empty($salario) || empty($id_club)) {
            return $this->json(['error' => 'Todos los campos son requeridos'], 400);
        }

        // Validar que el club existe
        $club = $entityManager->getRepository(Club::class)->find($id_club);
        if (!$club) {
            return $this->json(['error' => 'Club not found'], 404);
        }

        //Guardamos los dorsales del club del jugador
        $playersDelClub = $entityManager->getRepository(Player::class)->findBy(['club' => $club]);
        $dorsales = [];
        foreach($playersDelClub as $p){
            $dorsales[] = $p->getDorsal();
        }

        //Creamos al jugador y le asignamos los datos
        $player = new Player();
        $player->setNombre($nombre);
        $player->setApellidos($apellidos);
        if($dorsal <= 0 || $dorsal> 99) {
            return $this->json(['error' => 'El dorsal debe ser mayor que 0 y menor que 100'], 400);
        }else if(in_array($dorsal, $dorsales)){
            return $this->json(['error' => 'El dorsal ya existe en el club'], 400);
        }else{
            $player->setDorsal($dorsal);
        }
        $player->setSalario($salario);
        $player->setClub($club);

        //Guardamos el jugador en la base de datos
        $entityManager->persist($player);
        $entityManager->flush();

        //Enviamos el email
        #$this->sendEmailRegistered($player, $club);
        /*
            $email = (new Email())
                ->from($_ENV['MAILER_FROM_EMAIL'])
                ->to('iago.francisco@siweb.es')
                ->subject('Jugador registrado - ' . $club->getNombre())
                ->html('El jugador ' . $player->getNombre() . ' ' . $player->getApellidos(). ' ha sido dado de alta en el club ' . $club->getNombre());

            $mailer->send($email);
        */
        //Devolvemos el mensaje de éxito
        return $this->json(['message' => 'Player created successfully']);
        
    }

    #[Route('/players/{id}', name: 'player_update', methods: ['PUT'])]
    public function updatePlayer(EntityManagerInterface $entityManager, $id, Request $request): Response
    {
        $player = $entityManager->getRepository(Player::class)->find($id);
        
        if(!$player){
            return $this->json(['error' => 'Player not found'], 404);
        }

        // Para ver todos los dorsales del club del jugador
        $club = $player->getClub();
        $playersDelClub = $entityManager->getRepository(Player::class)->findBy(['club' => $club]);
        
        //Guardamos los dorsales del club del jugador
        $dorsales = [];
        foreach($playersDelClub as $p){
            $dorsales[] = $p->getDorsal();
        }
        
        //Obtenemos los datos del JSON
        $body = $request->getContent();
        $jsonData = json_decode($body, true);

        if(empty($jsonData)){
            return $this->json(['error' => 'No hay datos para actualizar'], 400);
        }

        //Hacemos validaciones
        if(isset($jsonData['dni'])){
            return $this->json(['error' => 'El DNI no puede ser modificado'], 400);
        }
        if(isset($jsonData['nombre'])){
            $player->setNombre($jsonData['nombre']);
        }
        if(isset($jsonData['apellidos'])){
            $player->setApellidos($jsonData['apellidos']);
        }
        if(isset($jsonData['dorsal'])){
            if($jsonData['dorsal'] <= 0 || $jsonData['dorsal'] > 99) {
                return $this->json(['error' => 'El dorsal debe ser mayor que 0 y menor que 100'], 400);
            }else if(in_array($jsonData['dorsal'], $dorsales)){
                return $this->json(['error' => 'El dorsal ya existe en el club'], 400);
            }else{
                $player->setDorsal($jsonData['dorsal']);
            }
        }
        if(isset($jsonData['salario'])){
            $player->setSalario($jsonData['salario']);
            if($jsonData['salario'] <= 0){
                return $this->json(['error' => 'El salario no puede ser 0 o negativo'], 400);
            }
        }
        if(isset($jsonData['id_club'])){
            $club = $entityManager->getRepository(Club::class)->find($jsonData['id_club']);
            if(!$club){
                return $this->json(['error' => 'Club not found'], 404);
            }
            $player->setClub($club);
        }

        //Devolvemos el mensaje de éxito
        return $this->json(['message' => 'Player updated successfully']);

    }    

/*

    public function sendEmailRemoved(Object $player, Object $club):Response
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
            $mail->Body = 'El jugador ' . $player->getNombre() . ' ' . $player->getApellidos() . ' ha sido eliminado del club ' . $club->getNombre();
            $mail->send();
            return $this->json(['message' => 'Email enviado correctamente']);
        }catch(Exception $e){
            return $this->json(['error' => 'Error al enviar el email'], 500);
        }
    }
    
    public function sendEmailRegistered(Object $player, Object $club):Response
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
            $mail->Subject = 'Jugador registrado - ' . $club->getNombre();
            $mail->Body = 'El jugador ' . $player->getNombre() . ' ' . $player->getApellidos() . ' ha sido dado de alta en el club ' . $club->getNombre();
            $mail->send();
            return $this->json(['message' => 'Email enviado correctamente']);
        } catch(Exception $e) {
            return $this->json(['error' => 'Error al enviar el email: ' . $e->getMessage()], 500);
        }
    }
        */
}