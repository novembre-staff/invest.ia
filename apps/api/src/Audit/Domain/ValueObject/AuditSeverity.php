<?php

declare(strict_types=1);

namespace App\Audit\Domain\ValueObject;

/**
 * Niveaux de sévérité pour les logs d'audit
 */
enum AuditSeverity: string
{
    case DEBUG = 'debug';
    case INFO = 'info';
    case WARNING = 'warning';
    case ERROR = 'error';
    case CRITICAL = 'critical';

    public function isHighPriority(): bool
    {
        return in_array($this, [self::ERROR, self::CRITICAL]);
    }

    public function getNumericLevel(): int
    {
        return match ($this) {
            self::DEBUG => 100,
            self::INFO => 200,
            self::WARNING => 300,
            self::ERROR => 400,
            self::CRITICAL => 500,
        };
    }
}
