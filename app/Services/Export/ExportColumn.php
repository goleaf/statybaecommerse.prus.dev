<?php

declare(strict_types=1);

namespace App\Services\Export;

use Closure;
use Illuminate\Database\Eloquent\Model;

final class ExportColumn
{
    /**
     * @param  Closure(Model): mixed  $resolver
     */
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        private readonly Closure $resolver,
    ) {}

    public function resolve(Model $record): mixed
    {
        return ($this->resolver)($record);
    }
}
