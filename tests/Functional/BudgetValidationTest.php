<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class BudgetValidationTest extends WebTestCase
{
    protected function tearDown(): void
    {
        // Restaurar el manejador de excepciones global para evitar warnings de pruebas riesgosas
        restore_exception_handler();
        
        parent::tearDown();
    }
    public function testCreatePlayerExceedsBudget(): void
    {
        $client = static::createClient();
        
        // Arrange - Crear un club con presupuesto pequeño
        $clubData = [
            'id_club' => 'SML' . rand(10, 99),
            'nombre' => 'Small Budget Club',
            'fundacion' => 2024,
            'ciudad' => 'Test City',
            'estadio' => 'Test Stadium',
            'presupuesto' => '1000000' // Solo 1M
        ];
        
        $client->request(
            'POST',
            '/clubs',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($clubData)
        );
        
        // Verificar que el club se creó exitosamente
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode(), 'El club debería crearse exitosamente');
        
        // Intentar crear un jugador con salario alto que excede el presupuesto
        $playerData = [
            'nombre' => 'Expensive',
            'apellidos' => 'Player',
            'dorsal' => 1,
            'salario' => '2000000', // 2M - excede el presupuesto
            'id_club' => $clubData['id_club']
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
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode(), 'La creación del jugador debería fallar por exceder el presupuesto');
        $this->assertJson($client->getResponse()->getContent());
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        // El error ahora es un array con diferentes campos
        $errorMessage = is_array($data['error']) ? json_encode($data['error']) : $data['error'];
        $this->assertStringContainsString('presupuesto', strtolower($errorMessage));
    }

    public function testCreateCoachExceedsBudget(): void
    {
        $client = static::createClient();
        
        // Arrange - Crear un club con presupuesto pequeño
        $clubData = [
            'id_club' => 'TNY' . rand(10, 99),
            'nombre' => 'Tiny Budget Club',
            'fundacion' => 2024,
            'ciudad' => 'Test City',
            'estadio' => 'Test Stadium',
            'presupuesto' => '500000' // Solo 500K
        ];
        
        $client->request(
            'POST',
            '/clubs',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($clubData)
        );
        
        // Verificar que el club se creó exitosamente
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode(), 'El club debería crearse exitosamente');
        
        // Intentar crear un entrenador con salario alto que excede el presupuesto
        $coachData = [
            'dni' => '12345678A',
            'nombre' => 'Expensive',
            'apellidos' => 'Coach',
            'salario' => '1000000', // 1M - excede el presupuesto
            'id_club' => $clubData['id_club']
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
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode(), 'La creación del entrenador debería fallar por exceder el presupuesto');
        $this->assertJson($client->getResponse()->getContent());
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        // El error ahora es un array con diferentes campos
        $errorMessage = is_array($data['error']) ? json_encode($data['error']) : $data['error'];
        $this->assertStringContainsString('presupuesto', strtolower($errorMessage));
    }

    public function testUpdatePlayerSalaryExceedsBudget(): void
    {
        $client = static::createClient();
        
        // Arrange - Crear un club con presupuesto pequeño
        $clubData = [
            'id_club' => 'UPD' . rand(10, 99), // Hacer único para evitar conflictos
            'nombre' => 'Update Budget Club',
            'fundacion' => 2024,
            'ciudad' => 'Test City',
            'estadio' => 'Test Stadium',
            'presupuesto' => '2000000' // 2M
        ];
        
        $client->request(
            'POST',
            '/clubs',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($clubData)
        );
        
        // Verificar que el club se creó correctamente
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode(), 'El club debería crearse exitosamente');
        
        // Crear un jugador con salario bajo
        $playerData = [
            'nombre' => 'Update',
            'apellidos' => 'Player',
            'dorsal' => 1,
            'salario' => '500000', // 500K
            'id_club' => $clubData['id_club']
        ];
        
        $client->request(
            'POST',
            '/players',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($playerData)
        );
        
        // Verificar que el jugador se creó correctamente
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode(), 'El jugador debería crearse exitosamente');
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseData, 'La respuesta debería contener message');
        $this->assertStringContainsString('created successfully', strtolower($responseData['message']));
        
        // Ahora intentar crear otro jugador con salario que exceda el presupuesto restante
        $expensivePlayerData = [
            'nombre' => 'Expensive',
            'apellidos' => 'Player',
            'dorsal' => 2,
            'salario' => '2000000', // 2M - excede el presupuesto restante (1.5M)
            'id_club' => $clubData['id_club']
        ];
        
        $client->request(
            'POST',
            '/players',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($expensivePlayerData)
        );
        
        // Assert - Debería fallar por exceder el presupuesto
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode(), 'La creación del segundo jugador debería fallar por exceder el presupuesto');
        $this->assertJson($client->getResponse()->getContent());
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        // El error ahora es un array con diferentes campos
        $errorMessage = is_array($data['error']) ? json_encode($data['error']) : $data['error'];
        $this->assertStringContainsString('presupuesto', strtolower($errorMessage));
    }

    public function testValidBudgetOperations(): void
    {
        $client = static::createClient();
        
        // Arrange - Crear un club con presupuesto suficiente
        $clubData = [
            'id_club' => 'VAL' . rand(10, 99),
            'nombre' => 'Valid Budget Club',
            'fundacion' => 2024,
            'ciudad' => 'Test City',
            'estadio' => 'Test Stadium',
            'presupuesto' => '10000000' // 10M
        ];
        
        $client->request(
            'POST',
            '/clubs',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($clubData)
        );
        
        // Verificar que el club se creó exitosamente
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode(), 'El club debería crearse exitosamente');
        
        // Crear jugador con salario válido
        $playerData = [
            'nombre' => 'Valid',
            'apellidos' => 'Player',
            'dorsal' => 1,
            'salario' => '2000000', // 2M
            'id_club' => $clubData['id_club']
        ];
        
        $client->request(
            'POST',
            '/players',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($playerData)
        );
        
        // Assert - Debería funcionar
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode(), 'El jugador debería crearse exitosamente');
        
        // Crear entrenador con salario válido
        $coachData = [
            'dni' => '98765432A',
            'nombre' => 'Valid',
            'apellidos' => 'Coach',
            'salario' => '3000000', // 3M
            'id_club' => $clubData['id_club']
        ];
        
        $client->request(
            'POST',
            '/coaches',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($coachData)
        );
        
        // Assert - Debería funcionar
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode(), 'El entrenador debería crearse exitosamente');
    }
}
