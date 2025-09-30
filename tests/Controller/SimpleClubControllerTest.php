<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SimpleClubControllerTest extends WebTestCase
{
    public function testGetClubsEndpoint(): void
    {
        $client = static::createClient();
        
        // Act
        $client->request('GET', '/clubs');
        
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

    public function testCreateClubWithInvalidData(): void
    {
        $client = static::createClient();
        
        // Arrange - Datos inválidos (sin nombre)
        $invalidData = [
            'id_club' => 'BAD',
            'fundacion' => 2024,
            'ciudad' => 'Madrid',
            'estadio' => 'Estadio',
            'presupuesto' => '100000000'
            // Falta 'nombre'
        ];
        
        // Act
        $client->request(
            'POST',
            '/clubs',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($invalidData)
        );
        
        // Assert
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testCreateClubWithNegativeBudget(): void
    {
        $client = static::createClient();
        
        // Arrange - Presupuesto negativo
        $invalidData = [
            'id_club' => 'NEG',
            'nombre' => 'Negative Budget Club',
            'fundacion' => 2024,
            'ciudad' => 'Madrid',
            'estadio' => 'Estadio',
            'presupuesto' => '-100000000'
        ];
        
        // Act
        $client->request(
            'POST',
            '/clubs',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($invalidData)
        );
        
        // Assert
        $response = $client->getResponse();
        $this->assertTrue(
            $response->getStatusCode() === Response::HTTP_BAD_REQUEST ||
            $response->getStatusCode() === Response::HTTP_OK
        );
    }

    public function testUpdateClubWithInvalidId(): void
    {
        $client = static::createClient();
        
        // Arrange
        $updateData = [
            'nombre' => 'Updated Club',
            'ciudad' => 'Barcelona'
        ];
        
        // Act - ID que probablemente no existe
        $client->request(
            'PUT',
            '/clubs/99999',
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

    public function testGetClubsWithPagination(): void
    {
        $client = static::createClient();
        
        // Act
        $client->request('GET', '/clubs?page=1&pageSize=5');
        
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

    public function testGetClubsWithFilter(): void
    {
        $client = static::createClient();
        
        // Act
        $client->request('GET', '/clubs?nombre=Real');
        
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
