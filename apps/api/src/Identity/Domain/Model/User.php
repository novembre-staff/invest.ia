<?php

declare(strict_types=1);

namespace App\Identity\Domain\Model;

use App\Identity\Domain\ValueObject\Email;
use App\Identity\Domain\ValueObject\UserId;
use App\Identity\Domain\ValueObject\HashedPassword;
use App\Identity\Domain\ValueObject\UserPreferences;
use App\Identity\Domain\ValueObject\UserStatus;

class User
{
    private UserId $id;
    private Email $email;
    private HashedPassword $password;
    private string $firstName;
    private string $lastName;
    private UserStatus $status;
    private bool $mfaEnabled;
    private ?string $mfaSecret;
    private ?\DateTimeImmutable $emailVerifiedAt;
    private UserPreferences $preferences;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;
    
    public function __construct(
        UserId $id,
        Email $email,
        HashedPassword $password,
        string $firstName,
        string $lastName
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->firstName = trim($firstName);
        $this->lastName = trim($lastName);
        $this->status = UserStatus::PENDING_VERIFICATION;
        $this->mfaEnabled = false;
        $this->mfaSecret = null;
        $this->emailVerifiedAt = null;
        $this->preferences = UserPreferences::default();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        
        $this->validateNames();
    }
    
    private function validateNames(): void
    {
        if (empty($this->firstName) || strlen($this->firstName) > 100) {
            throw new \InvalidArgumentException('First name must be between 1 and 100 characters');
        }
        
        if (empty($this->lastName) || strlen($this->lastName) > 100) {
            throw new \InvalidArgumentException('Last name must be between 1 and 100 characters');
        }
    }
    
    public function verifyEmail(): void
    {
        if (!$this->status->isPendingVerification()) {
            throw new \DomainException('User email is already verified or account is not in pending state');
        }
        
        $this->status = UserStatus::ACTIVE;
        $this->emailVerifiedAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }
    
    public function enableMfa(string $secret): void
    {
        if ($this->mfaEnabled) {
            throw new \DomainException('MFA is already enabled');
        }
        
        if (!$this->status->isActive()) {
            throw new \DomainException('Cannot enable MFA on non-active account');
        }
        
        $this->mfaEnabled = true;
        $this->mfaSecret = $secret;
        $this->updatedAt = new \DateTimeImmutable();
    }
    
    public function disableMfa(): void
    {
        if (!$this->mfaEnabled) {
            throw new \DomainException('MFA is already disabled');
        }
        
        $this->mfaEnabled = false;
        $this->mfaSecret = null;
        $this->updatedAt = new \DateTimeImmutable();
    }
    
    public function suspend(): void
    {
        if ($this->status->isSuspended()) {
            throw new \DomainException('User is already suspended');
        }
        
        $this->status = UserStatus::SUSPENDED;
        $this->updatedAt = new \DateTimeImmutable();
    }
    
    public function activate(): void
    {
        if ($this->status->isActive()) {
            throw new \DomainException('User is already active');
        }
        
        if ($this->status->isDeleted()) {
            throw new \DomainException('Cannot activate deleted user');
        }
        
        $this->status = UserStatus::ACTIVE;
        $this->updatedAt = new \DateTimeImmutable();
    }
    
    public function delete(): void
    {
        $this->status = UserStatus::DELETED;
        $this->updatedAt = new \DateTimeImmutable();
    }
    
    public function changePassword(HashedPassword $newPassword): void
    {
        $this->password = $newPassword;
        $this->updatedAt = new \DateTimeImmutable();
    }
    
    // Getters
    
    public function getId(): UserId
    {
        return $this->id;
    }
    
    public function getEmail(): Email
    {
        return $this->email;
    }
    
    public function getPassword(): HashedPassword
    {
        return $this->password;
    }
    
    public function getFirstName(): string
    {
        return $this->firstName;
    }
    
    public function getLastName(): string
    {
        return $this->lastName;
    }
    
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }
    
    public function getStatus(): UserStatus
    {
        return $this->status;
    }
    
    public function isMfaEnabled(): bool
    {
        return $this->mfaEnabled;
    }
    
    public function getMfaSecret(): ?string
    {
        return $this->mfaSecret;
    }
    
    public function getEmailVerifiedAt(): ?\DateTimeImmutable
    {
        return $this->emailVerifiedAt;
    }
    
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
    
    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
    
    public function isEmailVerified(): bool
    {
        return $this->emailVerifiedAt !== null;
    }
    
    public function isActive(): bool
    {
        return $this->status->isActive();
    }
    
    public function getPreferences(): UserPreferences
    {
        return $this->preferences;
    }
    
    /**
     * Mettre à jour les préférences utilisateur
     * 
     * @param UserPreferences $preferences Nouvelles préférences
     */
    public function updatePreferences(UserPreferences $preferences): void
    {
        $this->preferences = $preferences;
        $this->updatedAt = new \DateTimeImmutable();
    }
}
