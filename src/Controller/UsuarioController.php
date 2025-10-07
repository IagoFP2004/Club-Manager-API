<?php

namespace App\Controller;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UsuarioController extends AbstractController
{
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
/**
    #[Route('/api/{email}', name: 'api_email', methods: ['GET'])]
    public function getByEmail(EntityManagerInterface $entityManager, $email): Response
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }

        $data = [
            'id' => $user->getId(),
            'nombres' => $user->getNombre(),
            'email' => $user->getEmail(),
        ];
        return $this->json($data);
    }
**/
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(EntityManagerInterface $entityManager, Request $request):Response
    {
        $errors = [];

        $body = $request->getContent();
        $jsonData = json_decode($body, true);

        if(!$jsonData){
           $errors['json'] = "El JSON no es valido";
        }

        $nombre = $jsonData['nombre'];
        $email = $jsonData['email'];
        $password = $jsonData['password'];

        //Validacion del campo nombre
        if (empty($nombre)){
            $errors['nombre'] = "El nombre es requerido";
        }else if (!is_string($nombre)){
            $errors['nombre'] = "El nombre debe ser un string";
        }else if ($this->contieneCaracteresEspeciales($nombre)){
            $errors['nombre'] = "El nombre no puede contener caracteres especiales";
        }else if ($this->tieneEspaciosAlInicio($nombre)){
            $errors['nombre'] = "El nombre no puede contener caracteres especiales";
        }else if (strlen($nombre) < 3){
            $errors['nombre'] = "El nombre debe tener al menos 3 caracteres";
        }else if(preg_match('/\d/', $nombre)){
            $errors['nombre'] = "El nombre no puede contener numeros";
        }

        //Validacion del campo email
        if (empty($email)){
            $errors['email'] = "El email es requerido";
        }else if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $errors['email'] = "El email no es valido";
        }else if ($this->getByEmail($entityManager,$email)){
            $errors['email'] = "El email ya existe";
        }

        //Validacion del campo password
        if (empty($password)){
            $errors['password'] = "El password es requerido";
        }else if ( strlen($password) < 4 || strlen($password) > 8){
            $errors['password'] = "El password debe tener al menos 4 caracteres y menos de 8";
        }

        if(!empty($errors)){
            return $this->json($errors, 400);
        }

        $user = new User();
        $user->setNombre($nombre);
        $user->setEmail($email);
        $user->setPassword(password_hash($password, PASSWORD_DEFAULT));

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['message' => 'Usuario creado'], 201);
    }

    public function getByEmail(EntityManager $entityManager, string $email): bool
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        return (bool) $user;
    }

}