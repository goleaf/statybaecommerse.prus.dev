<?php

declare(strict_types=1);

namespace App\Services\Filament;

use Illuminate\Support\Collection;

final class FilamentResourceFixer
{
    /**
     * @return Collection<int, string>
     */
    public function getCriticalResources(): Collection
    {
        return collect();
    }

    public function fixResources(Collection $resources, bool $isDryRun): FilamentResourceFixResult
    {
        $rows = $resources
            ->values()
            ->map(static fn (string $resource): array => [
                $resource,
                $isDryRun ? 'dry-run' : 'skipped',
                0,
            ])
            ->all();

        return new FilamentResourceFixResult(
            processedCount: $resources->count(),
            fixedCount: 0,
            errors: [],
            rows: $rows,
        );
    }
}

final class FilamentResourceFixResult
{
    /**
     * @param array<int, string>                 $errors
     * @param array<int, array<int, int|string>> $rows
     */
    public function __construct(
        private readonly int $processedCount,
        private readonly int $fixedCount,
        private readonly array $errors,
        private readonly array $rows
    ) {}

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    /**
     * @return array<int, array<int, int|string>>
     */
    public function toTableRows(): array
    {
        return $this->rows;
    }

    /**
     * @return array<int, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getProcessedCount(): int
    {
        return $this->processedCount;
    }

    public function getFixedCount(): int
    {
        return $this->fixedCount;
    }

    public function getErrorCount(): int
    {
        return count($this->errors);
    }
}
