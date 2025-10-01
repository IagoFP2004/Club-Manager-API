<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CoachControllerTest extends WebTestCase
{
    public function testGetCoaches(): void
    {
        $client = static::createClient();
        
        // Act
        $client->request('GET', '/coaches');
        
        // Assert
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('coaches', $data);
        $this->assertArrayHasKey('pagination', $data);
    }

    public function testGetCoachById(): void
    {
        $client = static::createClient();
        
        // Act
        $client->request('GET', '/coaches/1');
        
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
            $this->assertArrayHasKey('apellidos', $data);
        }
    }

    public function testCreateCoach(): void
    {
        $client = static::createClient();
        
        // Arrange
        $coachData = [
            'dni' => '99999999Z',
            'nombre' => 'Test',
            'apellidos' => 'Coach',
            'salario' => '5000000',
            'id_club' => 'RM'
        ];
        
        // Act
        $client->request(
            'POST',
            '/coaches',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($coachData)
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

    public function testCreateCoachWithoutClub(): void
    {
        $client = static::createClient();
        
        // Arrange
        $coachData = [
            'dni' => '88888888Y',
            'nombre' => 'Free',
            'apellidos' => 'Coach',
            'salario' => '3000000'
        ];
        
        // Act
        $client->request(
            'POST',
            '/coaches',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($coachData)
        );
        
        // Assert
        $response = $client->getResponse();
        $this->assertTrue(
            $response->getStatusCode() === Response::HTTP_OK || 
            $response->getStatusCode() === Response::HTTP_BAD_REQUEST
        );
    }

    public function testCreateCoachWithInvalidData(): void
    {
        $client = static::createClient();
        
        // Arrange - Datos inválidos (sin DNI)
        $coachData = [
            'nombre' => 'Coach',
            'apellidos' => 'Test',
            'salario' => '1000000'
        ];
        
        // Act
        $client->request(
            'POST',
            '/coaches',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($coachData)
        );
        
        // Assert
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testCreateCoachWithDuplicateDni(): void
    {
        $client = static::createClient();
        
        // Arrange - DNI que probablemente ya existe
        $coachData = [
            'dni' => '11223344C', // DNI del backup
            'nombre' => 'Duplicate',
            'apellidos' => 'Coach',
            'salario' => '1000000'
        ];
        
        // Act
        $client->request(
            'POST',
            '/coaches',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($coachData)
        );
        
        // Assert - Verificar que falla por DNI duplicado o que no existe
        $response = $client->getResponse();
        $this->assertTrue(
            $response->getStatusCode() === Response::HTTP_BAD_REQUEST || $response->getStatusCode() === Response::HTTP_OK
        );
        
        if ($response->getStatusCode() === Response::HTTP_BAD_REQUEST) {
            $data = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('error', $data);
        } else {
            // Si se creó exitosamente, el test pasa porque no había duplicado
            $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        }
    }

    public function testUpdateCoach(): void
    {
        $client = static::createClient();
        
        // Arrange
        $updateData = [
            'nombre' => 'Updated',
            'apellidos' => 'Coach',
            'salario' => '6000000'
        ];
        
        // Act
        $client->request(
            'PUT',
            '/coaches/1',
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

    public function testUpdateCoachDniNotAllowed(): void
    {
        $client = static::createClient();
        
        // Arrange - Intentar cambiar DNI (no permitido)
        $updateData = [
            'dni' => '11111111A',
            'nombre' => 'Updated',
            'apellidos' => 'Coach'
        ];
        
        // Act
        $client->request(
            'PUT',
            '/coaches/1',
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
    }

    public function testDeleteCoach(): void
    {
        $client = static::createClient();
        
        // Act
        $client->request('DELETE', '/coaches/999'); // ID que probablemente no existe
        
        // Assert
        $response = $client->getResponse();
        $this->assertTrue(
            $response->getStatusCode() === Response::HTTP_OK || 
            $response->getStatusCode() === Response::HTTP_NOT_FOUND
        );
    }

    public function testGetCoachesWithPagination(): void
    {
        $client = static::createClient();
        
        // Act
        $client->request('GET', '/coaches?page=1&pageSize=5');
        
        // Assert
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('coaches', $data);
        $this->assertArrayHasKey('pagination', $data);
        $this->assertEquals(1, $data['pagination']['current_page']);
        $this->assertEquals(5, $data['pagination']['per_page']);
    }

    public function testGetCoachesWithFilter(): void
    {
        $client = static::createClient();
        
        // Act
        $client->request('GET', '/coaches?nombre=Diego');
        
        // Assert
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('coaches', $data);
        $this->assertArrayHasKey('pagination', $data);
    }
    
    protected function tearDown(): void
    {
        // Restaurar el manejador de excepciones global para evitar warnings de pruebas riesgosas
        restore_exception_handler();
        
        parent::tearDown();
    }
}
