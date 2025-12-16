<?php

declare(strict_types=1);

namespace App\Audit\UI\Http\Controller;

use App\Audit\Application\Query\GetAuditLogs;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/audit', name: 'audit_')]
class AuditController extends AbstractController
{
    use HandleTrait;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    #[Route('/logs', name: 'logs', methods: ['GET'])]
    public function getLogs(Request $request): JsonResponse
    {
        $userId = $this->getUser()?->getUserIdentifier();
        if ($userId === null) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // Admin-only endpoint - vérifier permissions
        // TODO: Implement role-based access control

        try {
            $query = new GetAuditLogs(
                userId: $request->query->get('user_id'),
                action: $request->query->get('action'),
                entityType: $request->query->get('entity_type'),
                entityId: $request->query->get('entity_id'),
                severity: $request->query->get('severity'),
                startDate: $request->query->get('start_date'),
                endDate: $request->query->get('end_date'),
                limit: (int) $request->query->get('limit', 100)
            );

            $logs = $this->handle($query);

            return new JsonResponse([
                'logs' => $logs,
                'total' => count($logs)
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'Failed to fetch audit logs'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/logs/user', name: 'user_logs', methods: ['GET'])]
    public function getUserLogs(Request $request): JsonResponse
    {
        $userId = $this->getUser()?->getUserIdentifier();
        if ($userId === null) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $query = new GetAuditLogs(
                userId: $userId,
                startDate: $request->query->get('start_date'),
                endDate: $request->query->get('end_date'),
                limit: (int) $request->query->get('limit', 50)
            );

            $logs = $this->handle($query);

            return new JsonResponse([
                'logs' => $logs,
                'total' => count($logs)
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'Failed to fetch user audit logs'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
