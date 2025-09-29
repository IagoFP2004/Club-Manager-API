<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250929152344 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('DROP INDEX uniq_club_id_club ON club');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B8EE387233CE2470 ON club (id_club)');
        $this->addSql('DROP INDEX UNIQ_coach_dni ON coach');
        $this->addSql('ALTER TABLE coach DROP FOREIGN KEY FK_coach_club');
        $this->addSql('ALTER TABLE coach CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('DROP INDEX idx_coach_club ON coach');
        $this->addSql('CREATE INDEX IDX_3F596DCC61190A32 ON coach (club_id)');
        $this->addSql('ALTER TABLE coach ADD CONSTRAINT FK_coach_club FOREIGN KEY (club_id) REFERENCES club (id)');
        $this->addSql('DROP INDEX UNIQ_player_dorsal_club ON player');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_player_club');
        $this->addSql('ALTER TABLE player CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('DROP INDEX idx_player_club ON player');
        $this->addSql('CREATE INDEX IDX_98197A6561190A32 ON player (club_id)');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_player_club FOREIGN KEY (club_id) REFERENCES club (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('DROP INDEX uniq_b8ee387233ce2470 ON club');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_club_id_club ON club (id_club)');
        $this->addSql('ALTER TABLE coach DROP FOREIGN KEY FK_3F596DCC61190A32');
        $this->addSql('ALTER TABLE coach CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_coach_dni ON coach (dni)');
        $this->addSql('DROP INDEX idx_3f596dcc61190a32 ON coach');
        $this->addSql('CREATE INDEX IDX_coach_club ON coach (club_id)');
        $this->addSql('ALTER TABLE coach ADD CONSTRAINT FK_3F596DCC61190A32 FOREIGN KEY (club_id) REFERENCES club (id)');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A6561190A32');
        $this->addSql('ALTER TABLE player CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_player_dorsal_club ON player (dorsal, club_id)');
        $this->addSql('DROP INDEX idx_98197a6561190a32 ON player');
        $this->addSql('CREATE INDEX IDX_player_club ON player (club_id)');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A6561190A32 FOREIGN KEY (club_id) REFERENCES club (id)');
    }
}
