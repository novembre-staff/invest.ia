<?php

declare(strict_types=1);

namespace App\Trading\UI\Http\Controller;

use App\Trading\Application\Command\CancelOrder;
use App\Trading\Application\Command\PlaceOrder;
use App\Trading\Application\Query\GetOrderById;
use App\Trading\Application\Query\GetUserOrders;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/orders', name: 'orders_')]
class OrderController extends AbstractController
{
    use HandleTrait;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    /**
     * Get user's orders
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function getUserOrders(Request $request): JsonResponse
    {
        // TODO: Get user ID from authentication
        $userId = $request->query->get('userId', 'test-user-id');
        $symbol = $request->query->get('symbol');
        $activeOnly = $request->query->getBoolean('activeOnly', false);
        $limit = min((int)$request->query->get('limit', 50), 200);

        try {
            $query = new GetUserOrders($userId, $symbol, $activeOnly, $limit);
            $orders = $this->handle($query);

            return new JsonResponse([
                'orders' => $orders,
                'total' => count($orders)
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to fetch orders'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get specific order by ID
     */
    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function getOrder(string $id, Request $request): JsonResponse
    {
        // TODO: Get user ID from authentication
        $userId = $request->query->get('userId', 'test-user-id');

        try {
            $query = new GetOrderById($id, $userId);
            $order = $this->handle($query);

            return new JsonResponse($order);
        } catch (\DomainException $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to fetch order'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Place a new order
     */
    #[Route('', name: 'place', methods: ['POST'])]
    public function placeOrder(Request $request): JsonResponse
    {
        // TODO: Get user ID from authentication
        $userId = $request->request->get('userId', 'test-user-id');

        try {
            $data = json_decode($request->getContent(), true);

            $command = new PlaceOrder(
                userId: $userId,
                exchangeConnectionId: $data['exchangeConnectionId'],
                symbol: $data['symbol'],
                type: $data['type'],
                side: $data['side'],
                quantity: (float)$data['quantity'],
                price: isset($data['price']) ? (float)$data['price'] : null,
                stopPrice: isset($data['stopPrice']) ? (float)$data['stopPrice'] : null,
                timeInForce: $data['timeInForce'] ?? 'GTC'
            );

            $order = $this->handle($command);

            return new JsonResponse($order, Response::HTTP_CREATED);
        } catch (\InvalidArgumentException | \DomainException $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to place order'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Cancel an order
     */
    #[Route('/{id}', name: 'cancel', methods: ['DELETE'])]
    public function cancelOrder(string $id, Request $request): JsonResponse
    {
        // TODO: Get user ID from authentication
        $userId = $request->query->get('userId', 'test-user-id');
        $exchangeConnectionId = $request->query->get('exchangeConnectionId');

        if (!$exchangeConnectionId) {
            return new JsonResponse([
                'error' => 'Exchange connection ID is required'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $command = new CancelOrder($id, $userId, $exchangeConnectionId);
            $this->handle($command);

            return new JsonResponse([
                'message' => 'Order cancelled successfully'
            ]);
        } catch (\DomainException $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to cancel order'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
