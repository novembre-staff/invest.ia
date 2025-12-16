<?php

declare(strict_types=1);

namespace App\Market\Domain\Model;

use App\Market\Domain\ValueObject\Symbol;
use App\Market\Domain\ValueObject\WatchlistId;
use App\Identity\Domain\ValueObject\UserId;

/**
 * Watchlist Aggregate Root
 * User's custom list of crypto assets to monitor
 */
class Watchlist
{
    /** @var Symbol[] */
    private array $symbols = [];
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        private readonly WatchlistId $id,
        private readonly UserId $userId,
        private string $name,
        array $initialSymbols = []
    ) {
        if (empty($name)) {
            throw new \InvalidArgumentException('Watchlist name cannot be empty');
        }

        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        foreach ($initialSymbols as $symbol) {
            $this->addSymbol($symbol);
        }
    }

    public function getId(): WatchlistId
    {
        return $this->id;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function rename(string $newName): void
    {
        if (empty($newName)) {
            throw new \InvalidArgumentException('Watchlist name cannot be empty');
        }

        $this->name = $newName;
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * @return Symbol[]
     */
    public function getSymbols(): array
    {
        return $this->symbols;
    }

    public function addSymbol(Symbol $symbol): void
    {
        // Check if already exists
        foreach ($this->symbols as $existing) {
            if ($existing->equals($symbol)) {
                return; // Already in watchlist
            }
        }

        if (count($this->symbols) >= 50) {
            throw new \DomainException('Watchlist cannot contain more than 50 symbols');
        }

        $this->symbols[] = $symbol;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function removeSymbol(Symbol $symbol): void
    {
        $this->symbols = array_filter(
            $this->symbols,
            fn(Symbol $s) => !$s->equals($symbol)
        );

        // Re-index array
        $this->symbols = array_values($this->symbols);
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function hasSymbol(Symbol $symbol): bool
    {
        foreach ($this->symbols as $existing) {
            if ($existing->equals($symbol)) {
                return true;
            }
        }

        return false;
    }

    public function getSymbolCount(): int
    {
        return count($this->symbols);
    }

    public function isEmpty(): bool
    {
        return empty($this->symbols);
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
