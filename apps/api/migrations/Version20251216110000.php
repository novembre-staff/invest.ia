<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251216110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add preferences column to users table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE users 
            ADD COLUMN preferences JSONB DEFAULT \'{"reportingCurrency":"USD","timezone":"UTC","language":"en","emailNotifications":true,"pushNotifications":true,"tradingAlerts":true,"newsAlerts":true,"theme":"auto","soundEnabled":true}\'::jsonb
        ');
        
        $this->addSql('
            COMMENT ON COLUMN users.preferences IS \'User preferences stored as JSON\'
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP COLUMN preferences');
    }
}
