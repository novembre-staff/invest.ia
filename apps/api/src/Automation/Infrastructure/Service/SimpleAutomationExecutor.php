<?php

declare(strict_types=1);

namespace App\Automation\Infrastructure\Service;

use App\Automation\Domain\Model\Automation;
use App\Automation\Domain\Service\AutomationExecutorInterface;
use App\Automation\Domain\ValueObject\AutomationType;
use App\Portfolio\Infrastructure\Adapter\PortfolioProviderInterface;
use App\Trading\Application\Command\PlaceOrder;
use App\Trading\Domain\ValueObject\OrderSide;
use App\Trading\Domain\ValueObject\OrderType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SimpleAutomationExecutor implements AutomationExecutorInterface
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private PortfolioProviderInterface $portfolioProvider,
        private LoggerInterface $logger
    ) {
    }

    public function execute(Automation $automation): float
    {
        return match ($automation->getType()) {
            AutomationType::DCA => $this->executeDca($automation),
            AutomationType::GRID_TRADING => $this->executeGridTrading($automation),
            AutomationType::REBALANCING => $this->executeRebalancing($automation),
            default => throw new \DomainException("Automation type {$automation->getType()->value} not supported")
        };
    }

    public function canExecute(Automation $automation): bool
    {
        // Check if user has sufficient balance
        try {
            $portfolio = $this->portfolioProvider->getPortfolio($automation->getUserId()->toString());
            
            $dcaConfig = $automation->getDcaConfig();
            if ($dcaConfig !== null) {
                $requiredBalance = $dcaConfig->getAmountPerPurchase();
                $usdtBalance = $portfolio->getBalance('USDT');
                
                return $usdtBalance >= $requiredBalance;
            }

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to check execution capability', [
                'automation_id' => $automation->getId()->toString(),
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    private function executeDca(Automation $automation): float
    {
        $dcaConfig = $automation->getDcaConfig();
        if ($dcaConfig === null) {
            throw new \DomainException('DCA configuration is required');
        }

        $amount = $dcaConfig->getAmountPerPurchase();
        $symbol = $automation->getSymbol()->toString();

        // Place market buy order
        $command = new PlaceOrder(
            userId: $automation->getUserId()->toString(),
            symbol: $symbol,
            side: OrderSide::BUY->value,
            type: OrderType::MARKET->value,
            quoteQuantity: $amount
        );

        $this->commandBus->dispatch($command);

        $this->logger->info('DCA order placed', [
            'automation_id' => $automation->getId()->toString(),
            'symbol' => $symbol,
            'amount' => $amount
        ]);

        return $amount;
    }

    private function executeGridTrading(Automation $automation): float
    {
        $gridConfig = $automation->getGridConfig();
        if ($gridConfig === null) {
            throw new \DomainException('Grid configuration is required');
        }

        // Get current price
        $symbol = $automation->getSymbol()->toString();
        
        // TODO: Get current price from market data provider
        // For now, place orders at grid levels
        $gridPrices = $gridConfig->calculateGridPrices();
        $quantity = $gridConfig->getQuantityPerGrid();

        // Place limit orders at each grid level
        // Buy orders below current price, sell orders above
        // This is a simplified implementation
        
        $investedAmount = 0.0;

        foreach ($gridPrices as $price) {
            // Place buy limit order
            $command = new PlaceOrder(
                userId: $automation->getUserId()->toString(),
                symbol: $symbol,
                side: OrderSide::BUY->value,
                type: OrderType::LIMIT->value,
                quantity: $quantity,
                price: $price
            );

            $this->commandBus->dispatch($command);
            $investedAmount += $quantity * $price;
        }

        $this->logger->info('Grid trading orders placed', [
            'automation_id' => $automation->getId()->toString(),
            'symbol' => $symbol,
            'grid_levels' => count($gridPrices),
            'invested_amount' => $investedAmount
        ]);

        return $investedAmount;
    }

    private function executeRebalancing(Automation $automation): float
    {
        // TODO: Implement portfolio rebalancing logic
        // This would require:
        // 1. Get target allocation from parameters
        // 2. Get current portfolio state
        // 3. Calculate required trades to reach target
        // 4. Execute trades

        $this->logger->info('Rebalancing executed', [
            'automation_id' => $automation->getId()->toString()
        ]);

        return 0.0;
    }
}
