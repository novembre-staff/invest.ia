<?php

declare(strict_types=1);

namespace App\Tests\Audit\Application\Handler;

use App\Audit\Application\Command\LogAuditEvent;
use App\Audit\Application\Handler\LogAuditEventHandler;
use App\Audit\Domain\Model\AuditLog;
use App\Audit\Domain\Repository\AuditLogRepositoryInterface;
use App\Audit\Domain\ValueObject\AuditAction;
use PHPUnit\Framework\TestCase;

class LogAuditEventHandlerTest extends TestCase
{
    private AuditLogRepositoryInterface $repository;
    private LogAuditEventHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AuditLogRepositoryInterface::class);
        $this->handler = new LogAuditEventHandler($this->repository);
    }

    public function testLogAuditEvent(): void
    {
        $command = new LogAuditEvent(
            userId: '550e8400-e29b-41d4-a716-446655440000',
            action: AuditAction::USER_LOGGED_IN->value,
            entityType: 'User',
            entityId: '550e8400-e29b-41d4-a716-446655440000',
            severity: 'info',
            metadata: ['ip' => '127.0.0.1'],
            ipAddress: '127.0.0.1',
            userAgent: 'Mozilla/5.0'
        );

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(AuditLog::class));

        ($this->handler)($command);
    }

    public function testAutomaticCriticalSeverity(): void
    {
        $command = new LogAuditEvent(
            userId: '550e8400-e29b-41d4-a716-446655440000',
            action: AuditAction::EMERGENCY_STOP_TRIGGERED->value,
            entityType: 'Bot',
            entityId: 'bot-123',
            // No severity specified - should auto-detect as CRITICAL
            severity: null
        );

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (AuditLog $log) {
                return $log->getSeverity()->value === 'critical';
            }));

        ($this->handler)($command);
    }
}
