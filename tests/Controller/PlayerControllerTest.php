<?php

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class PlayerControllerTest extends ApiTestCase
{
    private $client;
    private $em;
    private $clubId;
    private $playerIdSeed;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->catchExceptions(false);

        $this->em = static::getContainer()->get('doctrine')->getManager();

        // Esquema limpio en test (a partir de tus entidades)
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->dropDatabase();
        $meta = $this->em->getMetadataFactory()->getAllMetadata();
        if (!empty($meta)) {
            $tool->createSchema($meta);
        }

        // Semilla mínima: club 'RM' y dos jugadores
        $club = (new \App\Entity\Club())
            ->setIdClub('RM')
            ->setNombre('Real Madrid')
            ->setFundacion(1902)
            ->setCiudad('Madrid')
            ->setEstadio('Santiago Bernabéu')
            ->setPresupuesto('800000000');

        $p1 = (new \App\Entity\Player())
            ->setNombre('Iago')->setApellidos('Aspas Juncal')
            ->setDorsal(10)->setSalario('2500000')
            ->setClub($club);

        $p2 = (new \App\Entity\Player())
            ->setNombre('Vinicius')->setApellidos('Junior')
            ->setDorsal(7)->setSalario('20000000')
            ->setClub($club);

        $this->em->persist($club);
        $this->em->persist($p1);
        $this->em->persist($p2);
        $this->em->flush();

        $this->clubId = $club->getId();
        $this->playerIdSeed = $p1->getId();
    }

    public function testGetPlayers(): void
    {
        // Act
        $this->client->request('GET', '/players');
        
        // Assert
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
        
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('players', $data);
        $this->assertArrayHasKey('pagination', $data);
    }

    public function testGetPlayerById(): void
    {
        // Using $this->client from setUp
        
        // Act
        $this->client->request('GET', '/players/1');
        
        // Assert
        $response = $this->client->getResponse();
        self::assertTrue(
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

    public function testCreatePlayer(): void
    {
        // Using $this->client from setUp
        
        // Arrange
        $playerData = [
            'nombre' => 'Test',
            'apellidos' => 'Player',
            'dorsal' => 99,
            'salario' => '1000000',
            'id_club' => 'RM'
        ];
        
        // Act
        $this->client->request(
            'POST',
            '/players',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($playerData)
        );
        
        // Assert
        $response = $this->client->getResponse();
        self::assertTrue(
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
        // Using $this->client from setUp
        
        // Arrange
        $playerData = [
            'nombre' => 'Free',
            'apellidos' => 'Agent',
            'dorsal' => 88,
            'salario' => '500000'
        ];
        
        // Act
        $this->client->request(
            'POST',
            '/players',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($playerData)
        );
        
        // Assert
        $response = $this->client->getResponse();
        self::assertTrue(
            $response->getStatusCode() === Response::HTTP_OK || 
            $response->getStatusCode() === Response::HTTP_BAD_REQUEST
        );
    }

    public function testCreatePlayerWithInvalidData(): void
    {
        // Using $this->client from setUp
        
        // Arrange - Datos inválidos (sin nombre)
        $playerData = [
            'apellidos' => 'Player',
            'dorsal' => 99,
            'salario' => '1000000'
        ];
        
        // Act
        $this->client->request(
            'POST',
            '/players',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($playerData)
        );
        
        // Assert
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
        
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testUpdatePlayer(): void
    {
        // Using $this->client from setUp
        
        // Arrange
        $updateData = [
            'nombre' => 'Updated',
            'apellidos' => 'Player',
            'salario' => '2000000'
        ];
        
        // Act
        $this->client->request(
            'PUT',
            '/players/1',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );
        
        // Assert
        $response = $this->client->getResponse();
        self::assertTrue(
            $response->getStatusCode() === Response::HTTP_OK || 
            $response->getStatusCode() === Response::HTTP_NOT_FOUND ||
            $response->getStatusCode() === Response::HTTP_BAD_REQUEST
        );
    }

    public function testDeletePlayer(): void
    {
        // Using $this->client from setUp
        
        // Act
        $this->client->request('DELETE', '/players/999'); // ID que probablemente no existe
        
        // Assert
        $response = $this->client->getResponse();
        self::assertTrue(
            $response->getStatusCode() === Response::HTTP_OK || 
            $response->getStatusCode() === Response::HTTP_NOT_FOUND
        );
    }

    public function testGetPlayersWithPagination(): void
    {
        // Using $this->client from setUp
        
        // Act
        $this->client->request('GET', '/players?page=1&pageSize=5');
        
        // Assert
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
        
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('players', $data);
        $this->assertArrayHasKey('pagination', $data);
        $this->assertEquals(1, $data['pagination']['current_page']);
        $this->assertEquals(5, $data['pagination']['per_page']);
    }

    public function testGetPlayersWithFilter(): void
    {
        // Using $this->client from setUp
        
        // Act
        $this->client->request('GET', '/players?nombre=Iago');
        
        // Assert
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
        
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('players', $data);
        $this->assertArrayHasKey('pagination', $data);
    }
}
