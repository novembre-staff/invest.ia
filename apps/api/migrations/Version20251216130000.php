<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour créer la table proposals
 * UC-031, UC-032, UC-033: Gérer les propositions d'investissement
 */
final class Version20251216130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create proposals table for bot investment proposals';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE proposals (
                id VARCHAR(36) NOT NULL PRIMARY KEY,
                user_id VARCHAR(36) NOT NULL,
                strategy_id VARCHAR(36) NOT NULL,
                symbol VARCHAR(20) NOT NULL,
                side VARCHAR(10) NOT NULL,
                quantity DECIMAL(20, 8) NOT NULL,
                limit_price DECIMAL(20, 8) DEFAULT NULL,
                status VARCHAR(20) NOT NULL,
                rationale TEXT NOT NULL,
                risk_factors JSON NOT NULL,
                risk_score VARCHAR(10) NOT NULL,
                expected_return VARCHAR(20) DEFAULT NULL,
                stop_loss DECIMAL(20, 8) DEFAULT NULL,
                take_profit DECIMAL(20, 8) DEFAULT NULL,
                created_at TIMESTAMP NOT NULL,
                expires_at TIMESTAMP NOT NULL,
                responded_at TIMESTAMP DEFAULT NULL,
                order_id VARCHAR(36) DEFAULT NULL,
                INDEX idx_proposal_user_id (user_id),
                INDEX idx_proposal_strategy_id (strategy_id),
                INDEX idx_proposal_status (status),
                INDEX idx_proposal_expires_at (expires_at),
                INDEX idx_proposal_created_at (created_at)
            )
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE proposals');
    }
}
