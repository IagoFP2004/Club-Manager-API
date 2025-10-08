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

    //Test del campo de nombre
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

    public function testCreateUserInvalidNameCharactersError():void
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
                'nombre' => '¿',
                'email' => $email,
                'password' => 'test123',
            ]),JSON_THROW_ON_ERROR
        );

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('nombre', $data);
        $this->assertSame('El nombre no puede contener caracteres especiales', $data['nombre']);
    }

    public function testCreateUserNumberInNameError():void
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
                'nombre' => 'UsuarioTest123',
                'email' => $email,
                'password' => 'test123',
            ]),JSON_THROW_ON_ERROR
        );

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('nombre', $data);
        $this->assertSame('El nombre no puede contener numeros', $data['nombre']);
    }

    public function testCreateUserEspacionPrincipioInNameError():void
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
                'nombre' => '   UsuarioTest',
                'email' => $email,
                'password' => 'test123',
            ]),JSON_THROW_ON_ERROR
        );

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('nombre', $data);
        $this->assertSame('El nombre no puede comenzar por espacios', $data['nombre']);
    }

    public function testCreateUserShortNameError():void
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
                'nombre' => 'UT',
                'email' => $email,
                'password' => 'test123',
            ]),JSON_THROW_ON_ERROR
        );

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('nombre', $data);
        $this->assertSame('El nombre debe tener al menos 3 caracteres', $data['nombre']);
    }

    //Test del campo email
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

    //Test del campo de password
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
