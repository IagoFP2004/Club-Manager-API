<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250926120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Agregar campo presupuesto_restante a la tabla club';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club ADD presupuesto_restante DECIMAL(15,2) DEFAULT NULL');
        
        // Inicializar el presupuesto restante con el presupuesto total para clubs existentes
        $this->addSql('UPDATE club SET presupuesto_restante = presupuesto WHERE presupuesto_restante IS NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club DROP presupuesto_restante');
    }
}
