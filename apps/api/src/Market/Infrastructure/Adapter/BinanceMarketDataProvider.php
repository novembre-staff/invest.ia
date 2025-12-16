<?php

declare(strict_types=1);

namespace App\Market\Infrastructure\Adapter;

use App\Market\Domain\Model\MarketData;
use App\Market\Domain\ValueObject\Price;
use App\Market\Domain\ValueObject\Symbol;
use App\Market\Domain\ValueObject\Volume;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Binance market data adapter
 * API Docs: https://binance-docs.github.io/apidocs/spot/en/#24hr-ticker-price-change-statistics
 */
final readonly class BinanceMarketDataProvider implements MarketDataProviderInterface
{
    private const BASE_URL = 'https://api.binance.com';

    public function __construct(
        private HttpClientInterface $httpClient
    ) {
    }

    public function getMarketData(Symbol $symbol): ?MarketData
    {
        try {
            $response = $this->httpClient->request('GET', self::BASE_URL . '/api/v3/ticker/24hr', [
                'query' => ['symbol' => $symbol->getValue()],
            ]);

            $data = $response->toArray();

            return $this->mapToMarketData($data);
        } catch (\Exception) {
            return null;
        }
    }

    public function getMultipleMarketData(array $symbols): array
    {
        if (empty($symbols)) {
            return [];
        }

        try {
            // Binance supports multiple symbols in one request
            $symbolStrings = array_map(fn(Symbol $s) => $s->getValue(), $symbols);

            $response = $this->httpClient->request('GET', self::BASE_URL . '/api/v3/ticker/24hr', [
                'query' => ['symbols' => json_encode($symbolStrings)],
            ]);

            $dataArray = $response->toArray();

            $results = [];
            foreach ($dataArray as $data) {
                $marketData = $this->mapToMarketData($data);
                $results[] = $marketData;
            }

            return $results;
        } catch (\Exception) {
            return [];
        }
    }

    public function getTopMarkets(int $limit = 20): array
    {
        try {
            // Get all USDT pairs
            $response = $this->httpClient->request('GET', self::BASE_URL . '/api/v3/ticker/24hr');

            $dataArray = $response->toArray();

            // Filter USDT pairs and sort by volume
            $usdtPairs = array_filter($dataArray, function ($data) {
                return str_ends_with($data['symbol'], 'USDT');
            });

            usort($usdtPairs, function ($a, $b) {
                return ($b['quoteVolume'] ?? 0) <=> ($a['quoteVolume'] ?? 0);
            });

            // Take top N and map to MarketData
            $topPairs = array_slice($usdtPairs, 0, $limit);

            $results = [];
            foreach ($topPairs as $data) {
                $results[] = $this->mapToMarketData($data);
            }

            return $results;
        } catch (\Exception) {
            return [];
        }
    }

    public function searchSymbols(string $query): array
    {
        try {
            $query = strtoupper($query);

            // Get exchange info to search symbols
            $response = $this->httpClient->request('GET', self::BASE_URL . '/api/v3/exchangeInfo');

            $data = $response->toArray();
            $symbols = $data['symbols'] ?? [];

            // Filter symbols matching query
            $matchingSymbols = array_filter($symbols, function ($symbolData) use ($query) {
                $symbol = $symbolData['symbol'] ?? '';
                $baseAsset = $symbolData['baseAsset'] ?? '';

                return str_contains($symbol, $query) || str_contains($baseAsset, $query);
            });

            // Limit to 50 results and only USDT pairs
            $results = [];
            $count = 0;
            foreach ($matchingSymbols as $symbolData) {
                $symbol = $symbolData['symbol'] ?? '';
                if (str_ends_with($symbol, 'USDT') && $count < 50) {
                    $results[] = Symbol::fromString($symbol);
                    $count++;
                }
            }

            return $results;
        } catch (\Exception) {
            return [];
        }
    }

    /**
     * Map Binance API response to MarketData domain model
     */
    private function mapToMarketData(array $data): MarketData
    {
        $symbol = Symbol::fromString($data['symbol']);
        $quoteAsset = $symbol->getQuoteAsset();

        return new MarketData(
            symbol: $symbol,
            currentPrice: Price::fromFloat((float)$data['lastPrice'], $quoteAsset),
            highPrice24h: Price::fromFloat((float)$data['highPrice'], $quoteAsset),
            lowPrice24h: Price::fromFloat((float)$data['lowPrice'], $quoteAsset),
            openPrice24h: Price::fromFloat((float)$data['openPrice'], $quoteAsset),
            volume24h: Volume::fromFloat((float)($data['quoteVolume'] ?? $data['volume'])),
            priceChangePercent24h: (float)$data['priceChangePercent'],
            timestamp: new \DateTimeImmutable('@' . intval($data['closeTime'] / 1000))
        );
    }
}
