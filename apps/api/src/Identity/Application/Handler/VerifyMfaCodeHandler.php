<?php

declare(strict_types=1);

namespace App\Identity\Application\Handler;

use App\Identity\Application\Command\VerifyMfaCode;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\Service\TotpService;
use App\Identity\Domain\ValueObject\UserId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class VerifyMfaCodeHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly TotpService $totpService
    ) {}

    /**
     * Vérifie un code MFA pour un utilisateur
     * 
     * @return bool True si le code est valide
     */
    public function __invoke(VerifyMfaCode $command): bool
    {
        $userId = UserId::fromString($command->userId);
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            throw new \DomainException('User not found.');
        }

        if (!$user->isMfaEnabled()) {
            throw new \DomainException('MFA is not enabled for this user.');
        }

        $mfaSecret = $user->getMfaSecret();
        if (!$mfaSecret) {
            throw new \DomainException('MFA secret not found.');
        }

        // Vérifier le code TOTP
        return $this->totpService->verifyCode($mfaSecret, $command->code);
    }
}
