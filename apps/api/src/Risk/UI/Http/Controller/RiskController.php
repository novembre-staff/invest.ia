<?php

declare(strict_types=1);

namespace App\Risk\UI\Http\Controller;

use App\Risk\Application\Command\CreateRiskProfile;
use App\Risk\Application\Command\UpdateRiskLimits;
use App\Risk\Application\Query\GetCurrentExposure;
use App\Risk\Application\Query\GetRiskAssessment;
use App\Risk\Application\Query\GetRiskProfile;
use App\Shared\UI\Http\HandleTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/risk')]
class RiskController
{
    use HandleTrait;

    #[Route('/profile', methods: ['GET'])]
    public function getRiskProfile(Request $request): JsonResponse
    {
        try {
            // TODO: Get from authentication
            $userId = $request->query->get('userId');

            $query = new GetRiskProfile($userId);
            $profile = $this->handleQuery($query);

            if (!$profile) {
                return new JsonResponse(['error' => 'Risk profile not found'], Response::HTTP_NOT_FOUND);
            }

            return new JsonResponse($profile);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/profile', methods: ['POST'])]
    public function createRiskProfile(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // TODO: Get from authentication
            $userId = $data['userId'] ?? null;

            $command = new CreateRiskProfile(
                userId: $userId,
                riskLevel: $data['riskLevel'],
                maxPositionSizePercent: $data['maxPositionSizePercent'] ?? null,
                maxPortfolioExposurePercent: $data['maxPortfolioExposurePercent'] ?? null,
                maxDailyLossPercent: $data['maxDailyLossPercent'] ?? null,
                maxDrawdownPercent: $data['maxDrawdownPercent'] ?? null,
                maxLeverage: $data['maxLeverage'] ?? 1.0,
                maxConcentrationPercent: $data['maxConcentrationPercent'] ?? null,
                maxTradesPerDay: $data['maxTradesPerDay'] ?? null,
                requireApprovalAboveLimit: $data['requireApprovalAboveLimit'] ?? true,
                notes: $data['notes'] ?? null
            );

            $profile = $this->handleCommand($command);

            return new JsonResponse($profile, Response::HTTP_CREATED);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/profile/{id}/limits', methods: ['PUT'])]
    public function updateRiskLimits(string $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // TODO: Get from authentication
            $userId = $data['userId'] ?? null;

            $command = new UpdateRiskLimits(
                profileId: $id,
                userId: $userId,
                maxPositionSizePercent: $data['maxPositionSizePercent'] ?? null,
                maxPortfolioExposurePercent: $data['maxPortfolioExposurePercent'] ?? null,
                maxDailyLossPercent: $data['maxDailyLossPercent'] ?? null,
                maxDrawdownPercent: $data['maxDrawdownPercent'] ?? null,
                maxLeverage: $data['maxLeverage'] ?? null,
                maxConcentrationPercent: $data['maxConcentrationPercent'] ?? null,
                maxTradesPerDay: $data['maxTradesPerDay'] ?? null
            );

            $profile = $this->handleCommand($command);

            return new JsonResponse($profile);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/assessment', methods: ['GET'])]
    public function getRiskAssessment(Request $request): JsonResponse
    {
        try {
            // TODO: Get from authentication
            $userId = $request->query->get('userId');

            $query = new GetRiskAssessment($userId);
            $assessment = $this->handleQuery($query);

            return new JsonResponse($assessment);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/exposure', methods: ['GET'])]
    public function getCurrentExposure(Request $request): JsonResponse
    {
        try {
            // TODO: Get from authentication
            $userId = $request->query->get('userId');

            $query = new GetCurrentExposure($userId);
            $exposure = $this->handleQuery($query);

            return new JsonResponse($exposure);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
