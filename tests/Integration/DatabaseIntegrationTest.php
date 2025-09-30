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
        $this->assertEquals('50000000', $savedClub->getPresupuesto());

        // Cleanup
        $this->entityManager->remove($savedClub);
        $this->entityManager->flush();
    }

    public function testPlayerPersistence(): void
    {
        // Arrange
        $player = new Player();
        $player->setNombre('Test');
        $player->setApellidos('Player');
        $player->setDorsal(99);
        $player->setSalario('1000000');

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
        $this->assertEquals('1000000', $savedPlayer->getSalario());

        // Cleanup
        $this->entityManager->remove($savedPlayer);
        $this->entityManager->flush();
    }

    public function testCoachPersistence(): void
    {
        // Arrange
        $coach = new Coach();
        $coach->setDni('TEST1234A');
        $coach->setNombre('Test');
        $coach->setApellidos('Coach');
        $coach->setSalario('2000000');

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
        $this->assertEquals('2000000', $savedCoach->getSalario());

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
        $club->setIdClub('COACH');
        $club->setNombre('Coach Club');
        $club->setFundacion(2024);
        $club->setCiudad('Test City');
        $club->setEstadio('Test Stadium');
        $club->setPresupuesto('100000000');

        $coach = new Coach();
        $coach->setDni('COACH1234A');
        $coach->setNombre('Relationship');
        $coach->setApellidos('Coach');
        $coach->setSalario('3000000');
        $coach->setClub($club);

        // Act
        $this->entityManager->persist($club);
        $this->entityManager->persist($coach);
        $this->entityManager->flush();
        $this->entityManager->clear();

        // Assert
        $savedClub = $this->entityManager->getRepository(Club::class)
            ->findOneBy(['id_club' => 'COACH']);
        
        $savedCoach = $this->entityManager->getRepository(Coach::class)
            ->findOneBy(['dni' => 'COACH1234A']);

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
        $club->setIdClub('BUDGET');
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
        $player->setClub($club);

        $coach = new Coach();
        $coach->setDni('BUDGET1234A');
        $coach->setNombre('Budget');
        $coach->setApellidos('Coach');
        $coach->setSalario('20000000');
        $coach->setClub($club);

        // Act
        $this->entityManager->persist($club);
        $this->entityManager->persist($player);
        $this->entityManager->persist($coach);
        $this->entityManager->flush();
        $this->entityManager->clear();

        // Assert
        $savedClub = $this->entityManager->getRepository(Club::class)
            ->findOneBy(['id_club' => 'BUDGET']);

        $this->assertNotNull($savedClub);
        $this->assertEquals(30000000.0, $savedClub->getGastoJugadores());
        $this->assertEquals(20000000.0, $savedClub->getGastosEntrenadores());
        $this->assertEquals(50000000.0, $savedClub->getPresupuestoRestante());

        // Cleanup
        $this->entityManager->remove($player);
        $this->entityManager->remove($coach);
        $this->entityManager->remove($savedClub);
        $this->entityManager->flush();
    }
}
