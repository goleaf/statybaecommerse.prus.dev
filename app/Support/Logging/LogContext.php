<?php

declare(strict_types=1);

namespace App\Support\Logging;

use Illuminate\Contracts\Support\Arrayable;

final class LogContext implements Arrayable
{
    private ?string $correlationId = null;

    private ?string $requestId = null;

    private string|int|null $userId = null;

    private ?string $commandName = null;

    /** @var array<string, mixed> */
    private array $additional = [];

    public function setCorrelationId(?string $correlationId): void
    {
        $this->correlationId = $correlationId;
    }

    public function correlationId(): ?string
    {
        return $this->correlationId;
    }

    public function setRequestId(?string $requestId): void
    {
        $this->requestId = $requestId;
    }

    public function requestId(): ?string
    {
        return $this->requestId;
    }

    public function setUserId(string|int|null $userId): void
    {
        $this->userId = $userId;
    }

    public function userId(): string|int|null
    {
        return $this->userId;
    }

    public function setCommandName(?string $commandName): void
    {
        $this->commandName = $commandName;
    }

    public function commandName(): ?string
    {
        return $this->commandName;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function merge(array $context): void
    {
        $this->additional = array_merge($this->additional, $context);
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $context = [
            'correlation_id' => $this->correlationId,
            'request_id'     => $this->requestId,
            'user_id'        => $this->userId,
        ];

        if ($this->commandName !== null) {
            $context['command'] = $this->commandName;
        }

        foreach ($this->additional as $key => $value) {
            if ($value === null) {
                continue;
            }

            $context[$key] = $value;
        }

        return array_filter(
            $context,
            static fn ($value): bool => $value !== null && $value !== ''
        );
    }
}
