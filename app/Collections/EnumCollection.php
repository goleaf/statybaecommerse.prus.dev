<?php

declare(strict_types=1);

namespace App\Collections;

use function array_map;

use BackedEnum;

use function class_basename;
use function collect;
use function enum_exists;

use Illuminate\Support\Collection;

use function implode;

use InvalidArgumentException;

use function is_array;
use function is_bool;
use function is_numeric;
use function is_scalar;
use function is_string;
use function method_exists;
use function sprintf;
use function str_replace;
use function strtoupper;
use function usort;

use ValueError;

/**
 * @extends Collection<int, BackedEnum>
 */
final class EnumCollection extends Collection
{
    /**
     * @param iterable<mixed> $items
     */
    public function __construct(iterable $items = [])
    {
        $validated = [];
        foreach ($items as $item) {
            if (! $item instanceof BackedEnum) {
                throw new InvalidArgumentException('EnumCollection expects only BackedEnum instances.');
            }

            $validated[] = $item;
        }

        parent::__construct($validated);
    }

    /**
     * @param class-string<BackedEnum> $enumClass
     */
    public static function fromEnum(string $enumClass): self
    {
        self::assertEnumClass($enumClass);

        /** @var array<int, BackedEnum> $cases */
        $cases = $enumClass::cases();

        return new self($cases);
    }

    /**
     * @param class-string<BackedEnum>  $enumClass
     * @param iterable<int, string|int> $values
     */
    public static function fromValues(string $enumClass, iterable $values): self
    {
        self::assertEnumClass($enumClass);

        $resolved = [];
        foreach ($values as $value) {
            try {
                $resolved[] = $enumClass::from($value);
            } catch (ValueError) {
                // Ignore invalid values silently.
            }
        }

        return new self($resolved);
    }

    /**
     * @param class-string<BackedEnum> $enumClass
     * @param iterable<int, string>    $labels
     */
    public static function fromLabels(string $enumClass, iterable $labels): self
    {
        self::assertEnumClass($enumClass);

        if (! method_exists($enumClass, 'fromLabel')) {
            throw new InvalidArgumentException(sprintf('Enum class %s does not support fromLabel().', $enumClass));
        }

        $resolved = [];
        foreach ($labels as $label) {
            $enum = $enumClass::fromLabel($label);
            if ($enum instanceof BackedEnum) {
                $resolved[] = $enum;
            }
        }

        return new self($resolved);
    }

    /**
     * @return array<int, string|int>
     */
    public function backingValues(): array
    {
        $values = [];
        foreach ($this->items as $enum) {
            $values[] = $enum->value;
        }

        return $values;
    }

    /**
     * @return array<int, string>
     */
    public function labels(): array
    {
        $labels = [];
        foreach ($this->items as $enum) {
            $labels[] = $this->stringFromEnum($enum, ['label', 'getLabel'], (string) $enum->value);
        }

        return $labels;
    }

    /**
     * @return array<int, string>
     */
    public function descriptions(): array
    {
        $descriptions = [];
        foreach ($this->items as $enum) {
            $descriptions[] = $this->stringFromEnum($enum, ['description', 'getDescription'], '');
        }

        return $descriptions;
    }

    /**
     * @return array<int, string>
     */
    public function icons(): array
    {
        $icons = [];
        foreach ($this->items as $enum) {
            $icons[] = $this->stringFromEnum($enum, ['icon', 'getIcon'], '');
        }

        return $icons;
    }

    /**
     * @return array<int, string>
     */
    public function colors(): array
    {
        $colors = [];
        foreach ($this->items as $enum) {
            $colors[] = $this->stringFromEnum($enum, ['color', 'getColor'], '');
        }

        return $colors;
    }

    /**
     * @return array<int, int>
     */
    public function priorities(): array
    {
        $priorities = [];
        foreach ($this->items as $enum) {
            $priorities[] = $this->intFromEnum($enum, ['priority', 'getPriority']);
        }

        return $priorities;
    }

    /**
     * @return array<string|int, string>
     */
    public function options(): array
    {
        $options = [];
        foreach ($this->items as $enum) {
            $options[$enum->value] = $this->stringFromEnum($enum, ['label', 'getLabel'], (string) $enum->value);
        }

        return $options;
    }

    /**
     * @return array<string|int, array<string, mixed>>
     */
    public function optionsWithDescriptions(): array
    {
        $options = [];
        foreach ($this->items as $enum) {
            $options[$enum->value] = $this->enumArray($enum);
        }

        return $options;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function toArrays(): array
    {
        $arrays = [];
        foreach ($this->items as $enum) {
            $arrays[] = $this->enumArray($enum);
        }

        return $arrays;
    }

    public function toJson($options = 0): string
    {
        return (string) json_encode($this->toArrays(), $options);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forApi(): array
    {
        $payload = [];
        foreach ($this->items as $enum) {
            $payload[] = [
                'value'       => $enum->value,
                'label'       => $this->stringFromEnum($enum, ['label', 'getLabel'], (string) $enum->value),
                'description' => $this->stringFromEnum($enum, ['description', 'getDescription'], ''),
                'icon'        => $this->stringFromEnum($enum, ['icon', 'getIcon'], ''),
                'color'       => $this->stringFromEnum($enum, ['color', 'getColor'], ''),
            ];
        }

        return $payload;
    }

    /**
     * @return array<int, array{name: string, value: string|int, description: string}>
     */
    public function forGraphQL(): array
    {
        $payload = [];
        foreach ($this->items as $enum) {
            $value = (string) $enum->value;
            $payload[] = [
                'name'        => strtoupper($value),
                'value'       => $enum->value,
                'description' => $this->stringFromEnum($enum, ['description', 'getDescription'], ''),
            ];
        }

        return $payload;
    }

    public function forTypeScript(): string
    {
        $first = $this->first();
        if (! $first instanceof BackedEnum) {
            return 'export enum Enum {}';
        }

        $lines = [sprintf('export enum %s {', class_basename($first))];
        foreach ($this->items as $enum) {
            $lines[] = sprintf("  %s = '%s',", strtoupper((string) $enum->value), $enum->value);
        }
        $lines[] = '}';

        return implode("\n", $lines);
    }

    public function forJavaScript(): string
    {
        $first = $this->first();
        if (! $first instanceof BackedEnum) {
            return 'const Enum = {}';
        }

        $lines = [sprintf('const %s = {', class_basename($first))];
        foreach ($this->items as $enum) {
            $lines[] = sprintf("  %s: '%s',", strtoupper((string) $enum->value), $enum->value);
        }
        $lines[] = '};';

        return implode("\n", $lines);
    }

    public function forCss(): string
    {
        $lines = [':root {'];
        foreach ($this->items as $enum) {
            $lines[] = sprintf("  --%s: '%s';", str_replace('_', '-', (string) $enum->value), $enum->value);
        }
        $lines[] = '}';

        return implode("\n", $lines);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forDocumentation(): array
    {
        $rows = [];
        foreach ($this->items as $enum) {
            $rows[] = [
                'value'       => $enum->value,
                'label'       => $this->stringFromEnum($enum, ['label', 'getLabel'], (string) $enum->value),
                'description' => $this->stringFromEnum($enum, ['description', 'getDescription'], ''),
                'icon'        => $this->stringFromEnum($enum, ['icon', 'getIcon'], ''),
                'color'       => $this->stringFromEnum($enum, ['color', 'getColor'], ''),
                'priority'    => $this->intFromEnum($enum, ['priority', 'getPriority']),
            ];
        }

        return $rows;
    }

    public function forValidation(): string
    {
        return 'in:' . implode(',', array_map(static fn (BackedEnum $enum): string => (string) $enum->value, $this->items));
    }

    public function forDatabase(): string
    {
        $values = array_map(static fn (BackedEnum $enum): string => (string) $enum->value, $this->items);

        return "enum('" . implode("','", $values) . "')";
    }

    public function filterBy(string $property, mixed $value): self
    {
        $filtered = [];
        foreach ($this->items as $enum) {
            if ($this->methodResult($enum, $property) === $value) {
                $filtered[] = $enum;
            }
        }

        return new self($filtered);
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function filterByMultiple(array $filters): self
    {
        $filtered = [];
        foreach ($this->items as $enum) {
            $matches = true;
            foreach ($filters as $property => $value) {
                if ($this->methodResult($enum, (string) $property) !== $value) {
                    $matches = false;
                    break;
                }
            }

            if ($matches) {
                $filtered[] = $enum;
            }
        }

        return new self($filtered);
    }

    public function sortByProperty(string $property, bool $descending = false): self
    {
        $items = $this->items;
        usort($items, function (BackedEnum $a, BackedEnum $b) use ($property, $descending): int {
            $left = $this->methodResult($a, $property);
            $right = $this->methodResult($b, $property);

            if (! is_scalar($left)) {
                $left = null;
            }
            if (! is_scalar($right)) {
                $right = null;
            }

            $comparison = $left <=> $right;

            return $descending ? -$comparison : $comparison;
        });

        return new self($items);
    }

    public function sortByPriority(bool $descending = false): self
    {
        return $this->sortByProperty('priority', $descending);
    }

    public function sortByLabel(bool $descending = false): self
    {
        return $this->sortByProperty('label', $descending);
    }

    public function sortByValue(bool $descending = false): self
    {
        $items = $this->items;
        usort($items, function (BackedEnum $a, BackedEnum $b) use ($descending): int {
            $comparison = $a->value <=> $b->value;

            return $descending ? -$comparison : $comparison;
        });

        return new self($items);
    }

    /**
     * @return Collection<string, self>
     */
    public function groupByProperty(string $property): Collection
    {
        $groups = [];
        foreach ($this->items as $enum) {
            $key = $this->methodResult($enum, $property);
            $groups[$this->normalizeGroupKey($key)][] = $enum;
        }

        return collect($groups)->map(fn (array $items): self => new self($items));
    }

    /**
     * @return Collection<string, self>
     */
    public function groupByColor(): Collection
    {
        return $this->groupByProperty('color');
    }

    /**
     * @return Collection<string, self>
     */
    public function groupByPriority(): Collection
    {
        return $this->groupByProperty('priority');
    }

    public function whereProperty(string $property, mixed $value): self
    {
        return $this->filterBy($property, $value);
    }

    public function whereColor(string $color): self
    {
        return $this->whereProperty('color', $color);
    }

    public function wherePriority(int $priority): self
    {
        return $this->whereProperty('priority', $priority);
    }

    public function wherePriorityGreaterThan(int $priority): self
    {
        $filtered = [];
        foreach ($this->items as $enum) {
            $result = $this->methodResult($enum, 'priority');
            if (is_numeric($result) && (int) $result > $priority) {
                $filtered[] = $enum;
            }
        }

        return new self($filtered);
    }

    public function wherePriorityLessThan(int $priority): self
    {
        $filtered = [];
        foreach ($this->items as $enum) {
            $result = $this->methodResult($enum, 'priority');
            if (is_numeric($result) && (int) $result < $priority) {
                $filtered[] = $enum;
            }
        }

        return new self($filtered);
    }

    public function wherePriorityBetween(int $min, int $max): self
    {
        $filtered = [];
        foreach ($this->items as $enum) {
            $result = $this->methodResult($enum, 'priority');
            if (is_numeric($result)) {
                $value = (int) $result;
                if ($value >= $min && $value <= $max) {
                    $filtered[] = $enum;
                }
            }
        }

        return new self($filtered);
    }

    public function whereIcon(string $icon): self
    {
        return $this->whereProperty('icon', $icon);
    }

    public function whereLabel(string $label): self
    {
        return $this->whereProperty('label', $label);
    }

    public function whereValue(string $value): self
    {
        $filtered = [];
        foreach ($this->items as $enum) {
            if ((string) $enum->value === $value) {
                $filtered[] = $enum;
            }
        }

        return new self($filtered);
    }

    public function whereDescription(string $description): self
    {
        return $this->whereProperty('description', $description);
    }

    private static function assertEnumClass(string $enumClass): void
    {
        if (! enum_exists($enumClass)) {
            throw new InvalidArgumentException(sprintf('Enum class %s not found.', $enumClass));
        }

        if (! is_subclass_of($enumClass, BackedEnum::class)) {
            throw new InvalidArgumentException(sprintf('Enum class %s must extend BackedEnum.', $enumClass));
        }
    }

    /**
     * @param array<int, string> $methods
     */
    private function stringFromEnum(BackedEnum $enum, array $methods, string $default): string
    {
        foreach ($methods as $method) {
            $result = $this->methodResult($enum, $method);
            if (is_string($result) && $result !== '') {
                return $result;
            }
        }

        return $default;
    }

    /**
     * @param array<int, string> $methods
     */
    private function intFromEnum(BackedEnum $enum, array $methods, int $default = 0): int
    {
        foreach ($methods as $method) {
            $result = $this->methodResult($enum, $method);
            if (is_numeric($result)) {
                return (int) $result;
            }
        }

        return $default;
    }

    /**
     * @return array<string, mixed>
     */
    private function enumArray(BackedEnum $enum): array
    {
        $result = $this->methodResult($enum, 'toArray');
        if (is_array($result)) {
            $normalized = [];
            foreach ($result as $key => $value) {
                $normalized[(string) $key] = $value;
            }

            return $normalized;
        }

        return [
            'value' => $enum->value,
            'label' => $this->stringFromEnum($enum, ['label', 'getLabel'], (string) $enum->value),
        ];
    }

    private function normalizeGroupKey(mixed $key): string
    {
        if ($key === null) {
            return '__null__';
        }

        if (is_bool($key)) {
            return $key ? 'true' : 'false';
        }

        if (is_scalar($key)) {
            return (string) $key;
        }

        return '__non_scalar__';
    }

    private function methodResult(BackedEnum $enum, string $method): mixed
    {
        if (! method_exists($enum, $method)) {
            return null;
        }

        return $enum->{$method}();
    }
}
