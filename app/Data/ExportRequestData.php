<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ExportType;
use Spatie\LaravelData\Data;

final class ExportRequestData extends Data
{
    public function __construct(
        public ?string $name = null,
        public ?string $exportable = null,
        public ?string $format = null,
        public array $columns = [],
        public array $filters = [],
        public array $recordIds = [],
        public ?int $userId = null,
        public array $meta = [],
        public null|string|ExportType $entity = null,
        public array $ids = [],
        public ?string $locale = null,
        public ?string $timezone = null,
    ) {
    }

    public function toPayload(): array
    {
        return [
            'name' => $this->name,
            'exportable' => $this->exportable,
            'entity' => $this->entityEnum()?->value,
            'format' => $this->normalizedFormat(),
            'columns' => $this->requestedColumns(),
            'filters' => $this->filters,
            'record_ids' => $this->recordIdentifiers(),
            'user_id' => $this->userId,
            'meta' => $this->metadata(),
        ];
    }

    public function normalizedFormat(): string
    {
        $format = strtolower((string) ($this->format ?? 'csv'));

        return in_array($format, ['csv', 'xlsx', 'pdf'], true) ? $format : 'csv';
    }

    /**
     * @return array<int, string>
     */
    public function requestedColumns(): array
    {
        return array_values(array_unique(array_map(
            static fn ($column): string => (string) $column,
            $this->columns ?? [],
        )));
    }

    /**
     * @return array<int, int|string>
     */
    public function recordIdentifiers(): array
    {
        $candidates = $this->recordIds;

        if ($candidates === [] && $this->ids !== []) {
            $candidates = $this->ids;
        }

        return array_values(array_filter(
            $candidates,
            static fn ($value): bool => $value !== null && $value !== '',
        ));
    }

    public function entityEnum(): ?ExportType
    {
        if ($this->entity instanceof ExportType) {
            return $this->entity;
        }

        if (is_string($this->entity)) {
            return ExportType::tryFrom($this->entity);
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        $extras = array_filter([
            'locale' => $this->locale,
            'timezone' => $this->timezone,
        ], static fn ($value): bool => $value !== null && $value !== '');

        if ($this->meta === []) {
            return $extras;
        }

        return array_merge($extras, $this->meta);
    }
}
