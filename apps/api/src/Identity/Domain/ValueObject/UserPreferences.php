<?php

declare(strict_types=1);

namespace App\Identity\Domain\ValueObject;

/**
 * Value Object représentant les préférences utilisateur
 * Immutable et validé à la construction
 */
final readonly class UserPreferences
{
    // Devises supportées pour le reporting
    private const SUPPORTED_CURRENCIES = [
        'USD', 'EUR', 'GBP', 'JPY', 'CHF', 'CAD', 'AUD',
        'BTC', 'ETH', 'USDT', 'USDC'
    ];
    
    // Timezones courantes (liste limitée pour validation)
    private const SUPPORTED_TIMEZONES = [
        'UTC',
        'Europe/Paris',
        'Europe/London',
        'America/New_York',
        'America/Los_Angeles',
        'America/Chicago',
        'Asia/Tokyo',
        'Asia/Shanghai',
        'Asia/Singapore',
        'Australia/Sydney'
    ];
    
    // Langues supportées
    private const SUPPORTED_LANGUAGES = [
        'en', 'fr', 'es', 'de', 'it', 'pt', 'ja', 'zh', 'ko'
    ];
    
    /**
     * @param string $reportingCurrency Devise de référence pour les rapports (USD, EUR, BTC, etc.)
     * @param string $timezone Fuseau horaire de l'utilisateur
     * @param string $language Code langue ISO 639-1 (en, fr, es, etc.)
     * @param bool $emailNotifications Notifications par email activées
     * @param bool $pushNotifications Notifications push activées
     * @param bool $tradingAlerts Alertes trading activées
     * @param bool $newsAlerts Alertes actualités activées
     * @param string $theme Thème de l'interface (light, dark, auto)
     * @param bool $soundEnabled Sons activés
     */
    public function __construct(
        public string $reportingCurrency = 'USD',
        public string $timezone = 'UTC',
        public string $language = 'en',
        public bool $emailNotifications = true,
        public bool $pushNotifications = true,
        public bool $tradingAlerts = true,
        public bool $newsAlerts = true,
        public string $theme = 'auto',
        public bool $soundEnabled = true
    ) {
        $this->validate();
    }
    
    private function validate(): void
    {
        // Valider currency
        if (!in_array($this->reportingCurrency, self::SUPPORTED_CURRENCIES, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Unsupported currency: %s. Supported currencies: %s',
                    $this->reportingCurrency,
                    implode(', ', self::SUPPORTED_CURRENCIES)
                )
            );
        }
        
        // Valider timezone
        if (!in_array($this->timezone, self::SUPPORTED_TIMEZONES, true)) {
            // Fallback: vérifier si c'est une timezone valide PHP
            if (!in_array($this->timezone, \DateTimeZone::listIdentifiers(), true)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Invalid timezone: %s. Common timezones: %s',
                        $this->timezone,
                        implode(', ', self::SUPPORTED_TIMEZONES)
                    )
                );
            }
        }
        
        // Valider language
        if (!in_array($this->language, self::SUPPORTED_LANGUAGES, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Unsupported language: %s. Supported languages: %s',
                    $this->language,
                    implode(', ', self::SUPPORTED_LANGUAGES)
                )
            );
        }
        
        // Valider theme
        if (!in_array($this->theme, ['light', 'dark', 'auto'], true)) {
            throw new \InvalidArgumentException(
                'Theme must be one of: light, dark, auto'
            );
        }
    }
    
    /**
     * Créer des préférences par défaut
     */
    public static function default(): self
    {
        return new self();
    }
    
    /**
     * Créer depuis un tableau (ex: depuis JSON stocké en base)
     * 
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            reportingCurrency: $data['reportingCurrency'] ?? 'USD',
            timezone: $data['timezone'] ?? 'UTC',
            language: $data['language'] ?? 'en',
            emailNotifications: $data['emailNotifications'] ?? true,
            pushNotifications: $data['pushNotifications'] ?? true,
            tradingAlerts: $data['tradingAlerts'] ?? true,
            newsAlerts: $data['newsAlerts'] ?? true,
            theme: $data['theme'] ?? 'auto',
            soundEnabled: $data['soundEnabled'] ?? true
        );
    }
    
    /**
     * Convertir en tableau (pour sérialisation JSON)
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'reportingCurrency' => $this->reportingCurrency,
            'timezone' => $this->timezone,
            'language' => $this->language,
            'emailNotifications' => $this->emailNotifications,
            'pushNotifications' => $this->pushNotifications,
            'tradingAlerts' => $this->tradingAlerts,
            'newsAlerts' => $this->newsAlerts,
            'theme' => $this->theme,
            'soundEnabled' => $this->soundEnabled
        ];
    }
    
    /**
     * Créer une nouvelle instance avec modifications (immutabilité)
     * 
     * @param array<string, mixed> $changes
     */
    public function with(array $changes): self
    {
        $current = $this->toArray();
        $merged = array_merge($current, $changes);
        
        return self::fromArray($merged);
    }
    
    /**
     * Vérifier égalité avec d'autres préférences
     */
    public function equals(self $other): bool
    {
        return $this->toArray() === $other->toArray();
    }
    
    /**
     * Obtenir la liste des devises supportées
     * 
     * @return string[]
     */
    public static function getSupportedCurrencies(): array
    {
        return self::SUPPORTED_CURRENCIES;
    }
    
    /**
     * Obtenir la liste des langues supportées
     * 
     * @return string[]
     */
    public static function getSupportedLanguages(): array
    {
        return self::SUPPORTED_LANGUAGES;
    }
    
    /**
     * Obtenir la liste des timezones communes
     * 
     * @return string[]
     */
    public static function getSupportedTimezones(): array
    {
        return self::SUPPORTED_TIMEZONES;
    }
}
