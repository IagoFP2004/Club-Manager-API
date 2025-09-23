<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\DBAL\Connection;

class ClubController extends AbstractController
{
    #[Route('/club', name: 'club_list', methods: ['GET'])]
    public function listClubs(Connection $connection): Response
    {
        $sql = "SELECT id_club, nombre, fundacion, ciudad, estadio, entrenador FROM club";
        $clubs = $connection->fetchAllAssociative($sql);
        
        return $this->json([
            'clubs' => $clubs
        ]);
    }
}