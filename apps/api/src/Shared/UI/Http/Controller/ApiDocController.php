<?php

declare(strict_types=1);

namespace App\Shared\UI\Http\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
final class ApiDocController extends AbstractController
{
    /**
     * Health check endpoint
     * 
     * @Route("/health", name="health", methods={"GET"})
     */
    #[Route('/health', name: 'health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'healthy',
            'timestamp' => time(),
            'version' => '1.0.0'
        ]);
    }

    /**
     * API information
     * 
     * @Route("/info", name="info", methods={"GET"})
     */
    #[Route('/info', name: 'info', methods: ['GET'])]
    public function info(): JsonResponse
    {
        return new JsonResponse([
            'name' => 'invest.ia API',
            'version' => '1.0.0',
            'description' => 'Intelligent crypto trading platform with automated bots and sentiment analysis',
            'features' => [
                'authentication' => 'JWT with MFA support',
                'trading' => 'Binance integration with automated bots',
                'news' => 'Sentiment analysis with NLP',
                'notifications' => 'Multi-channel (Email, Push, SMS, Discord, Telegram)',
                'realtime' => 'WebSocket updates via Mercure'
            ],
            'endpoints' => [
                'documentation' => '/api/doc',
                'openapi' => '/api/doc.json'
            ]
        ]);
    }

    /**
     * API documentation (Swagger UI)
     * 
     * @Route("/doc", name="documentation", methods={"GET"})
     */
    #[Route('/doc', name: 'documentation', methods: ['GET'])]
    public function documentation(): Response
    {
        // TODO: Intégrer Swagger UI
        return new Response('<html><body><h1>API Documentation</h1><p>Swagger UI à implémenter</p></body></html>');
    }

    /**
     * OpenAPI specification
     * 
     * @Route("/doc.json", name="openapi_spec", methods={"GET"})
     */
    #[Route('/doc.json', name: 'openapi_spec', methods: ['GET'])]
    public function openApiSpec(): JsonResponse
    {
        // TODO: Générer spec OpenAPI complète
        $spec = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'invest.ia API',
                'version' => '1.0.0',
                'description' => 'RESTful API for intelligent crypto trading',
                'contact' => [
                    'name' => 'API Support',
                    'email' => 'support@invest.ia'
                ]
            ],
            'servers' => [
                [
                    'url' => 'http://localhost:8000/api',
                    'description' => 'Development server'
                ]
            ],
            'paths' => [
                '/health' => [
                    'get' => [
                        'summary' => 'Health check',
                        'responses' => [
                            '200' => [
                                'description' => 'API is healthy'
                            ]
                        ]
                    ]
                ]
                // TODO: Ajouter tous les endpoints
            ]
        ];

        return new JsonResponse($spec);
    }
}
