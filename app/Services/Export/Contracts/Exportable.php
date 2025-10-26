<?php

declare(strict_types=1);

namespace App\Services\Export\Contracts;

use App\Models\Export;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface Exportable
{
    /**
     * Human friendly export name.
     */
    public function name(): string;

    /**
     * @return array<string, \App\Services\Export\ExportColumn>
     */
    public function columns(): array;

    /**
     * Default columns used when the requester does not provide explicit selection.
     *
     * @return array<int, string>
     */
    public function defaultColumns(): array;

    /**
     * Configure the source query for the export.
     *
     * @param array<string, mixed> $options
     */
    public function query(array $options = []): Builder;

    /**
     * Provide the base filename (without extension) for the generated artifact.
     */
    public function fileName(Export $export): string;

    /**
     * Resolve a single row into a serializable array using the provided columns.
     *
     * @param  array<string, \App\Services\Export\ExportColumn> $columns
     * @return array<int, string>
     */
    public function map(Model $model, array $columns): array;
}
