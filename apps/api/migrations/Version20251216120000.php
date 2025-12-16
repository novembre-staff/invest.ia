<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Exchange connections table migration
 */
final class Version20251216120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create exchange_connections table for storing user exchange API connections';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE exchange_connections (
                id UUID PRIMARY KEY,
                user_id UUID NOT NULL,
                exchange_name VARCHAR(50) NOT NULL,
                api_key TEXT NOT NULL,
                api_secret TEXT NOT NULL,
                label VARCHAR(255),
                is_active BOOLEAN NOT NULL DEFAULT TRUE,
                connected_at TIMESTAMP NOT NULL,
                last_sync_at TIMESTAMP,
                CONSTRAINT fk_exchange_connections_user_id FOREIGN KEY (user_id) 
                    REFERENCES users(id) ON DELETE CASCADE
            )
        ');

        $this->addSql('CREATE INDEX idx_exchange_connections_user_id ON exchange_connections(user_id)');
        $this->addSql('CREATE INDEX idx_exchange_connections_exchange_name ON exchange_connections(exchange_name)');
        $this->addSql('CREATE UNIQUE INDEX idx_exchange_connections_user_exchange ON exchange_connections(user_id, exchange_name) WHERE is_active = TRUE');

        $this->addSql('COMMENT ON TABLE exchange_connections IS \'User connections to cryptocurrency exchanges\'');
        $this->addSql('COMMENT ON COLUMN exchange_connections.api_key IS \'Encrypted API key\'');
        $this->addSql('COMMENT ON COLUMN exchange_connections.api_secret IS \'Encrypted API secret\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE exchange_connections');
    }
}
