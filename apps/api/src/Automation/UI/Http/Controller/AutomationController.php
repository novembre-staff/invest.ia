<?php

declare(strict_types=1);

namespace App\Automation\UI\Http\Controller;

use App\Automation\Application\Command\ActivateAutomation;
use App\Automation\Application\Command\CreateAutomation;
use App\Automation\Application\Command\PauseAutomation;
use App\Automation\Application\Command\StopAutomation;
use App\Automation\Application\Command\UpdateAutomation;
use App\Automation\Application\Query\GetAutomation;
use App\Automation\Application\Query\GetUserAutomations;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class AutomationController
{
    use HandleTrait;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function getUserAutomations(Request $request): JsonResponse
    {
        try {
            // TODO: Get userId from authenticated user
            $userId = $request->query->get('user_id');

            $query = new GetUserAutomations($userId);
            $automations = $this->handle($query);

            return new JsonResponse($automations, Response::HTTP_OK);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAutomation(string $id, Request $request): JsonResponse
    {
        try {
            // TODO: Get userId from authenticated user
            $userId = $request->query->get('user_id');

            $query = new GetAutomation($id, $userId);
            $automation = $this->handle($query);

            if ($automation === null) {
                return new JsonResponse(['error' => 'Automation not found'], Response::HTTP_NOT_FOUND);
            }

            return new JsonResponse($automation, Response::HTTP_OK);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createAutomation(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // TODO: Get userId from authenticated user
            $userId = $data['user_id'];

            $command = new CreateAutomation(
                userId: $userId,
                name: $data['name'],
                type: $data['type'],
                symbol: $data['symbol'],
                interval: $data['interval'] ?? null,
                dcaConfig: $data['dca_config'] ?? null,
                gridConfig: $data['grid_config'] ?? null,
                parameters: $data['parameters'] ?? []
            );

            $automation = $this->handle($command);

            return new JsonResponse($automation, Response::HTTP_CREATED);
        } catch (\InvalidArgumentException|\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateAutomation(string $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // TODO: Get userId from authenticated user
            $userId = $data['user_id'];

            $command = new UpdateAutomation(
                automationId: $id,
                userId: $userId,
                name: $data['name'],
                interval: $data['interval'] ?? null,
                dcaConfig: $data['dca_config'] ?? null,
                gridConfig: $data['grid_config'] ?? null,
                parameters: $data['parameters'] ?? []
            );

            $automation = $this->handle($command);

            return new JsonResponse($automation, Response::HTTP_OK);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function activateAutomation(string $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // TODO: Get userId from authenticated user
            $userId = $data['user_id'];

            $command = new ActivateAutomation($id, $userId);
            $automation = $this->handle($command);

            return new JsonResponse($automation, Response::HTTP_OK);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function pauseAutomation(string $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // TODO: Get userId from authenticated user
            $userId = $data['user_id'];

            $command = new PauseAutomation($id, $userId);
            $automation = $this->handle($command);

            return new JsonResponse($automation, Response::HTTP_OK);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function stopAutomation(string $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // TODO: Get userId from authenticated user
            $userId = $data['user_id'];

            $command = new StopAutomation($id, $userId);
            $automation = $this->handle($command);

            return new JsonResponse($automation, Response::HTTP_OK);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
