<?php

declare(strict_types=1);

namespace App\Strategy\UI\Http\Controller;

use App\Shared\UI\Http\HandleTrait;
use App\Strategy\Application\Command\ActivateStrategy;
use App\Strategy\Application\Command\CreateStrategy;
use App\Strategy\Application\Command\PauseStrategy;
use App\Strategy\Application\Command\RunBacktest;
use App\Strategy\Application\Command\StopStrategy;
use App\Strategy\Application\Command\UpdateStrategy;
use App\Strategy\Application\Query\GetStrategyById;
use App\Strategy\Application\Query\GetUserStrategies;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/strategies')]
class StrategyController
{
    use HandleTrait;

    #[Route('', methods: ['GET'])]
    public function listStrategies(Request $request): JsonResponse
    {
        try {
            // TODO: Get from authentication
            $userId = $request->query->get('userId');
            $activeOnly = $request->query->getBoolean('activeOnly', false);
            $limit = min((int) $request->query->get('limit', 50), 200);

            $query = new GetUserStrategies($userId, $activeOnly, $limit);
            $strategies = $this->handleQuery($query);

            return new JsonResponse($strategies);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getStrategy(string $id, Request $request): JsonResponse
    {
        try {
            // TODO: Get from authentication
            $userId = $request->query->get('userId');

            $query = new GetStrategyById($id, $userId);
            $strategy = $this->handleQuery($query);

            return new JsonResponse($strategy);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', methods: ['POST'])]
    public function createStrategy(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // TODO: Get from authentication
            $userId = $data['userId'] ?? null;

            $command = new CreateStrategy(
                userId: $userId,
                name: $data['name'],
                description: $data['description'] ?? null,
                type: $data['type'],
                symbols: $data['symbols'],
                timeFrame: $data['timeFrame'],
                indicators: $data['indicators'],
                entryRules: $data['entryRules'],
                exitRules: $data['exitRules'],
                positionSizePercent: $data['positionSizePercent'] ?? 10.0,
                maxDrawdownPercent: $data['maxDrawdownPercent'] ?? 20.0,
                stopLossPercent: $data['stopLossPercent'] ?? null,
                takeProfitPercent: $data['takeProfitPercent'] ?? null
            );

            $strategy = $this->handleCommand($command);

            return new JsonResponse($strategy, Response::HTTP_CREATED);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function updateStrategy(string $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // TODO: Get from authentication
            $userId = $data['userId'] ?? null;

            $command = new UpdateStrategy(
                strategyId: $id,
                userId: $userId,
                name: $data['name'],
                description: $data['description'] ?? null,
                symbols: $data['symbols'],
                timeFrame: $data['timeFrame'],
                indicators: $data['indicators'],
                entryRules: $data['entryRules'],
                exitRules: $data['exitRules'],
                positionSizePercent: $data['positionSizePercent'] ?? 10.0,
                maxDrawdownPercent: $data['maxDrawdownPercent'] ?? 20.0,
                stopLossPercent: $data['stopLossPercent'] ?? null,
                takeProfitPercent: $data['takeProfitPercent'] ?? null
            );

            $strategy = $this->handleCommand($command);

            return new JsonResponse($strategy);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/backtest', methods: ['POST'])]
    public function runBacktest(string $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // TODO: Get from authentication
            $userId = $data['userId'] ?? null;

            $command = new RunBacktest(
                strategyId: $id,
                userId: $userId,
                startDate: $data['startDate'],
                endDate: $data['endDate'],
                initialCapital: $data['initialCapital'] ?? 10000.0
            );

            $strategy = $this->handleCommand($command);

            return new JsonResponse($strategy);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/activate', methods: ['POST'])]
    public function activateStrategy(string $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // TODO: Get from authentication
            $userId = $data['userId'] ?? null;

            $command = new ActivateStrategy($id, $userId);
            $strategy = $this->handleCommand($command);

            return new JsonResponse($strategy);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/pause', methods: ['POST'])]
    public function pauseStrategy(string $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // TODO: Get from authentication
            $userId = $data['userId'] ?? null;

            $command = new PauseStrategy($id, $userId);
            $strategy = $this->handleCommand($command);

            return new JsonResponse($strategy);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/stop', methods: ['POST'])]
    public function stopStrategy(string $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // TODO: Get from authentication
            $userId = $data['userId'] ?? null;

            $command = new StopStrategy($id, $userId);
            $strategy = $this->handleCommand($command);

            return new JsonResponse($strategy);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
