<?php

namespace App\Tests\Integration;

use App\Entity\Club;
use App\Entity\Player;
use App\Entity\Coach;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SimpleDatabaseTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

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

    public function testClubEntity(): void
    {
        // Arrange
        $club = new Club();
        $club->setIdClub('TEST' . uniqid());
        $club->setNombre('Test Club');
        $club->setFundacion(2024);
        $club->setCiudad('Test City');
        $club->setEstadio('Test Stadium');
        $club->setPresupuesto('50000000');

        // Act
        $this->entityManager->persist($club);
        $this->entityManager->flush();

        // Assert
        $this->assertNotNull($club->getId());
        $this->assertEquals('Test Club', $club->getNombre());
        $this->assertEquals(2024, $club->getFundacion());
        $this->assertEquals('50000000', $club->getPresupuesto());

        // Cleanup
        $this->entityManager->remove($club);
        $this->entityManager->flush();
    }

    public function testPlayerEntity(): void
    {
        // Arrange
        $player = new Player();
        $player->setNombre('Test' . uniqid());
        $player->setApellidos('Player');
        $player->setDorsal(99);
        $player->setSalario('1000000');

        // Act
        $this->entityManager->persist($player);
        $this->entityManager->flush();

        // Assert
        $this->assertNotNull($player->getId());
        $this->assertEquals('Test' . substr($player->getNombre(), 4), $player->getNombre());
        $this->assertEquals('Player', $player->getApellidos());
        $this->assertEquals(99, $player->getDorsal());
        $this->assertEquals('1000000', $player->getSalario());

        // Cleanup
        $this->entityManager->remove($player);
        $this->entityManager->flush();
    }

    public function testCoachEntity(): void
    {
        // Arrange
        $coach = new Coach();
        $coach->setDni('TEST' . rand(1000, 9999) . chr(rand(65, 90)));
        $coach->setNombre('Test' . uniqid());
        $coach->setApellidos('Coach');
        $coach->setSalario('2000000');

        // Act
        $this->entityManager->persist($coach);
        $this->entityManager->flush();

        // Assert
        $this->assertNotNull($coach->getId());
        $this->assertStringStartsWith('TEST', $coach->getDni());
        $this->assertStringStartsWith('Test', $coach->getNombre());
        $this->assertEquals('Coach', $coach->getApellidos());
        $this->assertEquals('2000000', $coach->getSalario());

        // Cleanup
        $this->entityManager->remove($coach);
        $this->entityManager->flush();
    }

    public function testClubBudgetCalculation(): void
    {
        // Arrange
        $club = new Club();
        $club->setIdClub('BUDGET' . uniqid());
        $club->setNombre('Budget Test Club');
        $club->setFundacion(2024);
        $club->setCiudad('Test City');
        $club->setEstadio('Test Stadium');
        $club->setPresupuesto('10000000'); // 10M

        $player = new Player();
        $player->setNombre('Budget' . uniqid());
        $player->setApellidos('Player');
        $player->setDorsal(1);
        $player->setSalario('3000000'); // 3M
        $player->setClub($club);

        $coach = new Coach();
        $coach->setDni('BUDGET' . rand(1000, 9999) . chr(rand(65, 90)));
        $coach->setNombre('Budget' . uniqid());
        $coach->setApellidos('Coach');
        $coach->setSalario('2000000'); // 2M
        $coach->setClub($club);

        // Act
        $this->entityManager->persist($club);
        $this->entityManager->persist($player);
        $this->entityManager->persist($coach);
        $this->entityManager->flush();

        // Assert
        $this->assertEquals(3000000.0, $club->getGastoJugadores());
        $this->assertEquals(2000000.0, $club->getGastosEntrenadores());
        $this->assertEquals(5000000.0, $club->getPresupuestoRestante());

        // Cleanup
        $this->entityManager->remove($player);
        $this->entityManager->remove($coach);
        $this->entityManager->remove($club);
        $this->entityManager->flush();
    }
}
