<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Connection;

use App\Entity\User;

class PlayerController extends AbstractController
{

    #[Route('/users', name: 'users_list', methods: ['GET'])] 
    public function userListSql(Connection $connection): Response
    {
        $sql = "SELECT id, email, roles FROM user";
        $users = $connection->fetchAllAssociative($sql);
        
        return $this->json([
            'users' => $users
        ]);
    }

    #[Route('/user/registrar', name: 'user_registrar', methods: ['POST'])]
    public function userRegistro(Connection $connection, Request $request): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);
        
        // Validar datos requeridos
        if (!$data || !isset($data['email']) || !isset($data['password'])) {
            return $this->json(['error' => 'Email y password son requeridos'], 400);
        }
        
        // Hashear la contraseña
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $roles = json_encode(['ROLE_USER']); // Convertir array a JSON string
        
        // Insertar usuario con SQL
        $sql = "INSERT INTO user (email, password, roles) VALUES (?, ?, ?)";
        $connection->executeStatement($sql, [
            $data['email'],
            $hashedPassword,
            $roles
        ]);
        
        // Obtener el ID del usuario insertado
        $lastId = $connection->lastInsertId();

        return $this->json([
            'message' => 'Usuario registrado correctamente',
            'email' => $data['email'],
            'id' => $lastId
        ], 201);
    }
}