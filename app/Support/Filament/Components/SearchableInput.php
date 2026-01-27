<?php

declare(strict_types=1);

namespace App\Support\Filament\Components;

use Filament\Forms\Components\Select;

class SearchableInput extends Select
{
    /**
     * @var array<string, mixed>
     */
    protected array $payload = [];

    /**
     * @var array<string, mixed>
     */
    protected array $meta = [];

    public function payload(array $payload): static
    {
        $this->payload = $payload;

        return $this;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function meta(string $key, mixed $value): static
    {
        $this->meta[$key] = $value;

        return $this;
    }

    public function getMeta(array|string|null $keys = null): mixed
    {
        if ($keys === null) {
            return $this->meta;
        }

        if (is_array($keys)) {
            $values = [];

            foreach ($keys as $key) {
                $values[$key] = $this->meta[$key] ?? null;
            }

            return $values;
        }

        return $this->meta[$keys] ?? null;
    }

    public function getSearchResultsUsing(?\Closure $callback): static
    {
        return parent::getSearchResultsUsing($callback);
    }

    public function searchUsing(?\Closure $callback): static
    {
        return $this->getSearchResultsUsing($callback);
    }

    /**
     * Helper to satisfy legacy searchable input method signature.
     */
    public function hydrateStateUsing(?callable $callback): static
    {
        return $this->afterStateHydrated($callback);
    }
}
