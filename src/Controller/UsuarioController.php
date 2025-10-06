<?php

namespace App\Controller;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
class UsuarioController extends AbstractController
{
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
}