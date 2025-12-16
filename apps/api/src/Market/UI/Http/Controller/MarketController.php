<?php

declare(strict_types=1);

namespace App\Market\UI\Http\Controller;

use App\Market\Application\Command\AddToWatchlist;
use App\Market\Application\Command\CreateWatchlist;
use App\Market\Application\Command\RemoveFromWatchlist;
use App\Market\Application\Query\GetDashboardData;
use App\Market\Application\Query\GetMarketData;
use App\Market\Application\Query\GetWatchlistWithData;
use App\Market\Domain\Repository\WatchlistRepositoryInterface;
use App\Market\Infrastructure\Adapter\MarketDataProviderInterface;
use App\Identity\Domain\ValueObject\UserId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/market', name: 'market_')]
class MarketController extends AbstractController
{
    use HandleTrait;

    public function __construct(
        MessageBusInterface $messageBus,
        private readonly WatchlistRepositoryInterface $watchlistRepository,
        private readonly MarketDataProviderInterface $marketDataProvider
    ) {
        $this->messageBus = $messageBus;
    }

    /**
     * Get dashboard with top markets
     */
    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    public function dashboard(): JsonResponse
    {
        $userId = $this->getUser()?->getUserIdentifier();
        if ($userId === null) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $query = new GetDashboardData($userId);
            $result = $this->handle($query);

            return new JsonResponse($result);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to fetch dashboard data'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get market data for a specific symbol
     */
    #[Route('/data/{symbol}', name: 'data', methods: ['GET'])]
    public function getMarketData(string $symbol): JsonResponse
    {
        try {
            $query = new GetMarketData($symbol);
            $result = $this->handle($query);

            if ($result === null) {
                return new JsonResponse([
                    'error' => 'Symbol not found'
                ], Response::HTTP_NOT_FOUND);
            }

            return new JsonResponse($result);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to fetch market data'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Search symbols
     */
    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');

        if (strlen($query) < 2) {
            return new JsonResponse([
                'results' => []
            ]);
        }

        try {
            $symbols = $this->marketDataProvider->searchSymbols($query);
            
            $results = array_map(fn($symbol) => $symbol->getValue(), $symbols);

            return new JsonResponse([
                'results' => $results
            ]);
        } catch (\Exception) {
            return new JsonResponse([
                'results' => []
            ]);
        }
    }

    /**
     * Get user's watchlists
     */
    #[Route('/watchlists', name: 'watchlists_list', methods: ['GET'])]
    public function listWatchlists(): JsonResponse
    {
        $userId = $this->getUser()?->getUserIdentifier();
        if ($userId === null) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $watchlists = $this->watchlistRepository->findByUserId(
            UserId::fromString($userId)
        );

        $watchlistsData = array_map(
            fn($watchlist) => [
                'id' => $watchlist->getId()->getValue(),
                'name' => $watchlist->getName(),
                'symbolCount' => $watchlist->getSymbolCount()
            ],
            $watchlists
        );

        return new JsonResponse([
            'watchlists' => $watchlistsData
        ]);
    }

    /**
     * Create a new watchlist
     */
    #[Route('/watchlists', name: 'watchlists_create', methods: ['POST'])]
    public function createWatchlist(Request $request): JsonResponse
    {
        $userId = $this->getUser()?->getUserIdentifier();
        if ($userId === null) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        $command = new CreateWatchlist(
            userId: $userId,
            name: $data['name'] ?? '',
            initialSymbols: $data['symbols'] ?? []
        );

        try {
            $result = $this->handle($command);

            return new JsonResponse([
                'message' => 'Watchlist created successfully',
                'watchlist' => $result
            ], Response::HTTP_CREATED);
        } catch (\DomainException $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get watchlist with market data
     */
    #[Route('/watchlists/{id}', name: 'watchlists_get', methods: ['GET'])]
    public function getWatchlist(string $id): JsonResponse
    {
        $userId = $this->getUser()?->getUserIdentifier();
        if ($userId === null) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $query = new GetWatchlistWithData($id, $userId);
            $result = $this->handle($query);

            return new JsonResponse($result);
        } catch (\DomainException $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Add symbol to watchlist
     */
    #[Route('/watchlists/{id}/symbols', name: 'watchlists_add_symbol', methods: ['POST'])]
    public function addSymbol(string $id, Request $request): JsonResponse
    {
        $userId = $this->getUser()?->getUserIdentifier();
        if ($userId === null) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        $command = new AddToWatchlist(
            watchlistId: $id,
            userId: $userId,
            symbol: $data['symbol'] ?? ''
        );

        try {
            $this->handle($command);

            return new JsonResponse([
                'message' => 'Symbol added to watchlist'
            ]);
        } catch (\DomainException $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove symbol from watchlist
     */
    #[Route('/watchlists/{id}/symbols/{symbol}', name: 'watchlists_remove_symbol', methods: ['DELETE'])]
    public function removeSymbol(string $id, string $symbol): JsonResponse
    {
        $userId = $this->getUser()?->getUserIdentifier();
        if ($userId === null) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $command = new RemoveFromWatchlist(
            watchlistId: $id,
            userId: $userId,
            symbol: $symbol
        );

        try {
            $this->handle($command);

            return new JsonResponse([
                'message' => 'Symbol removed from watchlist'
            ]);
        } catch (\DomainException $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
