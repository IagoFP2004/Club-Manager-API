<?php

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class PlayerValidationTest extends ApiTestCase
{
    private $client;
    private $em;
    private $clubId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->catchExceptions(false);

        $this->em = static::getContainer()->get('doctrine')->getManager();

        // Esquema limpio en test
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->dropDatabase();
        $meta = $this->em->getMetadataFactory()->getAllMetadata();
        if (!empty($meta)) {
            $tool->createSchema($meta);
        }

        // Crear club para las pruebas
        $club = (new \App\Entity\Club())
            ->setIdClub('RM')
            ->setNombre('Real Madrid')
            ->setFundacion(1902)
            ->setCiudad('Madrid')
            ->setEstadio('Santiago Bernabéu')
            ->setPresupuesto('800000000');

        $this->em->persist($club);
        $this->em->flush();

        $this->clubId = $club->getId();
    }

    public function testCreatePlayerWithAsteriskNameWithoutClub(): void
    {
        // Test: Crear jugador con nombre "*" SIN club - DEBE FALLAR
        $playerData = [
            'nombre' => '*',
            'apellidos' => 'Test',
            'dorsal' => 50,
            'salario' => '1000000'
            // Sin id_club
        ];
        
        $this->client->request(
            'POST',
            '/players',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($playerData)
        );
        
        // Assert: Debe fallar con error de caracteres especiales
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
        
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        // El error ahora es un array con diferentes campos
        $errorMessage = is_array($data['error']) ? json_encode($data['error']) : $data['error'];
        $this->assertStringContainsString('caracteres especiales', $errorMessage);
    }

    public function testCreatePlayerWithAsteriskNameWithClub(): void
    {
        // Test: Crear jugador con nombre "*" CON club - DEBE FALLAR
        $playerData = [
            'nombre' => '*',
            'apellidos' => 'Test',
            'dorsal' => 50,
            'salario' => '1000000',
            'id_club' => 'RM'
        ];
        
        $this->client->request(
            'POST',
            '/players',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($playerData)
        );
        
        // Assert: Debe fallar con error de caracteres especiales
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
        
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        // El error ahora es un array con diferentes campos
        $errorMessage = is_array($data['error']) ? json_encode($data['error']) : $data['error'];
        $this->assertStringContainsString('caracteres especiales', $errorMessage);
    }

    public function testCreatePlayerWithValidNameWithoutClub(): void
    {
        // Test: Crear jugador con nombre válido SIN club - DEBE FUNCIONAR
        $playerData = [
            'nombre' => 'Juan',
            'apellidos' => 'Pérez',
            'dorsal' => 50,
            'salario' => '1000000'
            // Sin id_club
        ];
        
        $this->client->request(
            'POST',
            '/players',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($playerData)
        );
        
        // Assert: Debe funcionar correctamente
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
        
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Player created successfully', $data['message']);
    }

    public function testCreatePlayerWithSpecialCharactersInApellidos(): void
    {
        // Test: Crear jugador con apellidos que contienen caracteres especiales
        $playerData = [
            'nombre' => 'Juan',
            'apellidos' => 'García-López',
            'dorsal' => 50,
            'salario' => '1000000',
            'id_club' => 'RM'
        ];
        
        $this->client->request(
            'POST',
            '/players',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($playerData)
        );
        
        // Assert: Debe fallar con error de caracteres especiales
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
        
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        // El error ahora es un array con diferentes campos
        $errorMessage = is_array($data['error']) ? json_encode($data['error']) : $data['error'];
        $this->assertStringContainsString('caracteres especiales', $errorMessage);
    }

    protected function tearDown(): void
    {
        restore_exception_handler();
        parent::tearDown();
    }
}
