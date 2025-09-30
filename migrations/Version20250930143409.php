<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250930143409 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow NULL values for id_club in player and coach tables';
    }

    public function up(Schema $schema): void
    {
        // Modificar tabla player para permitir NULL en id_club
        $this->addSql('ALTER TABLE player MODIFY id_club INT DEFAULT NULL');
        
        // Modificar tabla coach para permitir NULL en id_club  
        $this->addSql('ALTER TABLE coach MODIFY id_club INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Revertir cambios - hacer las columnas NOT NULL nuevamente
        $this->addSql('ALTER TABLE player MODIFY id_club INT NOT NULL');
        $this->addSql('ALTER TABLE coach MODIFY id_club INT NOT NULL');
    }
}
