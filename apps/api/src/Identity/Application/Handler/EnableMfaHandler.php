<?php

declare(strict_types=1);

namespace App\Identity\Application\Handler;

use App\Identity\Application\Command\EnableMfa;
use App\Identity\Domain\Event\MfaEnabled;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\Service\TotpService;
use App\Identity\Domain\ValueObject\UserId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class EnableMfaHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly TotpService $totpService,
        private readonly MessageBusInterface $eventBus
    ) {}

    /**
     * Active le MFA pour un utilisateur
     * Génère un nouveau secret TOTP et retourne l'URI de provisioning
     * 
     * @return array{secret: string, qrCodeUri: string, qrCodeUrl: string}
     */
    public function __invoke(EnableMfa $command): array
    {
        $userId = UserId::fromString($command->userId);
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            throw new \DomainException('User not found.');
        }

        if ($user->isMfaEnabled()) {
            throw new \DomainException('MFA is already enabled for this user.');
        }

        // Générer le secret TOTP
        $totpData = $this->totpService->generateSecret($user->getEmail()->getValue());

        // Activer MFA sur le user aggregate
        $user->enableMfa($totpData['secret']);

        // Sauvegarder
        $this->userRepository->save($user);

        // Dispatch domain event
        $this->eventBus->dispatch(new MfaEnabled(
            $user->getId(),
            $user->getEmail(),
            new \DateTimeImmutable()
        ));

        // Retourner les infos nécessaires pour configurer l'app TOTP
        return [
            'secret' => $totpData['secret'],
            'qrCodeUri' => $totpData['qrCodeUri'],
            'qrCodeUrl' => $this->totpService->getQrCodeUrl($totpData['qrCodeUri'])
        ];
    }
}
