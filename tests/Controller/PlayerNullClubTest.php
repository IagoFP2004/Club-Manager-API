<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Club;
use App\Entity\Player;

class PlayerNullClubTest extends WebTestCase
{
    private $client;
    private $em;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->catchExceptions(false);

        $this->em = static::getContainer()->get('doctrine')->getManager();

        // Esquema limpio en test
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->dropDatabase();
        $meta = $this->em->getMetadataFactory()->getAllMetadata();
        if (!empty($meta)) {
            $tool->createSchema($meta);
        }

        // Crear un club para las pruebas
        $club = (new Club())
            ->setIdClub('TEST')
            ->setNombre('Test Club')
            ->setFundacion(2000)
            ->setCiudad('Test City')
            ->setEstadio('Test Stadium')
            ->setPresupuesto('50000000'); // 50M

        // Crear un jugador SIN club
        $playerWithoutClub = (new Player())
            ->setNombre('Juan')
            ->setApellidos('Sin Club')
            ->setDorsal(99)
            ->setSalario('1000000'); // 1M

        // Crear un jugador CON club
        $playerWithClub = (new Player())
            ->setNombre('Pedro')
            ->setApellidos('Con Club')
            ->setDorsal(10)
            ->setSalario('2000000') // 2M
            ->setClub($club);

        $this->em->persist($club);
        $this->em->persist($playerWithoutClub);
        $this->em->persist($playerWithClub);
        $this->em->flush();
    }

    public function testCreatePlayerWithoutClub(): void
    {
        // Arrange
        $playerData = [
            'nombre' => 'Nuevo',
            'apellidos' => 'Jugador Libre',
            'dorsal' => 15,
            'salario' => '1500000'
            // Sin id_club - jugador libre
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
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Player created successfully', $data['message']);
    }

    public function testUpdatePlayerWithoutClubToWithoutClub(): void
    {
        // Arrange - Buscar el jugador sin club
        $player = $this->em->getRepository(Player::class)->findOneBy(['nombre' => 'Juan']);
        $this->assertNotNull($player);
        $this->assertNull($player->getClub());

        $updateData = [
            'nombre' => 'Juan Carlos',
            'salario' => '1200000'
        ];

        // Act
        $this->client->request(
            'PUT',
            '/players/' . $player->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        // Assert
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Player updated successfully', $data['message']);
    }

    public function testUpdatePlayerWithoutClubAddClub(): void
    {
        // Arrange - Buscar el jugador sin club y el club
        $player = $this->em->getRepository(Player::class)->findOneBy(['nombre' => 'Juan']);
        $club = $this->em->getRepository(Club::class)->findOneBy(['id_club' => 'TEST']);
        
        $this->assertNotNull($player);
        $this->assertNotNull($club);
        $this->assertNull($player->getClub());

        $updateData = [
            'id_club' => 'TEST'
        ];

        // Act
        $this->client->request(
            'PUT',
            '/players/' . $player->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        // Assert
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Player updated successfully', $data['message']);
    }

    public function testUpdatePlayerWithClubRemoveClub(): void
    {
        // Arrange - Buscar el jugador con club
        $player = $this->em->getRepository(Player::class)->findOneBy(['nombre' => 'Pedro']);
        
        $this->assertNotNull($player);
        $this->assertNotNull($player->getClub());

        $updateData = [
            'id_club' => null // Quitar el club
        ];

        // Act
        $this->client->request(
            'PUT',
            '/players/' . $player->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        // Assert
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Player updated successfully', $data['message']);
    }

    public function testUpdatePlayerWithClubRemoveClubEmptyString(): void
    {
        // Arrange - Buscar el jugador con club
        $player = $this->em->getRepository(Player::class)->findOneBy(['nombre' => 'Pedro']);
        
        $this->assertNotNull($player);
        $this->assertNotNull($player->getClub());

        $updateData = [
            'id_club' => '' // Quitar el club con string vacío
        ];

        // Act
        $this->client->request(
            'PUT',
            '/players/' . $player->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        // Assert
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Player updated successfully', $data['message']);
    }

    public function testUpdatePlayerWithInvalidClub(): void
    {
        // Arrange - Buscar el jugador sin club
        $player = $this->em->getRepository(Player::class)->findOneBy(['nombre' => 'Juan']);
        
        $this->assertNotNull($player);

        $updateData = [
            'id_club' => 'DEFINITELY_NOT_EXISTS_12345' // Club que no existe
        ];

        // Act
        $this->client->request(
            'PUT',
            '/players/' . $player->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        // Assert
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('club', $data['error']);
        $this->assertEquals('Club not found', $data['error']['club']);
    }

    public function testUpdatePlayerDorsalWithoutClub(): void
    {
        // Arrange - Buscar el jugador sin club
        $player = $this->em->getRepository(Player::class)->findOneBy(['nombre' => 'Juan']);
        
        $this->assertNotNull($player);
        $this->assertNull($player->getClub());

        $updateData = [
            'dorsal' => 50 // Cambiar dorsal sin club
        ];

        // Act
        $this->client->request(
            'PUT',
            '/players/' . $player->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        // Assert
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Player updated successfully', $data['message']);
    }

    public function testUpdatePlayerSalaryWithoutClub(): void
    {
        // Arrange - Buscar el jugador sin club
        $player = $this->em->getRepository(Player::class)->findOneBy(['nombre' => 'Juan']);
        
        $this->assertNotNull($player);
        $this->assertNull($player->getClub());

        $updateData = [
            'salario' => '3000000' // Cambiar salario sin club
        ];

        // Act
        $this->client->request(
            'PUT',
            '/players/' . $player->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        // Assert
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Player updated successfully', $data['message']);
    }

    public function testUpdatePlayerBudgetValidationWithClub(): void
    {
        // Arrange - Buscar el jugador con club
        $player = $this->em->getRepository(Player::class)->findOneBy(['nombre' => 'Pedro']);
        
        $this->assertNotNull($player);
        $this->assertNotNull($player->getClub());

        $updateData = [
            'salario' => '60000000' // 60M - más que el presupuesto del club (50M)
        ];

        // Act
        $this->client->request(
            'PUT',
            '/players/' . $player->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        // Assert
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('presupuesto', $data['error']);
        $this->assertStringContainsString('presupuesto suficiente', $data['error']['presupuesto']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        if ($this->em) {
            $this->em->close();
        }
        
        // Restaurar el manejador de excepciones global para evitar warnings de pruebas riesgosas
        restore_exception_handler();
    }
}
