<?php

declare(strict_types=1);

namespace App\Tests\Audit\Domain\Model;

use App\Audit\Domain\Model\AuditLog;
use App\Audit\Domain\ValueObject\AuditAction;
use App\Audit\Domain\ValueObject\AuditSeverity;
use App\Identity\Domain\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

class AuditLogTest extends TestCase
{
    public function testCreateAuditLog(): void
    {
        $userId = UserId::generate();
        $action = AuditAction::USER_LOGGED_IN;
        $entityType = 'User';
        $entityId = $userId->getValue();

        $auditLog = AuditLog::create(
            userId: $userId,
            action: $action,
            entityType: $entityType,
            entityId: $entityId,
            severity: AuditSeverity::INFO,
            metadata: ['ip' => '127.0.0.1'],
            ipAddress: '127.0.0.1',
            userAgent: 'Mozilla/5.0'
        );

        $this->assertEquals($userId, $auditLog->getUserId());
        $this->assertEquals($action, $auditLog->getAction());
        $this->assertEquals($entityType, $auditLog->getEntityType());
        $this->assertEquals($entityId, $auditLog->getEntityId());
        $this->assertEquals(AuditSeverity::INFO, $auditLog->getSeverity());
        $this->assertEquals(['ip' => '127.0.0.1'], $auditLog->getMetadata());
        $this->assertEquals('127.0.0.1', $auditLog->getIpAddress());
        $this->assertEquals('Mozilla/5.0', $auditLog->getUserAgent());
        $this->assertInstanceOf(\DateTimeImmutable::class, $auditLog->getOccurredAt());
    }

    public function testAutomaticCriticalSeverityForCriticalAction(): void
    {
        $userId = UserId::generate();
        $action = AuditAction::EMERGENCY_STOP_TRIGGERED;

        $auditLog = AuditLog::create(
            userId: $userId,
            action: $action,
            entityType: 'Bot',
            entityId: 'bot-123'
        );

        $this->assertTrue($auditLog->isHighSeverity());
    }

    public function testIsHighSeverity(): void
    {
        $userId = UserId::generate();

        $infoLog = AuditLog::create(
            userId: $userId,
            action: AuditAction::USER_LOGGED_IN,
            entityType: 'User',
            entityId: $userId->getValue(),
            severity: AuditSeverity::INFO
        );

        $errorLog = AuditLog::create(
            userId: $userId,
            action: AuditAction::ORDER_FAILED,
            entityType: 'Order',
            entityId: 'order-123',
            severity: AuditSeverity::ERROR
        );

        $criticalLog = AuditLog::create(
            userId: $userId,
            action: AuditAction::API_KEY_EXPOSED,
            entityType: 'Exchange',
            entityId: 'exchange-123',
            severity: AuditSeverity::CRITICAL
        );

        $this->assertFalse($infoLog->isHighSeverity());
        $this->assertTrue($errorLog->isHighSeverity());
        $this->assertTrue($criticalLog->isHighSeverity());
    }
}
