<?php

declare(strict_types=1);

namespace App\Identity\UI\Http\Controller;

use App\Identity\Application\Command\AuthenticateUser;
use App\Identity\Application\Command\DisableMfa;
use App\Identity\Application\Command\EnableMfa;
use App\Identity\Application\Command\LogoutUser;
use App\Identity\Application\Command\RegisterUser;
use App\Identity\Application\Command\VerifyMfaCode;
use App\Identity\Application\Command\VerifyUserEmail;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly TokenStorageInterface $tokenStorage
    ) {}
    
    #[Route('/register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!is_array($data)) {
            return new JsonResponse(
                ['error' => 'Invalid JSON payload'],
                Response::HTTP_BAD_REQUEST
            );
        }
        
        // Validation des champs requis
        $requiredFields = ['email', 'password', 'firstName', 'lastName'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                return new JsonResponse(
                    ['error' => sprintf('Missing required field: %s', $field)],
                    Response::HTTP_BAD_REQUEST
                );
            }
        }
        
        try {
            $command = new RegisterUser(
                email: $data['email'],
                password: $data['password'],
                firstName: $data['firstName'],
                lastName: $data['lastName']
            );
            
            $envelope = $this->commandBus->dispatch($command);
            $handledStamp = $envelope->last(HandledStamp::class);
            
            if (!$handledStamp) {
                throw new \RuntimeException('Command was not handled');
            }
            
            /** @var \App\Identity\Application\DTO\UserDTO $userDTO */
            $userDTO = $handledStamp->getResult();
            
            return new JsonResponse([
                'userId' => $userDTO->id,
                'email' => $userDTO->email,
                'firstName' => $userDTO->firstName,
                'lastName' => $userDTO->lastName,
                'status' => $userDTO->status,
                'mfaEnabled' => $userDTO->mfaEnabled,
                'emailVerified' => $userDTO->emailVerified
            ], Response::HTTP_CREATED);
            
        } catch (\DomainException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_CONFLICT
            );
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            // Log error in production
            return new JsonResponse(
                ['error' => 'An error occurred during registration'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
    
    #[Route('/verify-email/{userId}/{token}', methods: ['GET'])]
    public function verifyEmail(string $userId, string $token): JsonResponse
    {
        try {
            $command = new VerifyUserEmail($userId, $token);
            
            $this->commandBus->dispatch($command);
            
            return new JsonResponse([
                'message' => 'Email verified successfully',
                'userId' => $userId
            ], Response::HTTP_OK);
            
        } catch (\DomainException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'An error occurred during email verification'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
    
    #[Route('/login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!is_array($data)) {
            return new JsonResponse(
                ['error' => 'Invalid JSON payload'],
                Response::HTTP_BAD_REQUEST
            );
        }
        
        // Validation des champs requis
        if (!isset($data['email']) || !isset($data['password'])) {
            return new JsonResponse(
                ['error' => 'Email and password are required'],
                Response::HTTP_BAD_REQUEST
            );
        }
        
        try {
            $command = new AuthenticateUser(
                email: $data['email'],
                password: $data['password'],
                mfaCode: $data['mfaCode'] ?? null
            );
            
            $envelope = $this->commandBus->dispatch($command);
            $handledStamp = $envelope->last(HandledStamp::class);
            
            if (!$handledStamp) {
                throw new \RuntimeException('Command was not handled');
            }
            
            $result = $handledStamp->getResult();
            
            // Si MFA requise, retourner sans token
            if ($result['requiresMfa'] ?? false) {
                return new JsonResponse([
                    'requiresMfa' => true,
                    'userId' => $result['userId'],
                    'message' => 'MFA code required'
                ], Response::HTTP_OK);
            }
            
            // Créer le token JWT
            $token = $this->jwtManager->createFromPayload([
                'email' => $result['email'],
                'userId' => $result['userId']
            ]);
            
            return new JsonResponse([
                'token' => $token,
                'userId' => $result['userId'],
                'email' => $result['email']
            ], Response::HTTP_OK);
            
        } catch (\DomainException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_UNAUTHORIZED
            );
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'An error occurred during login'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
    
    #[Route('/logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        try {
            $user = $this->getUser();
            
            if (!$user) {
                return new JsonResponse(
                    ['error' => 'Not authenticated'],
                    Response::HTTP_UNAUTHORIZED
                );
            }
            
            $command = new LogoutUser(userId: $user->getId());
            $this->commandBus->dispatch($command);
            
            // Clear token
            $this->tokenStorage->setToken(null);
            
            return new JsonResponse([
                'message' => 'Logged out successfully'
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'An error occurred during logout'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
    
    #[Route('/mfa/enable', methods: ['POST'])]
    public function enableMfa(): JsonResponse
    {
        try {
            $user = $this->getUser();
            
            if (!$user) {
                return new JsonResponse(
                    ['error' => 'Not authenticated'],
                    Response::HTTP_UNAUTHORIZED
                );
            }
            
            $command = new EnableMfa(userId: $user->getId());
            
            $envelope = $this->commandBus->dispatch($command);
            $handledStamp = $envelope->last(HandledStamp::class);
            
            if (!$handledStamp) {
                throw new \RuntimeException('Command was not handled');
            }
            
            $result = $handledStamp->getResult();
            
            return new JsonResponse([
                'message' => 'MFA enabled successfully',
                'secret' => $result['secret'],
                'qrCodeUri' => $result['qrCodeUri'],
                'qrCodeUrl' => $result['qrCodeUrl']
            ], Response::HTTP_OK);
            
        } catch (\DomainException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'An error occurred while enabling MFA'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
    
    #[Route('/mfa/disable', methods: ['POST'])]
    public function disableMfa(): JsonResponse
    {
        try {
            $user = $this->getUser();
            
            if (!$user) {
                return new JsonResponse(
                    ['error' => 'Not authenticated'],
                    Response::HTTP_UNAUTHORIZED
                );
            }
            
            $command = new DisableMfa(userId: $user->getId());
            $this->commandBus->dispatch($command);
            
            return new JsonResponse([
                'message' => 'MFA disabled successfully'
            ], Response::HTTP_OK);
            
        } catch (\DomainException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'An error occurred while disabling MFA'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
    
    #[Route('/mfa/verify', methods: ['POST'])]
    public function verifyMfa(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!is_array($data)) {
            return new JsonResponse(
                ['error' => 'Invalid JSON payload'],
                Response::HTTP_BAD_REQUEST
            );
        }
        
        if (!isset($data['code']) || empty(trim($data['code']))) {
            return new JsonResponse(
                ['error' => 'MFA code is required'],
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
            
            $command = new VerifyMfaCode(
                userId: $user->getId(),
                code: trim($data['code'])
            );
            
            $envelope = $this->commandBus->dispatch($command);
            $handledStamp = $envelope->last(HandledStamp::class);
            
            if (!$handledStamp) {
                throw new \RuntimeException('Command was not handled');
            }
            
            $isValid = $handledStamp->getResult();
            
            if ($isValid) {
                return new JsonResponse([
                    'valid' => true,
                    'message' => 'MFA code verified successfully'
                ], Response::HTTP_OK);
            } else {
                return new JsonResponse([
                    'valid' => false,
                    'error' => 'Invalid MFA code'
                ], Response::HTTP_UNAUTHORIZED);
            }
            
        } catch (\DomainException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'An error occurred while verifying MFA code'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
