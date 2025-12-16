<?php

declare(strict_types=1);

namespace App\Audit\Domain\ValueObject;

/**
 * Actions auditables dans le système
 */
enum AuditAction: string
{
    // Authentification
    case USER_REGISTERED = 'user.registered';
    case USER_LOGGED_IN = 'user.logged_in';
    case USER_LOGGED_OUT = 'user.logged_out';
    case USER_PASSWORD_CHANGED = 'user.password_changed';
    case USER_MFA_ENABLED = 'user.mfa_enabled';
    case USER_MFA_DISABLED = 'user.mfa_disabled';
    
    // Exchange
    case EXCHANGE_CONNECTED = 'exchange.connected';
    case EXCHANGE_DISCONNECTED = 'exchange.disconnected';
    case EXCHANGE_API_CALL_FAILED = 'exchange.api_call_failed';
    
    // Trading
    case ORDER_CREATED = 'order.created';
    case ORDER_CANCELLED = 'order.cancelled';
    case ORDER_EXECUTED = 'order.executed';
    case ORDER_FAILED = 'order.failed';
    
    // Bots
    case BOT_CREATED = 'bot.created';
    case BOT_STARTED = 'bot.started';
    case BOT_STOPPED = 'bot.stopped';
    case BOT_PAUSED = 'bot.paused';
    case BOT_DELETED = 'bot.deleted';
    case BOT_SETTINGS_UPDATED = 'bot.settings_updated';
    
    // Strategy
    case STRATEGY_CREATED = 'strategy.created';
    case STRATEGY_EXECUTED = 'strategy.executed';
    case STRATEGY_STOPPED = 'strategy.stopped';
    
    // Portfolio
    case POSITION_OPENED = 'position.opened';
    case POSITION_CLOSED = 'position.closed';
    case POSITION_MODIFIED = 'position.modified';
    
    // Risk
    case RISK_LIMIT_BREACHED = 'risk.limit_breached';
    case EMERGENCY_STOP_TRIGGERED = 'risk.emergency_stop';
    case RISK_PROFILE_UPDATED = 'risk.profile_updated';
    
    // Alerts
    case ALERT_CREATED = 'alert.created';
    case ALERT_TRIGGERED = 'alert.triggered';
    case ALERT_DELETED = 'alert.deleted';
    
    // Settings
    case PREFERENCES_UPDATED = 'preferences.updated';
    case NOTIFICATION_SETTINGS_CHANGED = 'notification.settings_changed';
    
    // Security
    case SUSPICIOUS_ACTIVITY_DETECTED = 'security.suspicious_activity';
    case API_KEY_EXPOSED = 'security.api_key_exposed';
    case UNAUTHORIZED_ACCESS_ATTEMPT = 'security.unauthorized_access';

    public function isCritical(): bool
    {
        return in_array($this, [
            self::RISK_LIMIT_BREACHED,
            self::EMERGENCY_STOP_TRIGGERED,
            self::EXCHANGE_API_CALL_FAILED,
            self::ORDER_FAILED,
            self::API_KEY_EXPOSED,
            self::SUSPICIOUS_ACTIVITY_DETECTED,
            self::UNAUTHORIZED_ACCESS_ATTEMPT,
        ]);
    }

    public function isSecurityRelated(): bool
    {
        return in_array($this, [
            self::USER_LOGGED_IN,
            self::USER_LOGGED_OUT,
            self::USER_MFA_ENABLED,
            self::USER_MFA_DISABLED,
            self::USER_PASSWORD_CHANGED,
            self::SUSPICIOUS_ACTIVITY_DETECTED,
            self::API_KEY_EXPOSED,
            self::UNAUTHORIZED_ACCESS_ATTEMPT,
        ]);
    }
}
