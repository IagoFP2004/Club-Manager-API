<?php

namespace App\Tests\Integration;

use App\Entity\Club;
use App\Entity\Player;
use App\Entity\Coach;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DatabaseIntegrationTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testClubPersistence(): void
    {
        // Arrange
        $club = new Club();
        $club->setIdClub('TEST');
        $club->setNombre('Test Club');
        $club->setFundacion(2024);
        $club->setCiudad('Test City');
        $club->setEstadio('Test Stadium');
        $club->setPresupuesto('50000000');

        // Act
        $this->entityManager->persist($club);
        $this->entityManager->flush();
        $this->entityManager->clear();

        // Assert
        $savedClub = $this->entityManager->getRepository(Club::class)
            ->findOneBy(['id_club' => 'TEST']);
        
        $this->assertNotNull($savedClub);
        $this->assertEquals('Test Club', $savedClub->getNombre());
        $this->assertEquals(2024, $savedClub->getFundacion());
        $this->assertEquals('50000000.00', $savedClub->getPresupuesto());

        // Cleanup
        $this->entityManager->remove($savedClub);
        $this->entityManager->flush();
    }

    public function testPlayerPersistence(): void
    {
        // Arrange - Create club first
        $club = new Club();
        $club->setIdClub('T' . time() . rand(100, 999));
        $club->setNombre('Test Club');
        $club->setFundacion(2024);
        $club->setCiudad('Test City');
        $club->setEstadio('Test Stadium');
        $club->setPresupuesto('100000000');
        
        $this->entityManager->persist($club);
        $this->entityManager->flush();
        
        $player = new Player();
        $player->setNombre('Test');
        $player->setApellidos('Player');
        $player->setDorsal(99);
        $player->setSalario('1000000');
        $player->setClub($club);

        // Act
        $this->entityManager->persist($player);
        $this->entityManager->flush();
        $this->entityManager->clear();

        // Assert
        $savedPlayer = $this->entityManager->getRepository(Player::class)
            ->findOneBy(['nombre' => 'Test', 'apellidos' => 'Player']);
        
        $this->assertNotNull($savedPlayer);
        $this->assertEquals('Test', $savedPlayer->getNombre());
        $this->assertEquals('Player', $savedPlayer->getApellidos());
        $this->assertEquals(99, $savedPlayer->getDorsal());
        $this->assertEquals('1000000.00', $savedPlayer->getSalario());

        // Cleanup
        $this->entityManager->remove($savedPlayer);
        $this->entityManager->flush();
    }

    public function testCoachPersistence(): void
    {
        // Arrange - Create club first
        $club = new Club();
        $club->setIdClub('T2' . time() . rand(100, 999));
        $club->setNombre('Test Club 2');
        $club->setFundacion(2024);
        $club->setCiudad('Test City 2');
        $club->setEstadio('Test Stadium 2');
        $club->setPresupuesto('100000000');
        
        $this->entityManager->persist($club);
        $this->entityManager->flush();
        
        $coach = new Coach();
        $coach->setDni('TEST1234A');
        $coach->setNombre('Test');
        $coach->setApellidos('Coach');
        $coach->setSalario('2000000');
        $coach->setClub($club);

        // Act
        $this->entityManager->persist($coach);
        $this->entityManager->flush();
        $this->entityManager->clear();

        // Assert
        $savedCoach = $this->entityManager->getRepository(Coach::class)
            ->findOneBy(['dni' => 'TEST1234A']);
        
        $this->assertNotNull($savedCoach);
        $this->assertEquals('Test', $savedCoach->getNombre());
        $this->assertEquals('Coach', $savedCoach->getApellidos());
        $this->assertEquals('2000000.00', $savedCoach->getSalario());

        // Cleanup
        $this->entityManager->remove($savedCoach);
        $this->entityManager->flush();
    }

    public function testClubPlayerRelationship(): void
    {
        // Arrange
        $club = new Club();
        $club->setIdClub('REL');
        $club->setNombre('Relationship Club');
        $club->setFundacion(2024);
        $club->setCiudad('Test City');
        $club->setEstadio('Test Stadium');
        $club->setPresupuesto('100000000');

        $player = new Player();
        $player->setNombre('Relationship');
        $player->setApellidos('Player');
        $player->setDorsal(1);
        $player->setSalario('5000000');
        $player->setClub($club);

        // Act
        $this->entityManager->persist($club);
        $this->entityManager->persist($player);
        $this->entityManager->flush();
        $this->entityManager->clear();

        // Assert
        $savedClub = $this->entityManager->getRepository(Club::class)
            ->findOneBy(['id_club' => 'REL']);
        
        $savedPlayer = $this->entityManager->getRepository(Player::class)
            ->findOneBy(['nombre' => 'Relationship', 'apellidos' => 'Player']);

        $this->assertNotNull($savedClub);
        $this->assertNotNull($savedPlayer);
        $this->assertEquals($savedClub->getId(), $savedPlayer->getClub()->getId());
        $this->assertTrue($savedClub->getPlayers()->contains($savedPlayer));

        // Cleanup
        $this->entityManager->remove($savedPlayer);
        $this->entityManager->remove($savedClub);
        $this->entityManager->flush();
    }

    public function testClubCoachRelationship(): void
    {
        // Arrange
        $club = new Club();
        $club->setIdClub('REL' . uniqid());
        $club->setNombre('Coach Club');
        $club->setFundacion(2024);
        $club->setCiudad('Test City');
        $club->setEstadio('Test Stadium');
        $club->setPresupuesto('100000000');

        $coach = new Coach();
        $coach->setDni('REL' . rand(100000, 999999) . 'A');
        $coach->setNombre('Relationship');
        $coach->setApellidos('Coach');
        $coach->setSalario('3000000');
        $club->addCoach($coach);

        // Act
        $this->entityManager->persist($club);
        $this->entityManager->persist($coach);
        $this->entityManager->flush();
        $this->entityManager->clear();

        // Assert
        $savedClub = $this->entityManager->getRepository(Club::class)
            ->findOneBy(['nombre' => 'Coach Club']);
        
        $savedCoach = $this->entityManager->getRepository(Coach::class)
            ->findOneBy(['nombre' => 'Relationship', 'apellidos' => 'Coach']);

        $this->assertNotNull($savedClub);
        $this->assertNotNull($savedCoach);
        $this->assertEquals($savedClub->getId(), $savedCoach->getClub()->getId());
        $this->assertTrue($savedClub->getCoaches()->contains($savedCoach));

        // Cleanup
        $this->entityManager->remove($savedCoach);
        $this->entityManager->remove($savedClub);
        $this->entityManager->flush();
    }

    public function testClubBudgetCalculation(): void
    {
        // Arrange
        $club = new Club();
        $club->setIdClub('CALC' . uniqid());
        $club->setNombre('Budget Club');
        $club->setFundacion(2024);
        $club->setCiudad('Test City');
        $club->setEstadio('Test Stadium');
        $club->setPresupuesto('100000000');

        $player = new Player();
        $player->setNombre('Budget');
        $player->setApellidos('Player');
        $player->setDorsal(1);
        $player->setSalario('30000000');
        $club->addPlayer($player);

        $coach = new Coach();
        $coach->setDni('987654321B');
        $coach->setNombre('Budget');
        $coach->setApellidos('Coach');
        $coach->setSalario('20000000');
        $club->addCoach($coach);

        // Act
        $this->entityManager->persist($club);
        $this->entityManager->persist($player);
        $this->entityManager->persist($coach);
        $this->entityManager->flush();
        $this->entityManager->clear();

        // Assert
        $savedClub = $this->entityManager->getRepository(Club::class)
            ->findOneBy(['nombre' => 'Budget Club']);

        $this->assertNotNull($savedClub);
        $this->assertEquals(30000000.0, $savedClub->getGastoJugadores());
        $this->assertEquals(20000000.0, $savedClub->getGastosEntrenadores());
        $this->assertEquals(50000000.0, $savedClub->getPresupuestoRestante());

        // Cleanup - No es necesario, los tests se ejecutan en transacciones separadas
    }
}
