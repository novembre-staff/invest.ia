<?php

declare(strict_types=1);

namespace App\Identity\Application\Handler;

use App\Identity\Application\Command\AuthenticateUser;
use App\Identity\Domain\Event\UserLoggedIn;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\Service\TotpService;
use App\Identity\Domain\ValueObject\Email;
use App\Shared\Application\Service\PasswordHasherFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class AuthenticateUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherFactory $passwordHasherFactory,
        private readonly MessageBusInterface $eventBus,
        private readonly TotpService $totpService
    ) {}

    public function __invoke(AuthenticateUser $command): array
    {
        $email = new Email($command->email);
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new \DomainException('Invalid credentials.');
        }

        // Vérifier le mot de passe
        $hasher = $this->passwordHasherFactory->createHasher($user);
        if (!$hasher->verify($user->getPassword()->getHash(), $command->password)) {
            throw new \DomainException('Invalid credentials.');
        }

        // Vérifier si l'utilisateur est actif
        if (!$user->getStatus()->isActive()) {
            throw new \DomainException('Account is not active. Status: ' . $user->getStatus()->value);
        }

        // Si MFA activée, vérifier le code
        if ($user->isMfaEnabled()) {
            if (!$command->mfaCode) {
                return [
                    'requiresMfa' => true,
                    'userId' => $user->getId()->getValue()
                ];
            }

            // Vérifier le code TOTP
            $mfaSecret = $user->getMfaSecret();
            if (!$mfaSecret || !$this->totpService->verifyCode($mfaSecret, $command->mfaCode)) {
                throw new \DomainException('Invalid MFA code.');
            }
        }

        // Dispatch domain event
        $this->eventBus->dispatch(new UserLoggedIn(
            $user->getId(),
            $user->getEmail(),
            new \DateTimeImmutable(),
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ));

        return [
            'userId' => $user->getId()->getValue(),
            'email' => $user->getEmail()->getValue(),
            'requiresMfa' => false
        ];
    }
}
