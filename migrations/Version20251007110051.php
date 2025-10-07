<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251007110051 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club RENAME INDEX uniq_club_id_club TO UNIQ_B8EE387233CE2470');
        $this->addSql('ALTER TABLE coach RENAME INDEX idx_coach_club TO IDX_3F596DCC33CE2470');
        $this->addSql('ALTER TABLE player RENAME INDEX idx_player_club TO IDX_98197A6533CE2470');
        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(180) NOT NULL');
        $this->addSql('ALTER TABLE user RENAME INDEX email TO UNIQ_8D93D649E7927C74');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` CHANGE email email VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE `user` RENAME INDEX uniq_8d93d649e7927c74 TO email');
        $this->addSql('ALTER TABLE club RENAME INDEX uniq_b8ee387233ce2470 TO UNIQ_club_id_club');
        $this->addSql('ALTER TABLE player RENAME INDEX idx_98197a6533ce2470 TO IDX_player_club');
        $this->addSql('ALTER TABLE coach RENAME INDEX idx_3f596dcc33ce2470 TO IDX_coach_club');
    }
}
