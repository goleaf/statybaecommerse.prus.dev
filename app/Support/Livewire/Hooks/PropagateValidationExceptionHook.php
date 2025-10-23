<?php

declare(strict_types=1);

namespace App\Support\Livewire\Hooks;

use Illuminate\Validation\ValidationException;
use Livewire\ComponentHook;
use Throwable;

final class PropagateValidationExceptionHook extends ComponentHook
{
    public function exception(Throwable $exception, callable $stopPropagation): void
    {
        // Re-throw throttling validation errors before the core Livewire hook swallows them
        // so that automated tests can observe the 429 response in a deterministic way.
        if ($exception instanceof ValidationException && ($exception->status ?? null) === 429) {
            throw $exception;
        }
    }
}
