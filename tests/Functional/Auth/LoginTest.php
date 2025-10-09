<?php

namespace App\Tests\Functional\Auth;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginTest extends WebTestCase
{
    public function testLogin(): void
    {
        $client    = static::createClient();
        $container = static::getContainer();

        $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);


        $factory = $container->get(\Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface::class);
        $passwordHasher = $factory->getPasswordHasher(\App\Entity\User::class);

        // Crea el usuario de prueba con password hasheada
        $email = 'login+'.bin2hex(random_bytes(3)).'@test.local';
        $user  = new \App\Entity\User();
        $user->setNombre('UsuarioTest');
        $user->setEmail($email);
        $user->setPassword($passwordHasher->hash('abc123'));

        $em->persist($user);
        $em->flush();

        // Ejecuta el login (json_login estándar)
        $client->request(
            'POST',
            '/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email'    => $email,    // debe coincidir con username_path de security.yaml
                'password' => 'abc123',  // y con password_path
            ], JSON_THROW_ON_ERROR)
        );

        $status = $client->getResponse()->getStatusCode();
        $this->assertTrue(in_array($status, [200, 204], true), "Código inesperado: $status");
    }

    public function testLoginEmptyError(): void
    {
        $client    = static::createClient();
        $container = static::getContainer();

        $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);


        $factory = $container->get(\Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface::class);
        $passwordHasher = $factory->getPasswordHasher(\App\Entity\User::class);

        // Crea el usuario de prueba con password hasheada
        $email = 'login+'.bin2hex(random_bytes(3)).'@test.local';
        $user  = new \App\Entity\User();
        $user->setNombre('UsuarioTest');
        $user->setEmail($email);
        $user->setPassword($passwordHasher->hash('abc123'));

        $em->persist($user);
        $em->flush();

        // Ejecuta el login (json_login estándar)
        $client->request(
            'POST',
            '/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email'    => '',    // debe coincidir con username_path de security.yaml
                'password' => '',  // y con password_path
            ], JSON_THROW_ON_ERROR)
        );

        $status = $client->getResponse()->getStatusCode();
        $this->assertTrue(in_array($status, [400, 401], true), "Código inesperado: $status");
    }

    public function testLoginEmailError(): void
    {
        $client    = static::createClient();
        $container = static::getContainer();

        $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);


        $factory = $container->get(\Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface::class);
        $passwordHasher = $factory->getPasswordHasher(\App\Entity\User::class);

        // Crea el usuario de prueba con password hasheada
        $email = 'login+'.bin2hex(random_bytes(3)).'@test.local';
        $user  = new \App\Entity\User();
        $user->setNombre('UsuarioTest');
        $user->setEmail($email);
        $user->setPassword($passwordHasher->hash('abc123'));

        $em->persist($user);
        $em->flush();

        // Ejecuta el login (json_login estándar)
        $client->request(
            'POST',
            '/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email'    => '¿',
                'password' => 'abc123',
            ], JSON_THROW_ON_ERROR)
        );

        $status = $client->getResponse()->getStatusCode();
        $this->assertTrue(in_array($status, [400, 401], true), "Código inesperado: $status");
    }

    public function testLoginPasswordError(): void
    {
        $client    = static::createClient();
        $container = static::getContainer();

        $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);


        $factory = $container->get(\Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface::class);
        $passwordHasher = $factory->getPasswordHasher(\App\Entity\User::class);

        // Crea el usuario de prueba con password hasheada
        $email = 'login+'.bin2hex(random_bytes(3)).'@test.local';
        $user  = new \App\Entity\User();
        $user->setNombre('UsuarioTest');
        $user->setEmail($email);
        $user->setPassword($passwordHasher->hash('abc123'));

        $em->persist($user);
        $em->flush();

        // Ejecuta el login (json_login estándar)
        $client->request(
            'POST',
            '/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email'    => 'UsuarioTest',
                'password' => '?',
            ], JSON_THROW_ON_ERROR)
        );

        $status = $client->getResponse()->getStatusCode();
        $this->assertTrue(in_array($status, [400, 401], true), "Código inesperado: $status");
    }
}