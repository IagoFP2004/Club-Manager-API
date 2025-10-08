<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class UserControllerTest extends WebTestCase
{
    public function testCreateUser():void
    {
        $email = 'usuario+'.bin2hex(random_bytes(4)).'@test.local';

        $client = static::createClient();
        $client->request(
            'POST',
            '/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'nombre' => 'UsuarioTest',
                'email' => $email,
                'password' => 'test123',
            ])
        );

        //Verificamos si la respuesta da 201 (creado)
        $this->assertResponseStatusCodeSame(201);

        //Verficar que el header sea JSON
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        //Descodificamos el body
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $data);
        $this->assertSame('Usuario creado', $data['message']);
    }

    public function testCreateUserEmptyNameError():void
    {
        $email = 'usuario+'.bin2hex(random_bytes(4)).'@test.local';

        $client = static::createClient();
        $client->request(
            'POST',
            '/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'nombre' => '',
                'email' => $email,
                'password' => 'test123',
            ]),JSON_THROW_ON_ERROR
        );

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('nombre', $data);
        $this->assertSame('El nombre es requerido', $data['nombre']);

    }

    public function testCreateUserEmptyEmailError():void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'nombre' => 'UsuarioTest',
                'email' => '',
                'password' => 'test123',
            ]),JSON_THROW_ON_ERROR
        );

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('email', $data);
        $this->assertSame('El email es requerido', $data['email']);
    }

    public function testCreateUserEmptyPasswordError():void
    {
        $email = 'usuario+'.bin2hex(random_bytes(4)).'@test.local';

        $client = static::createClient();
        $client->request(
            'POST',
            '/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'nombre' => '',
                'email' => $email,
                'password' => '',
            ]),JSON_THROW_ON_ERROR
        );

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('password', $data);
        $this->assertSame('El password es requerido', $data['password']);

    }
}
