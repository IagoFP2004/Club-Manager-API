<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

abstract class ApiTestCase extends WebTestCase
{
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    protected function createTestClub(): array
    {
        $client = static::createClient();
        
        $clubData = [
            'id_club' => 'TEST' . uniqid(),
            'nombre' => 'Test Club',
            'fundacion' => 2024,
            'ciudad' => 'Test City',
            'estadio' => 'Test Stadium',
            'presupuesto' => '100000000'
        ];
        
        $client->request(
            'POST',
            '/clubs',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($clubData)
        );
        
        if ($client->getResponse()->getStatusCode() === 200) {
            $responseData = json_decode($client->getResponse()->getContent(), true);
            return [
                'id' => $responseData['club_id'] ?? null,
                'id_club' => $clubData['id_club'],
                'data' => $clubData
            ];
        }
        
        return null;
    }

    protected function createTestPlayer(array $clubInfo = null): array
    {
        $client = static::createClient();
        
        $playerData = [
            'nombre' => 'Test' . uniqid(),
            'apellidos' => 'Player',
            'dorsal' => rand(1, 99),
            'salario' => '1000000'
        ];
        
        if ($clubInfo) {
            $playerData['id_club'] = $clubInfo['id_club'];
        }
        
        $client->request(
            'POST',
            '/players',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($playerData)
        );
        
        if ($client->getResponse()->getStatusCode() === 200) {
            $responseData = json_decode($client->getResponse()->getContent(), true);
            return [
                'id' => $responseData['player_id'] ?? null,
                'data' => $playerData
            ];
        }
        
        return null;
    }

    protected function createTestCoach(array $clubInfo = null): array
    {
        $client = static::createClient();
        
        $coachData = [
            'dni' => 'TEST' . rand(1000, 9999) . chr(rand(65, 90)),
            'nombre' => 'Test' . uniqid(),
            'apellidos' => 'Coach',
            'salario' => '2000000'
        ];
        
        if ($clubInfo) {
            $coachData['id_club'] = $clubInfo['id_club'];
        }
        
        $client->request(
            'POST',
            '/coaches',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($coachData)
        );
        
        if ($client->getResponse()->getStatusCode() === 200) {
            $responseData = json_decode($client->getResponse()->getContent(), true);
            return [
                'id' => $responseData['coach_id'] ?? null,
                'data' => $coachData
            ];
        }
        
        return null;
    }
}
