<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250930071409 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX id ON club');
        $this->addSql('DROP INDEX `primary` ON club');
        $this->addSql('ALTER TABLE club CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B8EE387233CE2470 ON club (id_club)');
        $this->addSql('ALTER TABLE club ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE coach DROP FOREIGN KEY FK_coach_club');
        $this->addSql('ALTER TABLE coach CHANGE salario salario NUMERIC(10, 2) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('DROP INDEX idx_coach_club ON coach');
        $this->addSql('CREATE INDEX IDX_3F596DCC33CE2470 ON coach (id_club)');
        $this->addSql('ALTER TABLE coach ADD CONSTRAINT FK_coach_club FOREIGN KEY (id_club) REFERENCES club (id_club)');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_player_club');
        $this->addSql('DROP INDEX IDX_player_club ON player');
        $this->addSql('ALTER TABLE player ADD club_id INT NOT NULL, DROP id_club, CHANGE salario salario NUMERIC(10, 2) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A6561190A32 FOREIGN KEY (club_id) REFERENCES club (id)');
        $this->addSql('CREATE INDEX IDX_98197A6561190A32 ON player (club_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX UNIQ_B8EE387233CE2470 ON club');
        $this->addSql('DROP INDEX `PRIMARY` ON club');
        $this->addSql('ALTER TABLE club CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX id ON club (id)');
        $this->addSql('ALTER TABLE club ADD PRIMARY KEY (id_club)');
        $this->addSql('ALTER TABLE coach DROP FOREIGN KEY FK_3F596DCC33CE2470');
        $this->addSql('ALTER TABLE coach CHANGE salario salario NUMERIC(10, 0) NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('DROP INDEX idx_3f596dcc33ce2470 ON coach');
        $this->addSql('CREATE INDEX IDX_coach_club ON coach (id_club)');
        $this->addSql('ALTER TABLE coach ADD CONSTRAINT FK_3F596DCC33CE2470 FOREIGN KEY (id_club) REFERENCES club (id_club)');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A6561190A32');
        $this->addSql('DROP INDEX IDX_98197A6561190A32 ON player');
        $this->addSql('ALTER TABLE player ADD id_club VARCHAR(5) DEFAULT NULL, DROP club_id, CHANGE salario salario NUMERIC(10, 0) NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_player_club FOREIGN KEY (id_club) REFERENCES club (id_club)');
        $this->addSql('CREATE INDEX IDX_player_club ON player (id_club)');
    }
}
