<?php

declare(strict_types=1);

namespace App\Portfolio\UI\Http\Controller;

use App\Portfolio\Application\Query\GetPortfolio;
use App\Portfolio\Application\Query\GetTradeHistory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/portfolio', name: 'portfolio_')]
class PortfolioController extends AbstractController
{
    use HandleTrait;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    /**
     * Get user's portfolio (balances)
     */
    #[Route('', name: 'get', methods: ['GET'])]
    public function getPortfolio(): JsonResponse
    {
        $userId = $this->getUser()?->getUserIdentifier();
        if ($userId === null) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $query = new GetPortfolio($userId);
            $portfolio = $this->handle($query);

            return new JsonResponse([
                'portfolio' => $portfolio
            ]);
        } catch (\DomainException $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to fetch portfolio'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get trade history
     */
    #[Route('/trades', name: 'trades', methods: ['GET'])]
    public function getTradeHistory(Request $request): JsonResponse
    {
        $userId = $this->getUser()?->getUserIdentifier();
        if ($userId === null) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $symbol = $request->query->get('symbol');
        $limit = (int)$request->query->get('limit', 50);

        try {
            $query = new GetTradeHistory(
                userId: $userId,
                symbol: $symbol,
                limit: min($limit, 100) // Max 100
            );
            
            $trades = $this->handle($query);

            return new JsonResponse([
                'trades' => $trades,
                'total' => count($trades)
            ]);
        } catch (\DomainException $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to fetch trade history'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
