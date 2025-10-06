<?php
// src/EventListener/JWTAuthenticationSuccessListener.php

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

class JWTAuthenticationSuccessListener
{
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();

        $data = $event->getData();

        $data['user'] = [
            'id'      => $user->getId(),
            'nombres' => method_exists($user, 'getNombre') ? $user->getNombre() : null,
            'email'   => $user->getEmail(),
        ];

        $event->setData($data);
    }
}
