<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class BudgetValidationTest extends WebTestCase
{
    public function testCreatePlayerExceedsBudget(): void
    {
        $client = static::createClient();
        
        // Arrange - Crear un club con presupuesto pequeño
        $clubData = [
            'id_club' => 'SMALL',
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
        
        // Si el club se creó exitosamente, intentar crear un jugador con salario alto
        if ($client->getResponse()->getStatusCode() === Response::HTTP_OK) {
            $playerData = [
                'nombre' => 'Expensive',
                'apellidos' => 'Player',
                'dorsal' => 1,
                'salario' => '2000000', // 2M - excede el presupuesto
                'id_club' => 'SMALL'
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
            $this->assertStringContainsString('presupuesto', strtolower($data['error']));
        }
    }

    public function testCreateCoachExceedsBudget(): void
    {
        $client = static::createClient();
        
        // Arrange - Crear un club con presupuesto pequeño
        $clubData = [
            'id_club' => 'TINY',
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
        
        // Si el club se creó exitosamente, intentar crear un entrenador con salario alto
        if ($client->getResponse()->getStatusCode() === Response::HTTP_OK) {
            $coachData = [
                'dni' => '123456789A',
                'nombre' => 'Expensive',
                'apellidos' => 'Coach',
                'salario' => '1000000', // 1M - excede el presupuesto
                'id_club' => 'TINY'
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
            $this->assertStringContainsString('presupuesto', strtolower($data['error']));
        }
    }

    public function testUpdatePlayerSalaryExceedsBudget(): void
    {
        $client = static::createClient();
        
        // Arrange - Crear un club con presupuesto pequeño
        $clubData = [
            'id_club' => 'UPDATE',
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
        
        // Si el club se creó, crear un jugador con salario bajo
        if ($client->getResponse()->getStatusCode() === Response::HTTP_OK) {
            $playerData = [
                'nombre' => 'Update',
                'apellidos' => 'Player',
                'dorsal' => 1,
                'salario' => '500000', // 500K
                'id_club' => 'UPDATE'
            ];
            
            $client->request(
                'POST',
                '/players',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode($playerData)
            );
            
            // Si el jugador se creó, intentar actualizar su salario a uno muy alto
            if ($client->getResponse()->getStatusCode() === Response::HTTP_OK) {
                $responseData = json_decode($client->getResponse()->getContent(), true);
                if (isset($responseData['player_id'])) {
                    $updateData = [
                        'salario' => '3000000' // 3M - excede el presupuesto
                    ];
                    
                    // Act
                    $client->request(
                        'PUT',
                        '/players/' . $responseData['player_id'],
                        [],
                        [],
                        ['CONTENT_TYPE' => 'application/json'],
                        json_encode($updateData)
                    );
                    
                    // Assert
                    $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
                    $this->assertJson($client->getResponse()->getContent());
                    
                    $data = json_decode($client->getResponse()->getContent(), true);
                    $this->assertArrayHasKey('error', $data);
                    $this->assertStringContainsString('presupuesto', strtolower($data['error']));
                }
            }
        }
    }

    public function testValidBudgetOperations(): void
    {
        $client = static::createClient();
        
        // Arrange - Crear un club con presupuesto suficiente
        $clubData = [
            'id_club' => 'VALID',
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
        
        // Si el club se creó exitosamente
        if ($client->getResponse()->getStatusCode() === Response::HTTP_OK) {
            // Crear jugador con salario válido
            $playerData = [
                'nombre' => 'Valid',
                'apellidos' => 'Player',
                'dorsal' => 1,
                'salario' => '2000000', // 2M
                'id_club' => 'VALID'
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
            $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
            
            // Crear entrenador con salario válido
            $coachData = [
                'dni' => '987654321A',
                'nombre' => 'Valid',
                'apellidos' => 'Coach',
                'salario' => '3000000', // 3M
                'id_club' => 'VALID'
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
            $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        }
    }
}
