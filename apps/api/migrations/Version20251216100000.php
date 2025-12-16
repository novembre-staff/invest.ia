<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251216100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create users table for Identity bounded context';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE users (
                id UUID PRIMARY KEY,
                email VARCHAR(255) NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                status VARCHAR(50) NOT NULL DEFAULT \'pending_verification\',
                mfa_enabled BOOLEAN NOT NULL DEFAULT FALSE,
                mfa_secret VARCHAR(255),
                email_verified_at TIMESTAMP,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT unique_email UNIQUE (email)
            )
        ');
        
        $this->addSql('CREATE INDEX idx_user_email ON users (email)');
        $this->addSql('CREATE INDEX idx_user_status ON users (status)');
        $this->addSql('CREATE INDEX idx_user_created_at ON users (created_at)');
        
        $this->addSql('
            COMMENT ON TABLE users IS \'User accounts - Identity bounded context\'
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE users');
    }
}
