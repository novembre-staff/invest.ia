<?php

declare(strict_types=1);

namespace App\Tests\Identity\Domain\Service;

use App\Identity\Domain\Service\TotpService;
use PHPUnit\Framework\TestCase;

class TotpServiceTest extends TestCase
{
    private TotpService $totpService;
    
    protected function setUp(): void
    {
        $this->totpService = new TotpService();
    }
    
    public function testGenerateSecret(): void
    {
        $result = $this->totpService->generateSecret('test@example.com');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('secret', $result);
        $this->assertArrayHasKey('qrCodeUri', $result);
        
        // Secret should be base32 encoded (only A-Z and 2-7)
        $this->assertMatchesRegularExpression('/^[A-Z2-7]+$/', $result['secret']);
        
        // QR code URI should contain the email and issuer
        $this->assertStringContainsString('test@example.com', $result['qrCodeUri']);
        $this->assertStringContainsString('invest.ia', $result['qrCodeUri']);
        $this->assertStringStartsWith('otpauth://totp/', $result['qrCodeUri']);
    }
    
    public function testVerifyCodeWithValidCode(): void
    {
        // Generate a secret
        $result = $this->totpService->generateSecret('test@example.com');
        $secret = $result['secret'];
        
        // Get the current code
        $currentCode = $this->totpService->getCurrentCode($secret);
        
        // Verify it
        $this->assertTrue($this->totpService->verifyCode($secret, $currentCode));
    }
    
    public function testVerifyCodeWithInvalidCode(): void
    {
        $result = $this->totpService->generateSecret('test@example.com');
        $secret = $result['secret'];
        
        // Invalid code
        $this->assertFalse($this->totpService->verifyCode($secret, '000000'));
    }
    
    public function testVerifyCodeWithInvalidSecret(): void
    {
        // Invalid secret format
        $this->assertFalse($this->totpService->verifyCode('invalid-secret', '123456'));
    }
    
    public function testIsValidSecret(): void
    {
        $result = $this->totpService->generateSecret('test@example.com');
        $validSecret = $result['secret'];
        
        $this->assertTrue($this->totpService->isValidSecret($validSecret));
        $this->assertFalse($this->totpService->isValidSecret('invalid-secret'));
        $this->assertFalse($this->totpService->isValidSecret(''));
    }
    
    public function testGetCurrentCode(): void
    {
        $result = $this->totpService->generateSecret('test@example.com');
        $secret = $result['secret'];
        
        $code = $this->totpService->getCurrentCode($secret);
        
        // Code should be 6 digits
        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);
    }
    
    public function testGetQrCodeUrl(): void
    {
        $result = $this->totpService->generateSecret('test@example.com');
        $provisioningUri = $result['qrCodeUri'];
        
        $qrCodeUrl = $this->totpService->getQrCodeUrl($provisioningUri);
        
        $this->assertStringStartsWith('https://chart.googleapis.com/chart', $qrCodeUrl);
        $this->assertStringContainsString('chs=200x200', $qrCodeUrl);
        $this->assertStringContainsString('cht=qr', $qrCodeUrl);
        $this->assertStringContainsString(urlencode($provisioningUri), $qrCodeUrl);
    }
    
    public function testGetQrCodeUrlWithCustomSize(): void
    {
        $result = $this->totpService->generateSecret('test@example.com');
        $provisioningUri = $result['qrCodeUri'];
        
        $qrCodeUrl = $this->totpService->getQrCodeUrl($provisioningUri, 300);
        
        $this->assertStringContainsString('chs=300x300', $qrCodeUrl);
    }
    
    public function testVerifyCodeWithTimeWindow(): void
    {
        // This test verifies that the time window parameter works
        $result = $this->totpService->generateSecret('test@example.com');
        $secret = $result['secret'];
        
        $currentCode = $this->totpService->getCurrentCode($secret);
        
        // Should work with window=0 (exact time match)
        $this->assertTrue($this->totpService->verifyCode($secret, $currentCode, 0));
        
        // Should work with window=1 (default, allows ±30 seconds)
        $this->assertTrue($this->totpService->verifyCode($secret, $currentCode, 1));
        
        // Should work with larger window
        $this->assertTrue($this->totpService->verifyCode($secret, $currentCode, 2));
    }
}
