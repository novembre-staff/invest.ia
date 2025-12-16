<?php

declare(strict_types=1);

namespace App\Portfolio\Application\DTO;

use App\Portfolio\Domain\Model\Portfolio;

final readonly class PortfolioDTO
{
    /**
     * @param array<string, array{free: float, locked: float, total: float}> $balances
     */
    private function __construct(
        public string $exchangeName,
        public array $balances,
        public int $totalAssets,
        public string $lastUpdated
    ) {
    }

    public static function fromDomain(Portfolio $portfolio): self
    {
        $balancesData = [];
        foreach ($portfolio->getNonZeroBalances() as $balance) {
            $balancesData[$balance->getAsset()] = [
                'free' => $balance->getFree(),
                'locked' => $balance->getLocked(),
                'total' => $balance->getTotal(),
            ];
        }

        return new self(
            exchangeName: $portfolio->getExchangeName(),
            balances: $balancesData,
            totalAssets: $portfolio->getTotalAssets(),
            lastUpdated: $portfolio->getLastUpdated()->format(\DateTimeInterface::ATOM)
        );
    }
}
