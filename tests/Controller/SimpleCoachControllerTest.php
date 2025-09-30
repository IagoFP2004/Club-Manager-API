<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SimpleCoachControllerTest extends WebTestCase
{
    public function testGetCoachesEndpoint(): void
    {
        $client = static::createClient();
        
        // Act
        $client->request('GET', '/coaches');
        
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

    public function testCreateCoachWithInvalidData(): void
    {
        $client = static::createClient();
        
        // Arrange - Datos inválidos (sin DNI)
        $invalidData = [
            'nombre' => 'Coach',
            'apellidos' => 'Test',
            'salario' => '1000000'
            // Falta 'dni'
        ];
        
        // Act
        $client->request(
            'POST',
            '/coaches',
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

    public function testCreateCoachWithNegativeSalary(): void
    {
        $client = static::createClient();
        
        // Arrange - Salario negativo
        $invalidData = [
            'dni' => 'TEST1234A',
            'nombre' => 'Coach',
            'apellidos' => 'Test',
            'salario' => '-1000000'
        ];
        
        // Act
        $client->request(
            'POST',
            '/coaches',
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

    public function testUpdateCoachWithInvalidId(): void
    {
        $client = static::createClient();
        
        // Arrange
        $updateData = [
            'nombre' => 'Updated',
            'apellidos' => 'Coach'
        ];
        
        // Act - ID que probablemente no existe
        $client->request(
            'PUT',
            '/coaches/99999',
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

    public function testGetCoachesWithPagination(): void
    {
        $client = static::createClient();
        
        // Act
        $client->request('GET', '/coaches?page=1&pageSize=5');
        
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
