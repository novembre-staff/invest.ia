<?php

declare(strict_types=1);

namespace App\Risk\UI\Http\Controller;

use App\Risk\Application\Command\ActivateGlobalKillSwitch;
use App\Risk\Application\Command\ActivateBotKillSwitch;
use App\Strategy\Domain\ValueObject\TradingStrategyId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/risk/kill-switch', name: 'risk_kill_switch_')]
class KillSwitchController extends AbstractController
{
    use HandleTrait;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    /**
     * Activate global kill switch - stops ALL bots and cancels ALL orders
     */
    #[Route('/global', name: 'global', methods: ['POST'])]
    public function activateGlobal(Request $request): JsonResponse
    {
        // TODO: Get user ID from authentication
        $userId = $request->request->get('userId', 'test-user-id');
        $data = json_decode($request->getContent(), true);

        $reason = $data['reason'] ?? 'Manual activation by user';

        try {
            $command = new ActivateGlobalKillSwitch(
                userId: $userId,
                reason: $reason
            );

            $this->handle($command);

            return new JsonResponse([
                'success' => true,
                'message' => '🚨 Global kill switch activated - all bots stopped, all orders cancelled',
                'reason' => $reason
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to activate global kill switch: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Activate kill switch for specific bot
     */
    #[Route('/bot/{botId}', name: 'bot', methods: ['POST'])]
    public function activateBotKillSwitch(string $botId, Request $request): JsonResponse
    {
        // TODO: Get user ID from authentication
        $userId = $request->request->get('userId', 'test-user-id');
        $data = json_decode($request->getContent(), true);

        $reason = $data['reason'] ?? 'Manual activation by user';

        try {
            $command = new ActivateBotKillSwitch(
                botId: TradingStrategyId::fromString($botId),
                userId: $userId,
                reason: $reason
            );

            $this->handle($command);

            return new JsonResponse([
                'success' => true,
                'message' => "🛑 Bot kill switch activated for bot {$botId}",
                'bot_id' => $botId,
                'reason' => $reason
            ]);
        } catch (\DomainException $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to activate bot kill switch: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
