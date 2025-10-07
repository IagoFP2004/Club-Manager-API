<?php

namespace App\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    public function testCreateUser(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false); // <- muestra la excepción real si hay 500

        $userData = [
            'nombre' => 'John Doe',
            'email' => 'jd'.uniqid().'@example.com', // evita colisiones si hay unique
            'password' => 'abc123',
        ];

        $client->request(
            'POST',
            '/register',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT'  => 'application/json',
            ],
            content: json_encode($userData, JSON_THROW_ON_ERROR)
        );

        $response = $client->getResponse();
        $content = $response->getContent();

        if ($response->getStatusCode() !== Response::HTTP_CREATED) {
            echo "\nDEBUG BODY:\n".$content."\n";
        }

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode(), $content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('message', $data);
        $this->assertSame('Usuario creado', $data['message']);
    }
}
