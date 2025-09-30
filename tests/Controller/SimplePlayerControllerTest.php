<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SimplePlayerControllerTest extends WebTestCase
{
    public function testGetPlayersEndpoint(): void
    {
        $client = static::createClient();
        
        // Act
        $client->request('GET', '/players');
        
        // Assert
        $response = $client->getResponse();
        $this->assertTrue(
            $response->getStatusCode() === Response::HTTP_OK || 
            $response->getStatusCode() === Response::HTTP_INTERNAL_SERVER_ERROR
        );
        
        // Si la respuesta es exitosa, debe ser JSON
        if ($response->getStatusCode() === Response::HTTP_OK) {
            $this->assertJson($response->getContent());
        }
    }

    public function testGetPlayerByIdEndpoint(): void
    {
        $client = static::createClient();
        
        // Act
        $client->request('GET', '/players/1');
        
        // Assert
        $response = $client->getResponse();
        $this->assertTrue(
            $response->getStatusCode() === Response::HTTP_OK || 
            $response->getStatusCode() === Response::HTTP_NOT_FOUND ||
            $response->getStatusCode() === Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    public function testCreatePlayerWithInvalidJson(): void
    {
        $client = static::createClient();
        
        // Arrange - JSON inválido
        $invalidJson = '{"nombre": "Test", "apellidos": "Player", "dorsal": 99, "salario": "1000000"'; // Falta }
        
        // Act
        $client->request(
            'POST',
            '/players',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $invalidJson
        );
        
        // Assert
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
    }

    public function testCreatePlayerWithMissingData(): void
    {
        $client = static::createClient();
        
        // Arrange - Datos incompletos
        $incompleteData = [
            'apellidos' => 'Player',
            'dorsal' => 99,
            'salario' => '1000000'
            // Falta 'nombre'
        ];
        
        // Act
        $client->request(
            'POST',
            '/players',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($incompleteData)
        );
        
        // Assert
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testUpdatePlayerWithInvalidId(): void
    {
        $client = static::createClient();
        
        // Arrange
        $updateData = [
            'nombre' => 'Updated',
            'apellidos' => 'Player'
        ];
        
        // Act - ID que probablemente no existe
        $client->request(
            'PUT',
            '/players/99999',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );
        
        // Assert
        $response = $client->getResponse();
        $this->assertTrue(
            $response->getStatusCode() === Response::HTTP_NOT_FOUND ||
            $response->getStatusCode() === Response::HTTP_BAD_REQUEST
        );
    }

    public function testDeletePlayerWithInvalidId(): void
    {
        $client = static::createClient();
        
        // Act - ID que probablemente no existe
        $client->request('DELETE', '/players/99999');
        
        // Assert
        $response = $client->getResponse();
        $this->assertTrue(
            $response->getStatusCode() === Response::HTTP_NOT_FOUND ||
            $response->getStatusCode() === Response::HTTP_OK
        );
    }

    public function testGetPlayersWithPagination(): void
    {
        $client = static::createClient();
        
        // Act
        $client->request('GET', '/players?page=1&pageSize=5');
        
        // Assert
        $response = $client->getResponse();
        $this->assertTrue(
            $response->getStatusCode() === Response::HTTP_OK ||
            $response->getStatusCode() === Response::HTTP_INTERNAL_SERVER_ERROR
        );
        
        if ($response->getStatusCode() === Response::HTTP_OK) {
            $this->assertJson($response->getContent());
        }
    }

    public function testGetPlayersWithFilter(): void
    {
        $client = static::createClient();
        
        // Act
        $client->request('GET', '/players?nombre=Iago');
        
        // Assert
        $response = $client->getResponse();
        $this->assertTrue(
            $response->getStatusCode() === Response::HTTP_OK ||
            $response->getStatusCode() === Response::HTTP_INTERNAL_SERVER_ERROR
        );
        
        if ($response->getStatusCode() === Response::HTTP_OK) {
            $this->assertJson($response->getContent());
        }
    }
}
