<?php

declare(strict_types=1);

namespace App\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Illuminate\Contracts\Support\Arrayable;

final class CheckboxMatrix extends Field
{
    protected string $view = 'forms.components.checkbox-matrix';

    /** @var array<string, string>|Closure */
    protected array|Closure $rows = [];

    /** @var array<string, string>|Closure */
    protected array|Closure $columns = [];

    protected bool $rowSelectRequired = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->default(fn (): array => []);

        $this->rule('array');

        $this->afterStateHydrated(function (CheckboxMatrix $component, mixed $state): void {
            $component->state($component->normalizeState($state));
        });

        $this->dehydrateStateUsing(function (CheckboxMatrix $component, mixed $state): array {
            return $component->normalizeState($state);
        });

        $this->rule(function (CheckboxMatrix $component) {
            return function (string $attribute, mixed $value, Closure $fail) use ($component): void {
                if (! $component->isRowSelectRequired()) {
                    return;
                }

                $normalized = $component->normalizeState($value);

                foreach ($component->getRows() as $rowKey => $label) {
                    $hasSelection = false;

                    foreach ($component->getColumns() as $columnKey => $_columnLabel) {
                        if (! empty($normalized[$rowKey][$columnKey])) {
                            $hasSelection = true;
                            break;
                        }
                    }

                    if (! $hasSelection) {
                        $fail(trans('validation.required', [
                            'attribute' => sprintf('%s (%s)', $component->getLabel() ?? $attribute, $label),
                        ]));
                    }
                }
            };
        });
    }

    /**
     * @param  array<string, string>|Closure  $rows
     */
    public function rows(array|Closure $rows): self
    {
        $this->rows = $rows;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getRows(): array
    {
        $rows = $this->evaluate($this->rows);

        if ($rows instanceof Arrayable) {
            $rows = $rows->toArray();
        }

        return (array) $rows;
    }

    /**
     * @param  array<string, string>|Closure  $columns
     */
    public function columns(array|Closure $columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getColumns(): array
    {
        $columns = $this->evaluate($this->columns);

        if ($columns instanceof Arrayable) {
            $columns = $columns->toArray();
        }

        return (array) $columns;
    }

    public function rowSelectRequired(bool $required = true): self
    {
        $this->rowSelectRequired = $required;

        return $this;
    }

    public function isRowSelectRequired(): bool
    {
        return $this->rowSelectRequired;
    }

    /**
     * @return array<string, array<string, bool>>
     */
    public function normalizeState(mixed $state): array
    {
        $normalized = [];
        $rows = $this->getRows();
        $columns = $this->getColumns();

        foreach ($rows as $rowKey => $_label) {
            $rowState = [];

            if (is_array($state) && array_key_exists($rowKey, $state)) {
                $rowState = is_array($state[$rowKey]) ? $state[$rowKey] : [];
            }

            foreach ($columns as $columnKey => $_columnLabel) {
                $value = $rowState[$columnKey] ?? false;
                $normalized[$rowKey][$columnKey] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }

            if ($normalized[$rowKey] === []) {
                $normalized[$rowKey] = [];
            }
        }

        return $normalized;
    }
}
