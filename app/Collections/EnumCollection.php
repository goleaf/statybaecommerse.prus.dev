<?php

declare (strict_types=1);
namespace App\Collections;

use Illuminate\Support\Collection;
/**
 * EnumCollection
 * 
 * Custom collection class for EnumCollection data manipulation with enhanced methods and type safety.
 * 
 */
final class EnumCollection extends Collection
{
    /**
     * Initialize the class instance with required dependencies.
     * @param mixed $items
     */
    public function __construct($items = [])
    {
        parent::__construct($items);
    }
    /**
     * Handle fromEnum functionality with proper error handling.
     * @param string $enumClass
     * @return self
     */
    public static function fromEnum(string $enumClass): self
    {
        if (!class_exists($enumClass)) {
            throw new \InvalidArgumentException("Enum class '{$enumClass}' not found");
        }
        return new self($enumClass::cases());
    }
    /**
     * Handle fromValues functionality with proper error handling.
     * @param string $enumClass
     * @param array $values
     * @return self
     */
    public static function fromValues(string $enumClass, array $values): self
    {
        if (!class_exists($enumClass)) {
            throw new \InvalidArgumentException("Enum class '{$enumClass}' not found");
        }
        $enums = [];
        foreach ($values as $value) {
            try {
                $enums[] = $enumClass::from($value);
            } catch (\ValueError $e) {
                // Skip invalid values
            }
        }
        return new self($enums);
    }
    /**
     * Handle fromLabels functionality with proper error handling.
     * @param string $enumClass
     * @param array $labels
     * @return self
     */
    public static function fromLabels(string $enumClass, array $labels): self
    {
        if (!class_exists($enumClass)) {
            throw new \InvalidArgumentException("Enum class '{$enumClass}' not found");
        }
        $enums = [];
        foreach ($labels as $label) {
            $enum = $enumClass::fromLabel($label);
            if ($enum) {
                $enums[] = $enum;
            }
        }
        return new self($enums);
    }
    /**
     * Handle values functionality with proper error handling.
     * @return array
     */
    public function values(): array
    {
        return $this->map(fn($enum) => $enum->value)->toArray();
    }
    /**
     * Handle labels functionality with proper error handling.
     * @return array
     */
    public function labels(): array
    {
        return $this->map(fn($enum) => $enum->label())->toArray();
    }
    /**
     * Handle descriptions functionality with proper error handling.
     * @return array
     */
    public function descriptions(): array
    {
        return $this->map(fn($enum) => $enum->description())->toArray();
    }
    /**
     * Handle icons functionality with proper error handling.
     * @return array
     */
    public function icons(): array
    {
        return $this->map(fn($enum) => $enum->icon())->toArray();
    }
    /**
     * Handle colors functionality with proper error handling.
     * @return array
     */
    public function colors(): array
    {
        return $this->map(fn($enum) => $enum->color())->toArray();
    }
    /**
     * Handle priorities functionality with proper error handling.
     * @return array
     */
    public function priorities(): array
    {
        return $this->map(fn($enum) => $enum->priority())->toArray();
    }
    /**
     * Handle options functionality with proper error handling.
     * @return array
     */
    public function options(): array
    {
        return $this->mapWithKeys(fn($enum) => [$enum->value => $enum->label()])->toArray();
    }
    /**
     * Handle optionsWithDescriptions functionality with proper error handling.
     * @return array
     */
    public function optionsWithDescriptions(): array
    {
        return $this->mapWithKeys(fn($enum) => [$enum->value => $enum->toArray()])->toArray();
    }
    /**
     * Handle toArrays functionality with proper error handling.
     * @return array
     */
    public function toArrays(): array
    {
        return $this->map(fn($enum) => $enum->toArray())->toArray();
    }
    /**
     * Convert the instance to a JSON representation.
     * @param mixed $options
     * @return string
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArrays(), $options);
    }
    /**
     * Handle forApi functionality with proper error handling.
     * @return array
     */
    public function forApi(): array
    {
        return $this->map(fn($enum) => ['value' => $enum->value, 'label' => $enum->label(), 'description' => $enum->description(), 'icon' => $enum->icon(), 'color' => $enum->color()])->toArray();
    }
    /**
     * Handle forGraphQL functionality with proper error handling.
     * @return array
     */
    public function forGraphQL(): array
    {
        return $this->map(fn($enum) => ['name' => strtoupper($enum->value), 'value' => $enum->value, 'description' => $enum->description()])->toArray();
    }
    /**
     * Handle forTypeScript functionality with proper error handling.
     * @return string
     */
    public function forTypeScript(): string
    {
        $enumName = class_basename($this->first()::class);
        $enum = "export enum {$enumName} {\n";
        foreach ($this as $enumCase) {
            $enum .= '  ' . strtoupper($enumCase->value) . " = '" . $enumCase->value . "',\n";
        }
        return $enum . '}';
    }
    /**
     * Handle forJavaScript functionality with proper error handling.
     * @return string
     */
    public function forJavaScript(): string
    {
        $enumName = class_basename($this->first()::class);
        $object = "const {$enumName} = {\n";
        foreach ($this as $enumCase) {
            $object .= '  ' . strtoupper($enumCase->value) . ": '" . $enumCase->value . "',\n";
        }
        return $object . '};';
    }
    /**
     * Handle forCss functionality with proper error handling.
     * @return string
     */
    public function forCss(): string
    {
        $css = ":root {\n";
        foreach ($this as $enumCase) {
            $css .= '  --' . str_replace('_', '-', $enumCase->value) . ": '" . $enumCase->value . "';\n";
        }
        return $css . '}';
    }
    /**
     * Handle forDocumentation functionality with proper error handling.
     * @return array
     */
    public function forDocumentation(): array
    {
        return $this->map(fn($enum) => ['value' => $enum->value, 'label' => $enum->label(), 'description' => $enum->description(), 'icon' => $enum->icon(), 'color' => $enum->color(), 'priority' => $enum->priority()])->toArray();
    }
    /**
     * Handle forValidation functionality with proper error handling.
     * @return string
     */
    public function forValidation(): string
    {
        return 'in:' . implode(',', $this->values());
    }
    /**
     * Handle forDatabase functionality with proper error handling.
     * @return string
     */
    public function forDatabase(): string
    {
        return "enum('" . implode("','", $this->values()) . "')";
    }
    /**
     * Handle filterBy functionality with proper error handling.
     * @param string $property
     * @param mixed $value
     * @return self
     */
    public function filterBy(string $property, mixed $value): self
    {
        return $this->filter(fn($enum) => $enum->{$property}() === $value);
    }
    /**
     * Handle filterByMultiple functionality with proper error handling.
     * @param array $filters
     * @return self
     */
    public function filterByMultiple(array $filters): self
    {
        return $this->filter(function ($enum) use ($filters) {
            foreach ($filters as $property => $value) {
                if ($enum->{$property}() !== $value) {
                    return false;
                }
            }
            return true;
        });
    }
    /**
     * Handle sortByProperty functionality with proper error handling.
     * @param string $property
     * @param bool $descending
     * @return self
     */
    public function sortByProperty(string $property, bool $descending = false): self
    {
        $sorted = $this->sortBy(fn($enum) => $enum->{$property}());
        return $descending ? $sorted->reverse() : $sorted;
    }
    /**
     * Handle sortByPriority functionality with proper error handling.
     * @param bool $descending
     * @return self
     */
    public function sortByPriority(bool $descending = false): self
    {
        return $this->sortByProperty('priority', $descending);
    }
    /**
     * Handle sortByLabel functionality with proper error handling.
     * @param bool $descending
     * @return self
     */
    public function sortByLabel(bool $descending = false): self
    {
        return $this->sortByProperty('label', $descending);
    }
    /**
     * Handle sortByValue functionality with proper error handling.
     * @param bool $descending
     * @return self
     */
    public function sortByValue(bool $descending = false): self
    {
        return $this->sortBy(fn($enum) => $enum->value, $descending);
    }
    /**
     * Handle groupByProperty functionality with proper error handling.
     * @param string $property
     * @return Collection
     */
    public function groupByProperty(string $property): Collection
    {
        return $this->groupBy(fn($enum) => $enum->{$property}());
    }
    /**
     * Handle groupByColor functionality with proper error handling.
     * @return Collection
     */
    public function groupByColor(): Collection
    {
        return $this->groupByProperty('color');
    }
    /**
     * Handle groupByPriority functionality with proper error handling.
     * @return Collection
     */
    public function groupByPriority(): Collection
    {
        return $this->groupByProperty('priority');
    }
    /**
     * Handle whereProperty functionality with proper error handling.
     * @param string $property
     * @param mixed $value
     * @return self
     */
    public function whereProperty(string $property, mixed $value): self
    {
        return $this->filter(fn($enum) => $enum->{$property}() === $value);
    }
    /**
     * Handle whereColor functionality with proper error handling.
     * @param string $color
     * @return self
     */
    public function whereColor(string $color): self
    {
        return $this->whereProperty('color', $color);
    }
    /**
     * Handle wherePriority functionality with proper error handling.
     * @param int $priority
     * @return self
     */
    public function wherePriority(int $priority): self
    {
        return $this->whereProperty('priority', $priority);
    }
    /**
     * Handle wherePriorityGreaterThan functionality with proper error handling.
     * @param int $priority
     * @return self
     */
    public function wherePriorityGreaterThan(int $priority): self
    {
        return $this->filter(fn($enum) => $enum->priority() > $priority);
    }
    /**
     * Handle wherePriorityLessThan functionality with proper error handling.
     * @param int $priority
     * @return self
     */
    public function wherePriorityLessThan(int $priority): self
    {
        return $this->filter(fn($enum) => $enum->priority() < $priority);
    }
    /**
     * Handle wherePriorityBetween functionality with proper error handling.
     * @param int $min
     * @param int $max
     * @return self
     */
    public function wherePriorityBetween(int $min, int $max): self
    {
        return $this->filter(fn($enum) => $enum->priority() >= $min && $enum->priority() <= $max);
    }
    /**
     * Handle whereIcon functionality with proper error handling.
     * @param string $icon
     * @return self
     */
    public function whereIcon(string $icon): self
    {
        return $this->whereProperty('icon', $icon);
    }
    /**
     * Handle whereLabel functionality with proper error handling.
     * @param string $label
     * @return self
     */
    public function whereLabel(string $label): self
    {
        return $this->whereProperty('label', $label);
    }
    /**
     * Handle whereValue functionality with proper error handling.
     * @param string $value
     * @return self
     */
    public function whereValue(string $value): self
    {
        return $this->whereProperty('value', $value);
    }
    /**
     * Handle whereDescription functionality with proper error handling.
     * @param string $description
     * @return self
     */
    public function whereDescription(string $description): self
    {
        return $this->whereProperty('description', $description);
    }
    /**
     * Handle whereLabelContains functionality with proper error handling.
     * @param string $text
     * @return self
     */
    public function whereLabelContains(string $text): self
    {
        return $this->filter(fn($enum) => str_contains($enum->label(), $text));
    }
    /**
     * Handle whereDescriptionContains functionality with proper error handling.
     * @param string $text
     * @return self
     */
    public function whereDescriptionContains(string $text): self
    {
        return $this->filter(fn($enum) => str_contains($enum->description(), $text));
    }
    /**
     * Handle whereValueContains functionality with proper error handling.
     * @param string $text
     * @return self
     */
    public function whereValueContains(string $text): self
    {
        return $this->filter(fn($enum) => str_contains($enum->value, $text));
    }
    /**
     * Handle whereValueStartsWith functionality with proper error handling.
     * @param string $text
     * @return self
     */
    public function whereValueStartsWith(string $text): self
    {
        return $this->filter(fn($enum) => str_starts_with($enum->value, $text));
    }
    /**
     * Handle whereValueEndsWith functionality with proper error handling.
     * @param string $text
     * @return self
     */
    public function whereValueEndsWith(string $text): self
    {
        return $this->filter(fn($enum) => str_ends_with($enum->value, $text));
    }
    /**
     * Handle whereValueMatches functionality with proper error handling.
     * @param string $pattern
     * @return self
     */
    public function whereValueMatches(string $pattern): self
    {
        return $this->filter(fn($enum) => preg_match($pattern, $enum->value));
    }
    /**
     * Handle whereLabelMatches functionality with proper error handling.
     * @param string $pattern
     * @return self
     */
    public function whereLabelMatches(string $pattern): self
    {
        return $this->filter(fn($enum) => preg_match($pattern, $enum->label()));
    }
    /**
     * Handle whereDescriptionMatches functionality with proper error handling.
     * @param string $pattern
     * @return self
     */
    public function whereDescriptionMatches(string $pattern): self
    {
        return $this->filter(fn($enum) => preg_match($pattern, $enum->description()));
    }
    /**
     * Handle paginate functionality with proper error handling.
     * @param int $perPage
     * @param int $page
     * @return array
     */
    public function paginate(int $perPage, int $page = 1): array
    {
        $offset = ($page - 1) * $perPage;
        $items = $this->slice($offset, $perPage)->values();
        return ['data' => $items->toArrays(), 'current_page' => $page, 'per_page' => $perPage, 'total' => $this->count(), 'last_page' => ceil($this->count() / $perPage), 'from' => $offset + 1, 'to' => min($offset + $perPage, $this->count())];
    }
    /**
     * Handle searchEnum functionality with proper error handling.
     * @param string $query
     * @return self
     */
    public function searchEnum(string $query): self
    {
        return $this->filter(function ($enum) use ($query) {
            $query = strtolower($query);
            return str_contains(strtolower($enum->value), $query) || str_contains(strtolower($enum->label()), $query) || str_contains(strtolower($enum->description()), $query);
        });
    }
    /**
     * Handle random functionality with proper error handling.
     * @param mixed $number
     * @param mixed $preserveKeys
     * @return self
     */
    public function random($number = null, $preserveKeys = false): self
    {
        return new self(parent::random($number, $preserveKeys));
    }
    /**
     * Handle unique functionality with proper error handling.
     * @param mixed $key
     * @param mixed $strict
     * @return self
     */
    public function unique($key = null, $strict = false): self
    {
        if ($key === null) {
            $key = fn($enum) => $enum->value;
        }
        return new self(parent::unique($key, $strict));
    }
    /**
     * Handle uniqueByLabel functionality with proper error handling.
     * @return self
     */
    public function uniqueByLabel(): self
    {
        return $this->unique(fn($enum) => $enum->label());
    }
    /**
     * Handle uniqueByColor functionality with proper error handling.
     * @return self
     */
    public function uniqueByColor(): self
    {
        return $this->unique(fn($enum) => $enum->color());
    }
    /**
     * Handle uniqueByIcon functionality with proper error handling.
     * @return self
     */
    public function uniqueByIcon(): self
    {
        return $this->unique(fn($enum) => $enum->icon());
    }
    /**
     * Handle uniqueByPriority functionality with proper error handling.
     * @return self
     */
    public function uniqueByPriority(): self
    {
        return $this->unique(fn($enum) => $enum->priority());
    }
    /**
     * Handle uniqueByDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByDescription(): self
    {
        return $this->unique(fn($enum) => $enum->description());
    }
    /**
     * Handle uniqueByValueAndLabel functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueAndLabel(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->label());
    }
    /**
     * Handle uniqueByValueAndColor functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueAndColor(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->color());
    }
    /**
     * Handle uniqueByValueAndIcon functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueAndIcon(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->icon());
    }
    /**
     * Handle uniqueByValueAndPriority functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueAndPriority(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->priority());
    }
    /**
     * Handle uniqueByValueAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->description());
    }
    /**
     * Handle uniqueByLabelAndColor functionality with proper error handling.
     * @return self
     */
    public function uniqueByLabelAndColor(): self
    {
        return $this->unique(fn($enum) => $enum->label() . '|' . $enum->color());
    }
    /**
     * Handle uniqueByLabelAndIcon functionality with proper error handling.
     * @return self
     */
    public function uniqueByLabelAndIcon(): self
    {
        return $this->unique(fn($enum) => $enum->label() . '|' . $enum->icon());
    }
    /**
     * Handle uniqueByLabelAndPriority functionality with proper error handling.
     * @return self
     */
    public function uniqueByLabelAndPriority(): self
    {
        return $this->unique(fn($enum) => $enum->label() . '|' . $enum->priority());
    }
    /**
     * Handle uniqueByLabelAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByLabelAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->label() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByColorAndIcon functionality with proper error handling.
     * @return self
     */
    public function uniqueByColorAndIcon(): self
    {
        return $this->unique(fn($enum) => $enum->color() . '|' . $enum->icon());
    }
    /**
     * Handle uniqueByColorAndPriority functionality with proper error handling.
     * @return self
     */
    public function uniqueByColorAndPriority(): self
    {
        return $this->unique(fn($enum) => $enum->color() . '|' . $enum->priority());
    }
    /**
     * Handle uniqueByColorAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByColorAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->color() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByIconAndPriority functionality with proper error handling.
     * @return self
     */
    public function uniqueByIconAndPriority(): self
    {
        return $this->unique(fn($enum) => $enum->icon() . '|' . $enum->priority());
    }
    /**
     * Handle uniqueByIconAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByIconAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->icon() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByPriorityAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByPriorityAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->priority() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByValueLabelAndColor functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueLabelAndColor(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->label() . '|' . $enum->color());
    }
    /**
     * Handle uniqueByValueLabelAndIcon functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueLabelAndIcon(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->label() . '|' . $enum->icon());
    }
    /**
     * Handle uniqueByValueLabelAndPriority functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueLabelAndPriority(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->label() . '|' . $enum->priority());
    }
    /**
     * Handle uniqueByValueLabelAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueLabelAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->label() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByValueColorAndIcon functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueColorAndIcon(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->color() . '|' . $enum->icon());
    }
    /**
     * Handle uniqueByValueColorAndPriority functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueColorAndPriority(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->color() . '|' . $enum->priority());
    }
    /**
     * Handle uniqueByValueColorAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueColorAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->color() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByValueIconAndPriority functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueIconAndPriority(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->icon() . '|' . $enum->priority());
    }
    /**
     * Handle uniqueByValueIconAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueIconAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->icon() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByValuePriorityAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByValuePriorityAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->priority() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByLabelColorAndIcon functionality with proper error handling.
     * @return self
     */
    public function uniqueByLabelColorAndIcon(): self
    {
        return $this->unique(fn($enum) => $enum->label() . '|' . $enum->color() . '|' . $enum->icon());
    }
    /**
     * Handle uniqueByLabelColorAndPriority functionality with proper error handling.
     * @return self
     */
    public function uniqueByLabelColorAndPriority(): self
    {
        return $this->unique(fn($enum) => $enum->label() . '|' . $enum->color() . '|' . $enum->priority());
    }
    /**
     * Handle uniqueByLabelColorAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByLabelColorAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->label() . '|' . $enum->color() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByLabelIconAndPriority functionality with proper error handling.
     * @return self
     */
    public function uniqueByLabelIconAndPriority(): self
    {
        return $this->unique(fn($enum) => $enum->label() . '|' . $enum->icon() . '|' . $enum->priority());
    }
    /**
     * Handle uniqueByLabelIconAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByLabelIconAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->label() . '|' . $enum->icon() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByLabelPriorityAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByLabelPriorityAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->label() . '|' . $enum->priority() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByColorIconAndPriority functionality with proper error handling.
     * @return self
     */
    public function uniqueByColorIconAndPriority(): self
    {
        return $this->unique(fn($enum) => $enum->color() . '|' . $enum->icon() . '|' . $enum->priority());
    }
    /**
     * Handle uniqueByColorIconAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByColorIconAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->color() . '|' . $enum->icon() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByColorPriorityAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByColorPriorityAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->color() . '|' . $enum->priority() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByIconPriorityAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByIconPriorityAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->icon() . '|' . $enum->priority() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByValueLabelColorAndIcon functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueLabelColorAndIcon(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->label() . '|' . $enum->color() . '|' . $enum->icon());
    }
    /**
     * Handle uniqueByValueLabelColorAndPriority functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueLabelColorAndPriority(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->label() . '|' . $enum->color() . '|' . $enum->priority());
    }
    /**
     * Handle uniqueByValueLabelColorAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueLabelColorAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->label() . '|' . $enum->color() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByValueLabelIconAndPriority functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueLabelIconAndPriority(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->label() . '|' . $enum->icon() . '|' . $enum->priority());
    }
    /**
     * Handle uniqueByValueLabelIconAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueLabelIconAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->label() . '|' . $enum->icon() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByValueLabelPriorityAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueLabelPriorityAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->label() . '|' . $enum->priority() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByValueColorIconAndPriority functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueColorIconAndPriority(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->color() . '|' . $enum->icon() . '|' . $enum->priority());
    }
    /**
     * Handle uniqueByValueColorIconAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueColorIconAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->color() . '|' . $enum->icon() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByValueColorPriorityAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueColorPriorityAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->color() . '|' . $enum->priority() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByValueIconPriorityAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueIconPriorityAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->icon() . '|' . $enum->priority() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByLabelColorIconAndPriority functionality with proper error handling.
     * @return self
     */
    public function uniqueByLabelColorIconAndPriority(): self
    {
        return $this->unique(fn($enum) => $enum->label() . '|' . $enum->color() . '|' . $enum->icon() . '|' . $enum->priority());
    }
    /**
     * Handle uniqueByLabelColorIconAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByLabelColorIconAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->label() . '|' . $enum->color() . '|' . $enum->icon() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByLabelColorPriorityAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByLabelColorPriorityAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->label() . '|' . $enum->color() . '|' . $enum->priority() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByLabelIconPriorityAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByLabelIconPriorityAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->label() . '|' . $enum->icon() . '|' . $enum->priority() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByColorIconPriorityAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByColorIconPriorityAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->color() . '|' . $enum->icon() . '|' . $enum->priority() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByValueLabelColorIconAndPriority functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueLabelColorIconAndPriority(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->label() . '|' . $enum->color() . '|' . $enum->icon() . '|' . $enum->priority());
    }
    /**
     * Handle uniqueByValueLabelColorIconAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueLabelColorIconAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->label() . '|' . $enum->color() . '|' . $enum->icon() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByValueLabelColorPriorityAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueLabelColorPriorityAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->label() . '|' . $enum->color() . '|' . $enum->priority() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByValueLabelIconPriorityAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueLabelIconPriorityAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->label() . '|' . $enum->icon() . '|' . $enum->priority() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByValueColorIconPriorityAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByValueColorIconPriorityAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->color() . '|' . $enum->icon() . '|' . $enum->priority() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByLabelColorIconPriorityAndDescription functionality with proper error handling.
     * @return self
     */
    public function uniqueByLabelColorIconPriorityAndDescription(): self
    {
        return $this->unique(fn($enum) => $enum->label() . '|' . $enum->color() . '|' . $enum->icon() . '|' . $enum->priority() . '|' . $enum->description());
    }
    /**
     * Handle uniqueByAll functionality with proper error handling.
     * @return self
     */
    public function uniqueByAll(): self
    {
        return $this->unique(fn($enum) => $enum->value . '|' . $enum->label() . '|' . $enum->color() . '|' . $enum->icon() . '|' . $enum->priority() . '|' . $enum->description());
    }
    /**
     * Handle splitIn functionality with proper error handling.
     * @param mixed $numberOfGroups
     * @return Illuminate\Support\Collection
     */
    public function splitIn($numberOfGroups): \Illuminate\Support\Collection
    {
        return parent::splitIn($numberOfGroups);
    }
    /**
     * Handle splitForDisplay functionality with proper error handling.
     * @param int $columns
     * @return Illuminate\Support\Collection
     */
    public function splitForDisplay(int $columns = 3): \Illuminate\Support\Collection
    {
        return $this->splitIn($columns);
    }
    /**
     * Handle splitForForm functionality with proper error handling.
     * @param int $columns
     * @return Illuminate\Support\Collection
     */
    public function splitForForm(int $columns = 2): \Illuminate\Support\Collection
    {
        return $this->splitIn($columns);
    }
    /**
     * Handle splitForApi functionality with proper error handling.
     * @param int $groups
     * @return Illuminate\Support\Collection
     */
    public function splitForApi(int $groups = 4): \Illuminate\Support\Collection
    {
        return $this->splitIn($groups);
    }
}