<?php

namespace App\Controller;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;


class UsuarioController extends AbstractController
{
    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function __getUser():JsonResponse
    {
        $user = $this->getUser();
        return new JsonResponse([
            'id' => $user->getId(),
            'nombre' => $user->getNombre(),
            'email' => $user->getEmail(),
        ]);

    }
}