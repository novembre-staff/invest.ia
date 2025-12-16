<?php

declare(strict_types=1);

namespace App\Market\Domain\ValueObject;

/**
 * Symbol represents a trading pair (e.g., BTCUSDT, ETHUSDT)
 */
final readonly class Symbol
{
    public function __construct(
        private string $value
    ) {
        if (empty($value)) {
            throw new \InvalidArgumentException('Symbol cannot be empty');
        }

        // Validate format: uppercase alphanumeric
        if (!preg_match('/^[A-Z0-9]+$/', $value)) {
            throw new \InvalidArgumentException('Symbol must be uppercase alphanumeric');
        }
    }

    public static function fromString(string $value): self
    {
        return new self(strtoupper($value));
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Extract base and quote assets from symbol (e.g., BTCUSDT -> BTC, USDT)
     */
    public function getBaseAsset(): string
    {
        // Common quote assets
        $quoteAssets = ['USDT', 'BUSD', 'USD', 'EUR', 'BTC', 'ETH', 'BNB'];
        
        foreach ($quoteAssets as $quote) {
            if (str_ends_with($this->value, $quote)) {
                return substr($this->value, 0, -strlen($quote));
            }
        }
        
        // Fallback: assume last 3-4 chars are quote
        return substr($this->value, 0, -4);
    }

    public function getQuoteAsset(): string
    {
        $quoteAssets = ['USDT', 'BUSD', 'USD', 'EUR', 'BTC', 'ETH', 'BNB'];
        
        foreach ($quoteAssets as $quote) {
            if (str_ends_with($this->value, $quote)) {
                return $quote;
            }
        }
        
        return substr($this->value, -4);
    }
}
