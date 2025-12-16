<?php

declare(strict_types=1);

namespace App\Identity\Application\Command;

final readonly class UpdateUserPreferences
{
    /**
     * @param string $userId
     * @param string|null $reportingCurrency
     * @param string|null $timezone
     * @param string|null $language
     * @param bool|null $emailNotifications
     * @param bool|null $pushNotifications
     * @param bool|null $tradingAlerts
     * @param bool|null $newsAlerts
     * @param string|null $theme
     * @param bool|null $soundEnabled
     */
    public function __construct(
        public string $userId,
        public ?string $reportingCurrency = null,
        public ?string $timezone = null,
        public ?string $language = null,
        public ?bool $emailNotifications = null,
        public ?bool $pushNotifications = null,
        public ?bool $tradingAlerts = null,
        public ?bool $newsAlerts = null,
        public ?string $theme = null,
        public ?bool $soundEnabled = null
    ) {}
}
