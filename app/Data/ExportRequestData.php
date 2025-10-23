<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\MaxItems;
use Spatie\LaravelData\Attributes\Validation\MinItems;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

/**
 * @property array<int, string> $columns
 */
final class ExportRequestData extends Data
{
    /**
     * @param  array<int, string>  $columns
     * @param  array<string, mixed>  $filters
     * @param  array<int, int|string>  $recordIds
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        #[Required, StringType]
        public string $name,
        #[Required, StringType]
        public string $exportable,
        #[Required, StringType, In(['csv', 'xlsx', 'pdf'])]
        public string $format,
        #[Required, ArrayType, MinItems(1)]
        public array $columns,
        #[ArrayType]
        public array $filters = [],
        #[ArrayType]
        public array $recordIds = [],
        #[Nullable]
        public ?int $userId = null,
        #[ArrayType, MaxItems(50)]
        public array $meta = [],
    ) {
    }

    /**
     * Prepare sanitized payload for persistence.
     *
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'name' => $this->name,
            'exportable' => $this->exportable,
            'format' => strtolower($this->format),
            'columns' => array_values(array_unique($this->columns)),
            'filters' => $this->filters,
            'record_ids' => array_values($this->recordIds),
            'user_id' => $this->userId,
            'meta' => $this->meta,
        ];
    }
}
