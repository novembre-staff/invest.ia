<?php

declare(strict_types=1);

namespace App\Tests\Identity\Domain\ValueObject;

use App\Identity\Domain\ValueObject\UserPreferences;
use PHPUnit\Framework\TestCase;

class UserPreferencesTest extends TestCase
{
    public function testDefaultPreferences(): void
    {
        $preferences = UserPreferences::default();
        
        $this->assertEquals('USD', $preferences->reportingCurrency);
        $this->assertEquals('UTC', $preferences->timezone);
        $this->assertEquals('en', $preferences->language);
        $this->assertTrue($preferences->emailNotifications);
        $this->assertTrue($preferences->pushNotifications);
        $this->assertTrue($preferences->tradingAlerts);
        $this->assertTrue($preferences->newsAlerts);
        $this->assertEquals('auto', $preferences->theme);
        $this->assertTrue($preferences->soundEnabled);
    }
    
    public function testCreatePreferencesWithCustomValues(): void
    {
        $preferences = new UserPreferences(
            reportingCurrency: 'EUR',
            timezone: 'Europe/Paris',
            language: 'fr',
            emailNotifications: false,
            pushNotifications: false,
            tradingAlerts: true,
            newsAlerts: false,
            theme: 'dark',
            soundEnabled: false
        );
        
        $this->assertEquals('EUR', $preferences->reportingCurrency);
        $this->assertEquals('Europe/Paris', $preferences->timezone);
        $this->assertEquals('fr', $preferences->language);
        $this->assertFalse($preferences->emailNotifications);
        $this->assertFalse($preferences->pushNotifications);
        $this->assertTrue($preferences->tradingAlerts);
        $this->assertFalse($preferences->newsAlerts);
        $this->assertEquals('dark', $preferences->theme);
        $this->assertFalse($preferences->soundEnabled);
    }
    
    public function testInvalidCurrency(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported currency');
        
        new UserPreferences(reportingCurrency: 'INVALID');
    }
    
    public function testInvalidTimezone(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid timezone');
        
        new UserPreferences(timezone: 'Invalid/Timezone');
    }
    
    public function testInvalidLanguage(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported language');
        
        new UserPreferences(language: 'xx');
    }
    
    public function testInvalidTheme(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Theme must be one of: light, dark, auto');
        
        new UserPreferences(theme: 'invalid');
    }
    
    public function testFromArray(): void
    {
        $data = [
            'reportingCurrency' => 'BTC',
            'timezone' => 'Asia/Tokyo',
            'language' => 'ja',
            'emailNotifications' => false,
            'pushNotifications' => true,
            'tradingAlerts' => false,
            'newsAlerts' => true,
            'theme' => 'light',
            'soundEnabled' => false
        ];
        
        $preferences = UserPreferences::fromArray($data);
        
        $this->assertEquals('BTC', $preferences->reportingCurrency);
        $this->assertEquals('Asia/Tokyo', $preferences->timezone);
        $this->assertEquals('ja', $preferences->language);
        $this->assertFalse($preferences->emailNotifications);
        $this->assertTrue($preferences->pushNotifications);
        $this->assertFalse($preferences->tradingAlerts);
        $this->assertTrue($preferences->newsAlerts);
        $this->assertEquals('light', $preferences->theme);
        $this->assertFalse($preferences->soundEnabled);
    }
    
    public function testToArray(): void
    {
        $preferences = new UserPreferences(
            reportingCurrency: 'ETH',
            timezone: 'America/New_York',
            language: 'en'
        );
        
        $array = $preferences->toArray();
        
        $this->assertIsArray($array);
        $this->assertEquals('ETH', $array['reportingCurrency']);
        $this->assertEquals('America/New_York', $array['timezone']);
        $this->assertEquals('en', $array['language']);
        $this->assertArrayHasKey('emailNotifications', $array);
        $this->assertArrayHasKey('pushNotifications', $array);
    }
    
    public function testWith(): void
    {
        $original = UserPreferences::default();
        
        $modified = $original->with([
            'reportingCurrency' => 'EUR',
            'theme' => 'dark'
        ]);
        
        // Original unchanged (immutable)
        $this->assertEquals('USD', $original->reportingCurrency);
        $this->assertEquals('auto', $original->theme);
        
        // Modified has new values
        $this->assertEquals('EUR', $modified->reportingCurrency);
        $this->assertEquals('dark', $modified->theme);
        
        // Other values preserved
        $this->assertEquals('UTC', $modified->timezone);
        $this->assertEquals('en', $modified->language);
    }
    
    public function testEquals(): void
    {
        $prefs1 = UserPreferences::default();
        $prefs2 = UserPreferences::default();
        $prefs3 = new UserPreferences(reportingCurrency: 'EUR');
        
        $this->assertTrue($prefs1->equals($prefs2));
        $this->assertFalse($prefs1->equals($prefs3));
    }
    
    public function testGetSupportedCurrencies(): void
    {
        $currencies = UserPreferences::getSupportedCurrencies();
        
        $this->assertIsArray($currencies);
        $this->assertContains('USD', $currencies);
        $this->assertContains('EUR', $currencies);
        $this->assertContains('BTC', $currencies);
        $this->assertContains('ETH', $currencies);
    }
    
    public function testGetSupportedLanguages(): void
    {
        $languages = UserPreferences::getSupportedLanguages();
        
        $this->assertIsArray($languages);
        $this->assertContains('en', $languages);
        $this->assertContains('fr', $languages);
        $this->assertContains('es', $languages);
    }
    
    public function testGetSupportedTimezones(): void
    {
        $timezones = UserPreferences::getSupportedTimezones();
        
        $this->assertIsArray($timezones);
        $this->assertContains('UTC', $timezones);
        $this->assertContains('Europe/Paris', $timezones);
        $this->assertContains('America/New_York', $timezones);
    }
}
