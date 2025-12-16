<?php

declare(strict_types=1);

namespace App\Portfolio\Domain\Model;

use App\Portfolio\Domain\ValueObject\AssetBalance;

/**
 * Portfolio represents user's holdings on an exchange
 * This is a read model (data comes from exchange API)
 */
class Portfolio
{
    /**
     * @param AssetBalance[] $balances
     */
    public function __construct(
        private readonly string $exchangeName,
        private readonly string $userId,
        private readonly array $balances,
        private readonly \DateTimeImmutable $lastUpdated
    ) {
    }

    public function getExchangeName(): string
    {
        return $this->exchangeName;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @return AssetBalance[]
     */
    public function getBalances(): array
    {
        return $this->balances;
    }

    /**
     * Get balances that have actual value (> 0)
     * 
     * @return AssetBalance[]
     */
    public function getNonZeroBalances(): array
    {
        return array_filter(
            $this->balances,
            fn(AssetBalance $balance) => $balance->hasBalance()
        );
    }

    public function getBalance(string $asset): ?AssetBalance
    {
        foreach ($this->balances as $balance) {
            if ($balance->getAsset() === $asset) {
                return $balance;
            }
        }

        return null;
    }

    public function hasAsset(string $asset): bool
    {
        $balance = $this->getBalance($asset);
        return $balance !== null && $balance->hasBalance();
    }

    public function getLastUpdated(): \DateTimeImmutable
    {
        return $this->lastUpdated;
    }

    public function getTotalAssets(): int
    {
        return count($this->getNonZeroBalances());
    }
}
