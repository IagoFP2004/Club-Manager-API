<?php

namespace App\Tests\Entity;

use App\Entity\Club;
use App\Entity\Player;
use App\Entity\Coach;
use PHPUnit\Framework\TestCase;

class ClubTest extends TestCase
{
    public function testClubCreation(): void
    {
        // Arrange
        $club = new Club();
        
        // Act
        $club->setIdClub('RM');
        $club->setNombre('Real Madrid');
        $club->setFundacion(1902);
        $club->setCiudad('Madrid');
        $club->setEstadio('Santiago Bernabéu');
        $club->setPresupuesto('800000000');
        
        // Assert
        $this->assertEquals('RM', $club->getIdClub());
        $this->assertEquals('Real Madrid', $club->getNombre());
        $this->assertEquals(1902, $club->getFundacion());
        $this->assertEquals('Madrid', $club->getCiudad());
        $this->assertEquals('Santiago Bernabéu', $club->getEstadio());
        $this->assertEquals('800000000', $club->getPresupuesto());
    }

    public function testClubWithPlayers(): void
    {
        // Arrange
        $club = new Club();
        $player1 = new Player();
        $player2 = new Player();
        
        // Act
        $club->setNombre('FC Barcelona');
        $player1->setNombre('Lionel');
        $player1->setApellidos('Messi');
        $player1->setSalario('50000000');
        
        $player2->setNombre('Pedri');
        $player2->setApellidos('González');
        $player2->setSalario('15000000');
        
        $club->addPlayer($player1);
        $club->addPlayer($player2);
        
        // Assert
        $this->assertCount(2, $club->getPlayers());
        $this->assertTrue($club->getPlayers()->contains($player1));
        $this->assertTrue($club->getPlayers()->contains($player2));
        // Verificar que la relación bidireccional se estableció
        $this->assertEquals($club, $player1->getClub());
        $this->assertEquals($club, $player2->getClub());
    }

    public function testClubWithCoach(): void
    {
        // Arrange
        $club = new Club();
        $coach = new Coach();
        
        // Act
        $club->setNombre('Atlético de Madrid');
        $coach->setNombre('Diego');
        $coach->setApellidos('Simeone');
        $coach->setSalario('30000000');
        
        $club->addCoach($coach);
        
        // Assert
        $this->assertCount(1, $club->getCoaches());
        $this->assertTrue($club->getCoaches()->contains($coach));
        // Verificar que la relación bidireccional se estableció
        $this->assertEquals($club, $coach->getClub());
    }

    public function testGastoJugadores(): void
    {
        // Arrange
        $club = new Club();
        $player1 = new Player();
        $player2 = new Player();
        
        // Act
        $club->setPresupuesto('100000000');
        $player1->setSalario('30000000');
        $player2->setSalario('20000000');
        
        $club->addPlayer($player1);
        $club->addPlayer($player2);
        
        // Assert
        $this->assertEquals(50000000.0, $club->getGastoJugadores());
    }

    public function testGastosEntrenadores(): void
    {
        // Arrange
        $club = new Club();
        $coach = new Coach();
        
        // Act
        $club->setPresupuesto('100000000');
        $coach->setSalario('25000000');
        
        $club->addCoach($coach);
        
        // Assert
        $this->assertEquals(25000000.0, $club->getGastosEntrenadores());
    }

    public function testPresupuestoRestante(): void
    {
        // Arrange
        $club = new Club();
        $player = new Player();
        $coach = new Coach();
        
        // Act
        $club->setPresupuesto('100000000');
        $player->setSalario('40000000');
        $coach->setSalario('20000000');
        
        $club->addPlayer($player);
        $club->addCoach($coach);
        
        // Assert
        $this->assertEquals(40000000.0, $club->getPresupuestoRestante());
    }

    public function testRemovePlayer(): void
    {
        // Arrange
        $club = new Club();
        $player = new Player();
        
        // Act
        $club->addPlayer($player);
        $this->assertCount(1, $club->getPlayers());
        
        $club->removePlayer($player);
        
        // Assert
        $this->assertCount(0, $club->getPlayers());
        $this->assertFalse($club->getPlayers()->contains($player));
    }

    public function testRemoveCoach(): void
    {
        // Arrange
        $club = new Club();
        $coach = new Coach();
        
        // Act
        $club->addCoach($coach);
        $this->assertCount(1, $club->getCoaches());
        
        $club->removeCoach($coach);
        
        // Assert
        $this->assertCount(0, $club->getCoaches());
        $this->assertFalse($club->getCoaches()->contains($coach));
    }

    public function testValidarNuevoPresupuestoSuficiente(): void
    {
        // Arrange
        $club = new Club();
        $player = new Player();
        $coach = new Coach();
        
        $player->setSalario('40000000'); // 40M
        $coach->setSalario('20000000');  // 20M
        // Total gastos: 60M
        
        $club->addPlayer($player);
        $club->addCoach($coach);
        
        // Act & Assert
        // Presupuesto de 70M debería ser suficiente para gastos de 60M
        $this->assertTrue($club->validarNuevoPresupuesto(70000000.0));
        
        // Presupuesto de 60M debería ser exactamente suficiente
        $this->assertTrue($club->validarNuevoPresupuesto(60000000.0));
    }

    public function testValidarNuevoPresupuestoInsuficiente(): void
    {
        // Arrange
        $club = new Club();
        $player = new Player();
        $coach = new Coach();
        
        $player->setSalario('40000000'); // 40M
        $coach->setSalario('20000000');  // 20M
        // Total gastos: 60M
        
        $club->addPlayer($player);
        $club->addCoach($coach);
        
        // Act & Assert
        // Presupuesto de 50M no debería ser suficiente para gastos de 60M
        $this->assertFalse($club->validarNuevoPresupuesto(50000000.0));
        
        // Presupuesto de 59.99M no debería ser suficiente
        $this->assertFalse($club->validarNuevoPresupuesto(59990000.0));
    }

    public function testValidarNuevoPresupuestoSinGastos(): void
    {
        // Arrange
        $club = new Club();
        // Club sin jugadores ni entrenadores (gastos = 0)
        
        // Act & Assert
        // Cualquier presupuesto positivo debería ser válido
        $this->assertTrue($club->validarNuevoPresupuesto(1000000.0));
        $this->assertTrue($club->validarNuevoPresupuesto(0.0));
    }

    public function testValidarNuevoPresupuestoSoloJugadores(): void
    {
        // Arrange
        $club = new Club();
        $player1 = new Player();
        $player2 = new Player();
        
        $player1->setSalario('30000000'); // 30M
        $player2->setSalario('25000000'); // 25M
        // Total gastos: 55M (solo jugadores, sin entrenadores)
        
        $club->addPlayer($player1);
        $club->addPlayer($player2);
        
        // Act & Assert
        $this->assertTrue($club->validarNuevoPresupuesto(60000000.0));
        $this->assertTrue($club->validarNuevoPresupuesto(55000000.0));
        $this->assertFalse($club->validarNuevoPresupuesto(50000000.0));
    }

    public function testValidarNuevoPresupuestoSoloEntrenadores(): void
    {
        // Arrange
        $club = new Club();
        $coach = new Coach();
        
        $coach->setSalario('15000000'); // 15M
        // Total gastos: 15M (solo entrenadores, sin jugadores)
        
        $club->addCoach($coach);
        
        // Act & Assert
        $this->assertTrue($club->validarNuevoPresupuesto(20000000.0));
        $this->assertTrue($club->validarNuevoPresupuesto(15000000.0));
        $this->assertFalse($club->validarNuevoPresupuesto(10000000.0));
    }
}
