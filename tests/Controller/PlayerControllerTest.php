<?php

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class PlayerControllerTest extends ApiTestCase
{
    public function testGetPlayers(): void
    {
        $client = static::createClient();
        
        // Act
        $client->request('GET', '/players');
        
        // Assert
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('players', $data);
        $this->assertArrayHasKey('pagination', $data);
    }

    public function testGetPlayerById(): void
    {
        $client = static::createClient();
        
        // Act
        $client->request('GET', '/players/1');
        
        // Assert
        $response = $client->getResponse();
        $this->assertTrue(
            $response->getStatusCode() === Response::HTTP_OK || 
            $response->getStatusCode() === Response::HTTP_NOT_FOUND
        );
        
        if ($response->getStatusCode() === Response::HTTP_OK) {
            $this->assertJson($response->getContent());
            $data = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('player', $data);
        }
    }

    public function testCreatePlayer(): void
    {
        $client = static::createClient();
        
        // Arrange
        $playerData = [
            'nombre' => 'Test',
            'apellidos' => 'Player',
            'dorsal' => 99,
            'salario' => '1000000',
            'id_club' => 'RM'
        ];
        
        // Act
        $client->request(
            'POST',
            '/players',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($playerData)
        );
        
        // Assert
        $response = $client->getResponse();
        $this->assertTrue(
            $response->getStatusCode() === Response::HTTP_OK || 
            $response->getStatusCode() === Response::HTTP_BAD_REQUEST
        );
        
        if ($response->getStatusCode() === Response::HTTP_OK) {
            $this->assertJson($response->getContent());
            $data = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('message', $data);
        }
    }

    public function testCreatePlayerWithoutClub(): void
    {
        $client = static::createClient();
        
        // Arrange
        $playerData = [
            'nombre' => 'Free',
            'apellidos' => 'Agent',
            'dorsal' => 88,
            'salario' => '500000'
        ];
        
        // Act
        $client->request(
            'POST',
            '/players',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($playerData)
        );
        
        // Assert
        $response = $client->getResponse();
        $this->assertTrue(
            $response->getStatusCode() === Response::HTTP_OK || 
            $response->getStatusCode() === Response::HTTP_BAD_REQUEST
        );
    }

    public function testCreatePlayerWithInvalidData(): void
    {
        $client = static::createClient();
        
        // Arrange - Datos inválidos (sin nombre)
        $playerData = [
            'apellidos' => 'Player',
            'dorsal' => 99,
            'salario' => '1000000'
        ];
        
        // Act
        $client->request(
            'POST',
            '/players',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($playerData)
        );
        
        // Assert
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testUpdatePlayer(): void
    {
        $client = static::createClient();
        
        // Arrange
        $updateData = [
            'nombre' => 'Updated',
            'apellidos' => 'Player',
            'salario' => '2000000'
        ];
        
        // Act
        $client->request(
            'PUT',
            '/players/1',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );
        
        // Assert
        $response = $client->getResponse();
        $this->assertTrue(
            $response->getStatusCode() === Response::HTTP_OK || 
            $response->getStatusCode() === Response::HTTP_NOT_FOUND ||
            $response->getStatusCode() === Response::HTTP_BAD_REQUEST
        );
    }

    public function testDeletePlayer(): void
    {
        $client = static::createClient();
        
        // Act
        $client->request('DELETE', '/players/999'); // ID que probablemente no existe
        
        // Assert
        $response = $client->getResponse();
        $this->assertTrue(
            $response->getStatusCode() === Response::HTTP_OK || 
            $response->getStatusCode() === Response::HTTP_NOT_FOUND
        );
    }

    public function testGetPlayersWithPagination(): void
    {
        $client = static::createClient();
        
        // Act
        $client->request('GET', '/players?page=1&pageSize=5');
        
        // Assert
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('players', $data);
        $this->assertArrayHasKey('pagination', $data);
        $this->assertEquals(1, $data['pagination']['current_page']);
        $this->assertEquals(5, $data['pagination']['per_page']);
    }

    public function testGetPlayersWithFilter(): void
    {
        $client = static::createClient();
        
        // Act
        $client->request('GET', '/players?nombre=Iago');
        
        // Assert
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('players', $data);
        $this->assertArrayHasKey('pagination', $data);
    }
}
