<?php

declare(strict_types=1);

namespace App\Analytics\UI\Http\Controller;

use App\Analytics\Application\Command\GenerateReport;
use App\Analytics\Application\Query\GetPerformanceReport;
use App\Analytics\Application\Query\GetPortfolioStatistics;
use App\Analytics\Application\Query\GetUserReports;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class AnalyticsController
{
    use HandleTrait;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function getStatistics(Request $request): JsonResponse
    {
        try {
            // TODO: Get userId from authenticated user
            $userId = $request->query->get('user_id');
            $period = $request->query->get('period', '30d');

            $query = new GetPortfolioStatistics($userId, $period);
            $statistics = $this->handle($query);

            return new JsonResponse($statistics, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getUserReports(Request $request): JsonResponse
    {
        try {
            // TODO: Get userId from authenticated user
            $userId = $request->query->get('user_id');
            $type = $request->query->get('type');
            $limit = (int) $request->query->get('limit', 20);

            $query = new GetUserReports($userId, $type, $limit);
            $reports = $this->handle($query);

            return new JsonResponse($reports, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getReport(string $id, Request $request): JsonResponse
    {
        try {
            // TODO: Get userId from authenticated user
            $userId = $request->query->get('user_id');

            $query = new GetPerformanceReport($id, $userId);
            $report = $this->handle($query);

            if ($report === null) {
                return new JsonResponse(['error' => 'Report not found'], Response::HTTP_NOT_FOUND);
            }

            return new JsonResponse($report, Response::HTTP_OK);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function generateReport(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // TODO: Get userId from authenticated user
            $userId = $data['user_id'];

            $command = new GenerateReport(
                userId: $userId,
                type: $data['type'],
                period: $data['period'] ?? '30d',
                startDate: $data['start_date'] ?? null,
                endDate: $data['end_date'] ?? null
            );

            $report = $this->handle($command);

            return new JsonResponse($report, Response::HTTP_CREATED);
        } catch (\InvalidArgumentException|\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
