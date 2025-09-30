<?php

namespace App\Tests\Entity;

use App\Entity\Player;
use App\Entity\Club;
use PHPUnit\Framework\TestCase;

class PlayerTest extends TestCase
{
    public function testPlayerCreation(): void
    {
        // Arrange (Preparar)
        $player = new Player();
        
        // Act (Actuar)
        $player->setNombre('Lionel');
        $player->setApellidos('Messi');
        $player->setDorsal(10);
        $player->setSalario('50000000');
        
        // Assert (Verificar)
        $this->assertEquals('Lionel', $player->getNombre());
        $this->assertEquals('Messi', $player->getApellidos());
        $this->assertEquals(10, $player->getDorsal());
        $this->assertEquals('50000000', $player->getSalario());
    }

    public function testPlayerWithClub(): void
    {
        // Arrange
        $player = new Player();
        $club = new Club();
        
        // Act
        $club->setNombre('FC Barcelona');
        $player->setNombre('Lionel');
        $player->setApellidos('Messi');
        $player->setClub($club);
        
        // Assert
        $this->assertEquals('FC Barcelona', $player->getClub()->getNombre());
        // Nota: La relación bidireccional se establece automáticamente en la entidad Club
        // cuando se llama a addPlayer(), pero aquí solo estamos probando la relación directa
        $this->assertNotNull($player->getClub());
    }

    public function testPlayerWithoutClub(): void
    {
        // Arrange
        $player = new Player();
        
        // Act
        $player->setNombre('Jugador');
        $player->setApellidos('Libre');
        $player->setClub(null);
        
        // Assert
        $this->assertNull($player->getClub());
    }

    public function testPlayerGettersAndSetters(): void
    {
        $player = new Player();
        
        // Test ID (debería ser null inicialmente)
        $this->assertNull($player->getId());
        
        // Test nombre
        $player->setNombre('Cristiano');
        $this->assertEquals('Cristiano', $player->getNombre());
        
        // Test apellidos
        $player->setApellidos('Ronaldo');
        $this->assertEquals('Ronaldo', $player->getApellidos());
        
        // Test dorsal
        $player->setDorsal(7);
        $this->assertEquals(7, $player->getDorsal());
        
        // Test salario
        $player->setSalario('40000000');
        $this->assertEquals('40000000', $player->getSalario());
    }
}