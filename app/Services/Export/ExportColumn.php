<?php

declare(strict_types=1);

namespace App\Services\Export;

use Closure;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use Stringable;

final class ExportColumn
{
    /**
     * @param Closure(Model): mixed|null $resolver
     */
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly ?string $attribute = null,
        private readonly ?Closure $resolver = null,
    ) {
        if ($this->attribute === null && $this->resolver === null) {
            throw new InvalidArgumentException('Export columns require either an attribute path or a resolver.');
        }
    }

    public function resolve(Model $model): string
    {
        $value = $this->resolver !== null
            ? ($this->resolver)($model)
            : data_get($model, $this->attribute);

        if ($value instanceof Stringable) {
            $value = $value->__toString();
        }

        if ($value instanceof Carbon) {
            return $value->toIso8601String();
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('c');
        }

        if (is_bool($value)) {
            return $value ? __('Yes') : __('No');
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
        }

        if ($value === null) {
            return '';
        }

        return (string) $value;
    }
}
