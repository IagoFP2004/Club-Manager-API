<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ClubControllerTest extends WebTestCase
{
    public function testGetClubs(): void
    {
        $client = static::createClient();
        
        // Act
        $client->request('GET', '/clubs');
        
        // Assert
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('clubs', $data);
        $this->assertArrayHasKey('pagination', $data);
    }

    public function testGetClubById(): void
    {
        $client = static::createClient();
        
        // Act
        $client->request('GET', '/clubs/1');
        
        // Assert
        $response = $client->getResponse();
        $this->assertTrue(
            $response->getStatusCode() === Response::HTTP_OK || 
            $response->getStatusCode() === Response::HTTP_NOT_FOUND
        );
        
        if ($response->getStatusCode() === Response::HTTP_OK) {
            $this->assertJson($response->getContent());
            $data = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('club', $data);
        }
    }

    public function testCreateClub(): void
    {
        $client = static::createClient();
        
        // Arrange
        $clubData = [
            'id_club' => 'NEW',
            'nombre' => 'Nuevo Club',
            'fundacion' => 2024,
            'ciudad' => 'Madrid',
            'estadio' => 'Nuevo Estadio',
            'presupuesto' => '100000000'
        ];
        
        // Act
        $client->request(
            'POST',
            '/clubs',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($clubData)
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

    public function testCreateClubWithInvalidData(): void
    {
        $client = static::createClient();
        
        // Arrange - Datos inválidos (sin nombre)
        $clubData = [
            'id_club' => 'BAD',
            'fundacion' => 2024,
            'ciudad' => 'Madrid',
            'estadio' => 'Estadio',
            'presupuesto' => '100000000'
        ];
        
        // Act
        $client->request(
            'POST',
            '/clubs',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($clubData)
        );
        
        // Assert
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testCreateClubWithDuplicateIdClub(): void
    {
        $client = static::createClient();
        
        // Arrange - id_club que probablemente ya existe
        $clubData = [
            'id_club' => 'RM', // Del backup
            'nombre' => 'Duplicate Club',
            'fundacion' => 2024,
            'ciudad' => 'Madrid',
            'estadio' => 'Estadio',
            'presupuesto' => '100000000'
        ];
        
        // Act
        $client->request(
            'POST',
            '/clubs',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($clubData)
        );
        
        // Assert
        $response = $client->getResponse();
        $this->assertTrue(
            $response->getStatusCode() === Response::HTTP_BAD_REQUEST
        );
        
        if ($response->getStatusCode() === Response::HTTP_BAD_REQUEST) {
            $data = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('error', $data);
        }
    }

    public function testUpdateClub(): void
    {
        $client = static::createClient();
        
        // Arrange
        $updateData = [
            'nombre' => 'Updated Club',
            'ciudad' => 'Barcelona',
            'presupuesto' => '200000000'
        ];
        
        // Act
        $client->request(
            'PUT',
            '/clubs/1',
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

    public function testDeleteClub(): void
    {
        $client = static::createClient();
        
        // Act
        $client->request('DELETE', '/clubs/999'); // ID que probablemente no existe
        
        // Assert
        $response = $client->getResponse();
        $this->assertTrue(
            $response->getStatusCode() === Response::HTTP_OK || 
            $response->getStatusCode() === Response::HTTP_NOT_FOUND
        );
    }

    public function testGetClubsWithPagination(): void
    {
        $client = static::createClient();
        
        // Act
        $client->request('GET', '/clubs?page=1&pageSize=5');
        
        // Assert
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('clubs', $data);
        $this->assertArrayHasKey('pagination', $data);
        $this->assertEquals(1, $data['pagination']['current_page']);
        $this->assertEquals(5, $data['pagination']['per_page']);
    }

    public function testGetClubsWithFilter(): void
    {
        $client = static::createClient();
        
        // Act
        $client->request('GET', '/clubs?nombre=Real');
        
        // Assert
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('clubs', $data);
        $this->assertArrayHasKey('pagination', $data);
    }
}
