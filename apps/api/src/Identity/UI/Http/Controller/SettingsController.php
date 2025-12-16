<?php

declare(strict_types=1);

namespace App\Identity\UI\Http\Controller;

use App\Identity\Application\Command\UpdateUserPreferences;
use App\Identity\Domain\ValueObject\UserPreferences;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/settings')]
class SettingsController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus
    ) {}
    
    #[Route('/preferences', methods: ['GET'])]
    public function getPreferences(): JsonResponse
    {
        try {
            $user = $this->getUser();
            
            if (!$user) {
                return new JsonResponse(
                    ['error' => 'Not authenticated'],
                    Response::HTTP_UNAUTHORIZED
                );
            }
            
            // Récupérer les préférences depuis le user
            // Note: On devrait récupérer le User domain object, mais pour simplifier
            // on va dispatcher une query ou récupérer depuis le repository
            
            // Pour l'instant, retourner les valeurs par défaut en exemple
            // TODO: Récupérer les vraies préférences du user via repository
            
            return new JsonResponse([
                'reportingCurrency' => 'USD',
                'timezone' => 'UTC',
                'language' => 'en',
                'emailNotifications' => true,
                'pushNotifications' => true,
                'tradingAlerts' => true,
                'newsAlerts' => true,
                'theme' => 'auto',
                'soundEnabled' => true,
                'availableCurrencies' => UserPreferences::getSupportedCurrencies(),
                'availableLanguages' => UserPreferences::getSupportedLanguages(),
                'availableTimezones' => UserPreferences::getSupportedTimezones()
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'An error occurred while retrieving preferences'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
    
    #[Route('/preferences', methods: ['PUT'])]
    public function updatePreferences(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!is_array($data)) {
            return new JsonResponse(
                ['error' => 'Invalid JSON payload'],
                Response::HTTP_BAD_REQUEST
            );
        }
        
        try {
            $user = $this->getUser();
            
            if (!$user) {
                return new JsonResponse(
                    ['error' => 'Not authenticated'],
                    Response::HTTP_UNAUTHORIZED
                );
            }
            
            $command = new UpdateUserPreferences(
                userId: $user->getId(),
                reportingCurrency: $data['reportingCurrency'] ?? null,
                timezone: $data['timezone'] ?? null,
                language: $data['language'] ?? null,
                emailNotifications: $data['emailNotifications'] ?? null,
                pushNotifications: $data['pushNotifications'] ?? null,
                tradingAlerts: $data['tradingAlerts'] ?? null,
                newsAlerts: $data['newsAlerts'] ?? null,
                theme: $data['theme'] ?? null,
                soundEnabled: $data['soundEnabled'] ?? null
            );
            
            $envelope = $this->commandBus->dispatch($command);
            $handledStamp = $envelope->last(HandledStamp::class);
            
            if (!$handledStamp) {
                throw new \RuntimeException('Command was not handled');
            }
            
            $preferencesDTO = $handledStamp->getResult();
            
            return new JsonResponse([
                'message' => 'Preferences updated successfully',
                'preferences' => [
                    'reportingCurrency' => $preferencesDTO->reportingCurrency,
                    'timezone' => $preferencesDTO->timezone,
                    'language' => $preferencesDTO->language,
                    'emailNotifications' => $preferencesDTO->emailNotifications,
                    'pushNotifications' => $preferencesDTO->pushNotifications,
                    'tradingAlerts' => $preferencesDTO->tradingAlerts,
                    'newsAlerts' => $preferencesDTO->newsAlerts,
                    'theme' => $preferencesDTO->theme,
                    'soundEnabled' => $preferencesDTO->soundEnabled
                ]
            ], Response::HTTP_OK);
            
        } catch (\DomainException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'An error occurred while updating preferences'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
