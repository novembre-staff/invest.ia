<?php

declare(strict_types=1);

namespace App\Alert\UI\Http\Controller;

use App\Alert\Application\Command\CancelPriceAlert;
use App\Alert\Application\Command\CreatePriceAlert;
use App\Alert\Application\Query\GetAlertById;
use App\Alert\Application\Query\GetUserAlerts;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/alerts', name: 'alerts_')]
class AlertController extends AbstractController
{
    use HandleTrait;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    /**
     * Get user's alerts
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function getUserAlerts(Request $request): JsonResponse
    {
        // TODO: Get user ID from authentication
        $userId = $request->query->get('userId', 'test-user-id');
        $activeOnly = $request->query->getBoolean('activeOnly', false);

        try {
            $query = new GetUserAlerts($userId, $activeOnly);
            $alerts = $this->handle($query);

            return new JsonResponse([
                'alerts' => $alerts,
                'total' => count($alerts)
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to fetch alerts'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get specific alert by ID
     */
    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function getAlert(string $id, Request $request): JsonResponse
    {
        // TODO: Get user ID from authentication
        $userId = $request->query->get('userId', 'test-user-id');

        try {
            $query = new GetAlertById($id, $userId);
            $alert = $this->handle($query);

            return new JsonResponse($alert);
        } catch (\DomainException $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to fetch alert'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create new price alert
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function createAlert(Request $request): JsonResponse
    {
        // TODO: Get user ID from authentication
        $userId = $request->request->get('userId', 'test-user-id');

        try {
            $data = json_decode($request->getContent(), true);

            $command = new CreatePriceAlert(
                userId: $userId,
                type: $data['type'],
                symbol: $data['symbol'] ?? null,
                targetValue: (float)$data['targetValue'],
                notificationChannels: $data['notificationChannels'],
                message: $data['message'] ?? null,
                expiresAt: $data['expiresAt'] ?? null
            );

            $alert = $this->handle($command);

            return new JsonResponse($alert, Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to create alert'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Cancel an alert
     */
    #[Route('/{id}', name: 'cancel', methods: ['DELETE'])]
    public function cancelAlert(string $id, Request $request): JsonResponse
    {
        // TODO: Get user ID from authentication
        $userId = $request->query->get('userId', 'test-user-id');

        try {
            $command = new CancelPriceAlert($id, $userId);
            $this->handle($command);

            return new JsonResponse([
                'message' => 'Alert cancelled successfully'
            ]);
        } catch (\DomainException $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to cancel alert'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
