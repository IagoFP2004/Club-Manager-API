<?php

namespace App\Controller;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class UsuarioController extends AbstractController
{
    #[Route('/user/{email}', name: 'user')]
}