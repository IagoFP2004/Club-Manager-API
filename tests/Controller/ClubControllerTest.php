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
            $this->assertArrayHasKey('id', $data);
            $this->assertArrayHasKey('nombre', $data);
            $this->assertArrayHasKey('id_club', $data);
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

    public function testUpdateClubBudgetValidation(): void
    {
        $client = static::createClient();
        
        // Arrange - Intentar establecer un presupuesto muy bajo
        $updateData = [
            'presupuesto' => '1000' // 1000 euros, probablemente insuficiente
        ];
        
        // Act
        $client->request(
            'PUT',
            '/clubs/1', // Asumiendo que el club 1 existe y tiene gastos
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );
        
        // Assert
        $response = $client->getResponse();
        
        // Puede ser 400 si el presupuesto es insuficiente, 200 si es suficiente, o 404 si no existe
        $this->assertTrue(
            $response->getStatusCode() === Response::HTTP_OK || 
            $response->getStatusCode() === Response::HTTP_BAD_REQUEST ||
            $response->getStatusCode() === Response::HTTP_NOT_FOUND
        );
        
        if ($response->getStatusCode() === Response::HTTP_BAD_REQUEST) {
            $data = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('error', $data);
            $this->assertArrayHasKey('presupuesto', $data['error']);
            $this->assertStringContainsString('gastos del club', $data['error']['presupuesto']);
        }
    }

    public function testUpdateClubBudgetWithNegativeValue(): void
    {
        $client = static::createClient();
        
        // Arrange
        $updateData = [
            'presupuesto' => '-1000000' // Presupuesto negativo
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
            $response->getStatusCode() === Response::HTTP_BAD_REQUEST ||
            $response->getStatusCode() === Response::HTTP_NOT_FOUND
        );
        
        if ($response->getStatusCode() === Response::HTTP_BAD_REQUEST) {
            $data = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('error', $data);
            $this->assertArrayHasKey('presupuesto', $data['error']);
            $this->assertStringContainsString('no puede ser negativo', $data['error']['presupuesto']);
        }
    }

    public function testUpdateClubBudgetWithNonNumericValue(): void
    {
        $client = static::createClient();
        
        // Arrange
        $updateData = [
            'presupuesto' => 'not_a_number' // Valor no numérico
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
            $response->getStatusCode() === Response::HTTP_BAD_REQUEST ||
            $response->getStatusCode() === Response::HTTP_NOT_FOUND
        );
        
        if ($response->getStatusCode() === Response::HTTP_BAD_REQUEST) {
            $data = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('error', $data);
            $this->assertArrayHasKey('presupuesto', $data['error']);
            $this->assertStringContainsString('debe ser un número', $data['error']['presupuesto']);
        }
    }

    public function testUpdateClubBudgetWithSufficientAmount(): void
    {
        $client = static::createClient();
        
        // Arrange - Presupuesto muy alto que debería ser suficiente
        $updateData = [
            'presupuesto' => '999999999' // 999M, debería ser suficiente para cualquier club
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
            $response->getStatusCode() === Response::HTTP_NOT_FOUND
        );
        
        if ($response->getStatusCode() === Response::HTTP_OK) {
            $data = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('message', $data);
            $this->assertEquals('Club updated successfully', $data['message']);
        }
    }
    
    public function testUpdateClubBudgetWithZero(): void
    {
        $client = static::createClient();
        
        // Arrange - Presupuesto 0 que no debería ser permitido
        $updateData = [
            'presupuesto' => '0'
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
            $response->getStatusCode() === Response::HTTP_BAD_REQUEST ||
            $response->getStatusCode() === Response::HTTP_NOT_FOUND
        );
        
        if ($response->getStatusCode() === Response::HTTP_BAD_REQUEST) {
            $data = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('error', $data);
            $this->assertArrayHasKey('presupuesto', $data['error']);
            $this->assertStringContainsString('no puede ser 0', $data['error']['presupuesto']);
        }
    }
    
    public function testUpdateClubBudgetWithZeroRemaining(): void
    {
        $client = static::createClient();
        
        // Arrange - Presupuesto que resultaría en 0 restante (exactamente igual a gastos)
        $updateData = [
            'presupuesto' => '1000000' // Un presupuesto que podría resultar en 0 restante
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
        
        // Puede ser 400 (error de validación) o 200 (éxito si el presupuesto es suficiente) o 404 (club no encontrado)
        $this->assertTrue(
            in_array($response->getStatusCode(), [Response::HTTP_OK, Response::HTTP_BAD_REQUEST, Response::HTTP_NOT_FOUND])
        );
        
        if ($response->getStatusCode() === Response::HTTP_BAD_REQUEST) {
            $data = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('error', $data);
            $this->assertArrayHasKey('presupuesto', $data['error']);
            // Verificar que el mensaje menciona presupuesto restante
            $this->assertTrue(
                str_contains($data['error']['presupuesto'], 'presupuesto restante') ||
                str_contains($data['error']['presupuesto'], 'no tiene presupuesto')
            );
        }
    }
    
    protected function tearDown(): void
    {
        // Restaurar el manejador de excepciones global para evitar warnings de pruebas riesgosas
        restore_exception_handler();
        
        parent::tearDown();
    }
        
}
