<?php

declare(strict_types=1);

namespace App\Tests\Audit\Domain\ValueObject;

use App\Audit\Domain\ValueObject\AuditAction;
use PHPUnit\Framework\TestCase;

class AuditActionTest extends TestCase
{
    public function testCriticalActions(): void
    {
        $this->assertTrue(AuditAction::RISK_LIMIT_BREACHED->isCritical());
        $this->assertTrue(AuditAction::EMERGENCY_STOP_TRIGGERED->isCritical());
        $this->assertTrue(AuditAction::API_KEY_EXPOSED->isCritical());
        $this->assertFalse(AuditAction::USER_LOGGED_IN->isCritical());
    }

    public function testSecurityRelatedActions(): void
    {
        $this->assertTrue(AuditAction::USER_LOGGED_IN->isSecurityRelated());
        $this->assertTrue(AuditAction::USER_MFA_ENABLED->isSecurityRelated());
        $this->assertTrue(AuditAction::UNAUTHORIZED_ACCESS_ATTEMPT->isSecurityRelated());
        $this->assertFalse(AuditAction::BOT_CREATED->isSecurityRelated());
    }
}
