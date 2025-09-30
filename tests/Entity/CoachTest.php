<?php

namespace App\Tests\Entity;

use App\Entity\Coach;
use App\Entity\Club;
use PHPUnit\Framework\TestCase;

class CoachTest extends TestCase
{
    public function testCoachCreation(): void
    {
        // Arrange
        $coach = new Coach();
        
        // Act
        $coach->setDni('12345678A');
        $coach->setNombre('Pep');
        $coach->setApellidos('Guardiola');
        $coach->setSalario('20000000');
        
        // Assert
        $this->assertEquals('12345678A', $coach->getDni());
        $this->assertEquals('Pep', $coach->getNombre());
        $this->assertEquals('Guardiola', $coach->getApellidos());
        $this->assertEquals('20000000', $coach->getSalario());
    }

    public function testCoachWithClub(): void
    {
        // Arrange
        $coach = new Coach();
        $club = new Club();
        
        // Act
        $club->setNombre('Manchester City');
        $coach->setNombre('Pep');
        $coach->setApellidos('Guardiola');
        $coach->setClub($club);
        
        // Assert
        $this->assertEquals('Manchester City', $coach->getClub()->getNombre());
        // Nota: La relación bidireccional se establece automáticamente en la entidad Club
        // cuando se llama a addCoach(), pero aquí solo estamos probando la relación directa
        $this->assertNotNull($coach->getClub());
    }

    public function testCoachWithoutClub(): void
    {
        // Arrange
        $coach = new Coach();
        
        // Act
        $coach->setNombre('Entrenador');
        $coach->setApellidos('Libre');
        $coach->setClub(null);
        
        // Assert
        $this->assertNull($coach->getClub());
    }

    public function testCoachGettersAndSetters(): void
    {
        $coach = new Coach();
        
        // Test ID (debería ser null inicialmente)
        $this->assertNull($coach->getId());
        
        // Test DNI
        $coach->setDni('87654321B');
        $this->assertEquals('87654321B', $coach->getDni());
        
        // Test nombre
        $coach->setNombre('Carlo');
        $this->assertEquals('Carlo', $coach->getNombre());
        
        // Test apellidos
        $coach->setApellidos('Ancelotti');
        $this->assertEquals('Ancelotti', $coach->getApellidos());
        
        // Test salario
        $coach->setSalario('15000000');
        $this->assertEquals('15000000', $coach->getSalario());
    }

    public function testToString(): void
    {
        // Arrange
        $coach = new Coach();
        
        // Act
        $coach->setNombre('Diego');
        $coach->setApellidos('Simeone');
        
        // Assert
        $this->assertEquals('Diego Simeone', (string)$coach);
    }
}
