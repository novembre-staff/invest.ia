<?php

declare(strict_types=1);

namespace App\Bots\UI\Http\Controller;

use App\Bots\Application\Command\AcceptProposal;
use App\Bots\Application\Command\CreateProposal;
use App\Bots\Application\Command\RejectProposal;
use App\Bots\Application\DTO\ProposalDTO;
use App\Bots\Domain\Repository\ProposalRepositoryInterface;
use App\Bots\Domain\ValueObject\ProposalId;
use App\Bots\Domain\ValueObject\ProposalStatus;
use App\Identity\Domain\ValueObject\UserId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/bots/proposals', name: 'bot_proposals_')]
class ProposalController extends AbstractController
{
    use HandleTrait;

    public function __construct(
        MessageBusInterface $commandBus,
        private readonly ProposalRepositoryInterface $proposalRepository
    ) {
        $this->messageBus = $commandBus;
    }

    /**
     * Liste des propositions pour l'utilisateur connecté
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $userId = $this->getUser()?->getUserIdentifier();
        if ($userId === null) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $statusFilter = $request->query->get('status');
        $status = $statusFilter ? ProposalStatus::tryFrom($statusFilter) : null;

        $proposals = $this->proposalRepository->findByUserId(
            UserId::fromString($userId),
            $status,
            50
        );

        $proposalsData = array_map(
            fn($proposal) => ProposalDTO::fromAggregate($proposal),
            $proposals
        );

        $pendingCount = $this->proposalRepository->countPendingByUserId(
            UserId::fromString($userId)
        );

        return new JsonResponse([
            'proposals' => $proposalsData,
            'total' => count($proposalsData),
            'pendingCount' => $pendingCount
        ]);
    }

    /**
     * Détail d'une proposition
     */
    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        $userId = $this->getUser()?->getUserIdentifier();
        if ($userId === null) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $proposal = $this->proposalRepository->findById(ProposalId::fromString($id));

            if (!$proposal) {
                return new JsonResponse(['error' => 'Proposal not found'], Response::HTTP_NOT_FOUND);
            }

            // Vérifier ownership
            if ($proposal->getUserId()->getValue() !== $userId) {
                return new JsonResponse(['error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
            }

            return new JsonResponse([
                'proposal' => ProposalDTO::fromAggregate($proposal)
            ]);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => 'Invalid proposal ID'], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Créer une proposition (appelé par les bots/strategies)
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $userId = $this->getUser()?->getUserIdentifier();
        if ($userId === null) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid JSON payload'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $command = new CreateProposal(
                userId: $userId,
                strategyId: $data['strategyId'] ?? '',
                symbol: $data['symbol'] ?? '',
                side: $data['side'] ?? '',
                quantity: $data['quantity'] ?? '',
                rationale: $data['rationale'] ?? '',
                riskFactors: $data['riskFactors'] ?? [],
                riskScore: $data['riskScore'] ?? '',
                limitPrice: $data['limitPrice'] ?? null,
                expectedReturn: $data['expectedReturn'] ?? null,
                stopLoss: $data['stopLoss'] ?? null,
                takeProfit: $data['takeProfit'] ?? null,
                expirationMinutes: $data['expirationMinutes'] ?? 30
            );

            /** @var ProposalDTO $proposalDTO */
            $proposalDTO = $this->handle($command);

            return new JsonResponse([
                'message' => 'Proposal created successfully',
                'proposal' => $proposalDTO
            ], Response::HTTP_CREATED);
        } catch (\DomainException | \InvalidArgumentException $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to create proposal'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * UC-031: Accepter une proposition
     */
    #[Route('/{id}/accept', name: 'accept', methods: ['POST'])]
    public function accept(string $id): JsonResponse
    {
        $userId = $this->getUser()?->getUserIdentifier();
        if ($userId === null) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $command = new AcceptProposal(
                proposalId: $id,
                userId: $userId
            );

            $result = $this->handle($command);

            return new JsonResponse([
                'message' => 'Proposal accepted successfully',
                'orderId' => $result['orderId'],
                'proposalId' => $result['proposalId']
            ], Response::HTTP_OK);
        } catch (\DomainException $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to accept proposal'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * UC-032: Refuser une proposition
     */
    #[Route('/{id}/reject', name: 'reject', methods: ['POST'])]
    public function reject(string $id, Request $request): JsonResponse
    {
        $userId = $this->getUser()?->getUserIdentifier();
        if ($userId === null) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        $reason = is_array($data) ? ($data['reason'] ?? '') : '';

        try {
            $command = new RejectProposal(
                proposalId: $id,
                userId: $userId,
                reason: $reason
            );

            $this->handle($command);

            return new JsonResponse([
                'message' => 'Proposal rejected successfully'
            ], Response::HTTP_OK);
        } catch (\DomainException $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to reject proposal'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
