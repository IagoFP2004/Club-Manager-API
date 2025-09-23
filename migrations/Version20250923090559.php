<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250923090559 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Solo agregar la relación, la tabla club ya existe
        $this->addSql('ALTER TABLE player CHANGE id_club id_club INT DEFAULT NULL');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A6533CE2470 FOREIGN KEY (id_club) REFERENCES club (id)');
        $this->addSql('CREATE INDEX IDX_98197A6533CE2470 ON player (id_club)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A6533CE2470');
        $this->addSql('DROP INDEX IDX_98197A6533CE2470 ON player');
        $this->addSql('ALTER TABLE player CHANGE id_club id_club VARCHAR(5) NOT NULL');
    }
}
