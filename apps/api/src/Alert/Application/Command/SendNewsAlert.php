<?php

declare(strict_types=1);

namespace App\Alert\Application\Command;

final class SendNewsAlert
{
    /**
     * @param string[] $channels
     */
    public function __construct(
        private readonly string $newsId,
        private readonly string $userId,
        private readonly array $channels
    ) {
    }

    public function newsId(): string
    {
        return $this->newsId;
    }

    public function userId(): string
    {
        return $this->userId;
    }

    /**
     * @return string[]
     */
    public function channels(): array
    {
        return $this->channels;
    }
}
