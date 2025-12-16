<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

/**
 * Rate limiting subscriber pour protéger l'API
 */
final class RateLimitSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RateLimiterFactory $apiGeneralLimiter
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        // Skip pour routes publiques (login, register)
        $route = $request->attributes->get('_route');
        if ($this->isPublicRoute($route)) {
            return;
        }

        // Utilise l'IP comme identifiant
        $identifier = $request->getClientIp() ?? 'unknown';

        $limiter = $this->apiGeneralLimiter->create($identifier);
        $limit = $limiter->consume(1);

        // Ajoute les headers rate limit
        $response = $event->getResponse();
        if ($response) {
            $response->headers->set('X-RateLimit-Remaining', (string) $limit->getRemainingTokens());
            $response->headers->set('X-RateLimit-Limit', '100');
            $response->headers->set('X-RateLimit-Reset', (string) $limit->getRetryAfter()->getTimestamp());
        }

        // Si limite dépassée
        if (!$limit->isAccepted()) {
            $event->setResponse(new JsonResponse([
                'error' => [
                    'code' => 'RATE_LIMIT_EXCEEDED',
                    'message' => 'Too many requests. Please try again later.',
                    'retry_after' => $limit->getRetryAfter()->getTimestamp()
                ]
            ], 429));
        }
    }

    private function isPublicRoute(?string $route): bool
    {
        $publicRoutes = [
            'api_auth_register',
            'api_auth_login',
            'api_health_check',
        ];

        return $route !== null && in_array($route, $publicRoutes, true);
    }
}
