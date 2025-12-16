<?php

declare(strict_types=1);

namespace App\Identity\Application\DTO;

use App\Identity\Domain\ValueObject\UserPreferences;

final readonly class UserPreferencesDTO
{
    public function __construct(
        public string $reportingCurrency,
        public string $timezone,
        public string $language,
        public bool $emailNotifications,
        public bool $pushNotifications,
        public bool $tradingAlerts,
        public bool $newsAlerts,
        public string $theme,
        public bool $soundEnabled
    ) {}
    
    public static function fromValueObject(UserPreferences $preferences): self
    {
        return new self(
            reportingCurrency: $preferences->reportingCurrency,
            timezone: $preferences->timezone,
            language: $preferences->language,
            emailNotifications: $preferences->emailNotifications,
            pushNotifications: $preferences->pushNotifications,
            tradingAlerts: $preferences->tradingAlerts,
            newsAlerts: $preferences->newsAlerts,
            theme: $preferences->theme,
            soundEnabled: $preferences->soundEnabled
        );
    }
}
