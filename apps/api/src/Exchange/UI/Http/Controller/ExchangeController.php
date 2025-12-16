<?php

declare(strict_types=1);

namespace App\Exchange\UI\Http\Controller;

use App\Exchange\Application\Command\ConnectExchange;
use App\Exchange\Application\Command\DisconnectExchange;
use App\Exchange\Application\DTO\ExchangeConnectionDTO;
use App\Exchange\Domain\Repository\ExchangeConnectionRepositoryInterface;
use App\Identity\Domain\ValueObject\UserId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/exchanges', name: 'exchanges_')]
class ExchangeController extends AbstractController
{
    use HandleTrait;

    public function __construct(
        MessageBusInterface $commandBus,
        private readonly ExchangeConnectionRepositoryInterface $connectionRepository
    ) {
        $this->messageBus = $commandBus;
    }

    /**
     * Connect to an exchange (e.g., Binance)
     */
    #[Route('/connect', name: 'connect', methods: ['POST'])]
    public function connect(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $userId = $this->getUser()?->getUserIdentifier();
        if ($userId === null) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $command = new ConnectExchange(
            userId: $userId,
            exchangeName: $data['exchangeName'] ?? '',
            apiKey: $data['apiKey'] ?? '',
            apiSecret: $data['apiSecret'] ?? '',
            label: $data['label'] ?? null
        );

        try {
            /** @var ExchangeConnectionDTO $result */
            $result = $this->handle($command);

            return new JsonResponse([
                'message' => 'Exchange connected successfully',
                'connection' => [
                    'id' => $result->id,
                    'exchangeName' => $result->exchangeName,
                    'label' => $result->label,
                    'isActive' => $result->isActive,
                    'connectedAt' => $result->connectedAt,
                ]
            ], Response::HTTP_CREATED);
        } catch (\DomainException $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to connect exchange'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get all exchange connections for the authenticated user
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $userId = $this->getUser()?->getUserIdentifier();
        if ($userId === null) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $connections = $this->connectionRepository->findByUserId(
            UserId::fromString($userId)
        );

        $connectionsData = array_map(
            fn($connection) => ExchangeConnectionDTO::fromAggregate($connection),
            $connections
        );

        return new JsonResponse([
            'connections' => $connectionsData
        ]);
    }

    /**
     * Disconnect from an exchange
     */
    #[Route('/{connectionId}', name: 'disconnect', methods: ['DELETE'])]
    public function disconnect(string $connectionId): JsonResponse
    {
        $userId = $this->getUser()?->getUserIdentifier();
        if ($userId === null) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $command = new DisconnectExchange(
            connectionId: $connectionId,
            userId: $userId
        );

        try {
            $this->handle($command);

            return new JsonResponse([
                'message' => 'Exchange disconnected successfully'
            ]);
        } catch (\DomainException $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to disconnect exchange'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
