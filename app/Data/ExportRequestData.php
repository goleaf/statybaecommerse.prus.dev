<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ExportFormat;
use App\Enums\ExportType;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\EnumType;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

final class ExportRequestData extends Data
{
    public function __construct(
        #[Required, EnumType(ExportType::class)]
        #[MapInputName('entity')]
        public ExportType $entity,
        #[Nullable, ArrayType]
        public ?array $filters,
        #[Nullable, ArrayType]
        public ?array $columns,
        #[Required, EnumType(ExportFormat::class)]
        public ExportFormat $format,
        #[Nullable, StringType]
        public ?string $locale,
        #[Nullable, StringType]
        public ?string $timezone,
        #[Nullable, ArrayType]
        #[MapInputName('ids')]
        public ?array $ids = null,
    ) {
        $this->filters ??= [];
        $this->columns ??= [];
    }

    public function normalizedLocale(): string
    {
        return $this->locale ?: app()->getLocale();
    }

    public function normalizedTimezone(): string
    {
        return $this->timezone ?: config('app.timezone');
    }
}
