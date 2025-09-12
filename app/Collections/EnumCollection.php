<?php

declare(strict_types=1);

namespace App\Collections;

use Illuminate\Support\Collection;

final class EnumCollection extends Collection
{
    /**
     * Create a new enum collection instance
     */
    public function __construct($items = [])
    {
        parent::__construct($items);
    }

    /**
     * Create a new enum collection from enum cases
     */
    public static function fromEnum(string $enumClass): self
    {
        if (! class_exists($enumClass)) {
            throw new \InvalidArgumentException("Enum class '{$enumClass}' not found");
        }

        return new self($enumClass::cases());
    }

    /**
     * Create a new enum collection from enum values
     */
    public static function fromValues(string $enumClass, array $values): self
    {
        if (! class_exists($enumClass)) {
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
     * Create a new enum collection from enum labels
     */
    public static function fromLabels(string $enumClass, array $labels): self
    {
        if (! class_exists($enumClass)) {
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
     * Get enum values
     */
    public function values(): array
    {
        return $this->map(fn ($enum) => $enum->value)->toArray();
    }

    /**
     * Get enum labels
     */
    public function labels(): array
    {
        return $this->map(fn ($enum) => $enum->label())->toArray();
    }

    /**
     * Get enum descriptions
     */
    public function descriptions(): array
    {
        return $this->map(fn ($enum) => $enum->description())->toArray();
    }

    /**
     * Get enum icons
     */
    public function icons(): array
    {
        return $this->map(fn ($enum) => $enum->icon())->toArray();
    }

    /**
     * Get enum colors
     */
    public function colors(): array
    {
        return $this->map(fn ($enum) => $enum->color())->toArray();
    }

    /**
     * Get enum priorities
     */
    public function priorities(): array
    {
        return $this->map(fn ($enum) => $enum->priority())->toArray();
    }

    /**
     * Get enum options for select dropdowns
     */
    public function options(): array
    {
        return $this->mapWithKeys(fn ($enum) => [$enum->value => $enum->label()])->toArray();
    }

    /**
     * Get enum options with descriptions
     */
    public function optionsWithDescriptions(): array
    {
        return $this->mapWithKeys(fn ($enum) => [
            $enum->value => $enum->toArray(),
        ])->toArray();
    }

    /**
     * Get enum cases as arrays
     */
    public function toArrays(): array
    {
        return $this->map(fn ($enum) => $enum->toArray())->toArray();
    }

    /**
     * Get enum cases as JSON
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArrays(), $options);
    }

    /**
     * Get enum cases for API responses
     */
    public function forApi(): array
    {
        return $this->map(fn ($enum) => [
            'value' => $enum->value,
            'label' => $enum->label(),
            'description' => $enum->description(),
            'icon' => $enum->icon(),
            'color' => $enum->color(),
        ])->toArray();
    }

    /**
     * Get enum cases for GraphQL
     */
    public function forGraphQL(): array
    {
        return $this->map(fn ($enum) => [
            'name' => strtoupper($enum->value),
            'value' => $enum->value,
            'description' => $enum->description(),
        ])->toArray();
    }

    /**
     * Get enum cases for TypeScript
     */
    public function forTypeScript(): string
    {
        $enumName = class_basename($this->first()::class);
        $enum = "export enum {$enumName} {\n";

        foreach ($this as $enumCase) {
            $enum .= '  '.strtoupper($enumCase->value)." = '".$enumCase->value."',\n";
        }

        return $enum.'}';
    }

    /**
     * Get enum cases for JavaScript
     */
    public function forJavaScript(): string
    {
        $enumName = class_basename($this->first()::class);
        $object = "const {$enumName} = {\n";

        foreach ($this as $enumCase) {
            $object .= '  '.strtoupper($enumCase->value).": '".$enumCase->value."',\n";
        }

        return $object.'};';
    }

    /**
     * Get enum cases for CSS
     */
    public function forCss(): string
    {
        $css = ":root {\n";

        foreach ($this as $enumCase) {
            $css .= '  --'.str_replace('_', '-', $enumCase->value).": '".$enumCase->value."';\n";
        }

        return $css.'}';
    }

    /**
     * Get enum cases for documentation
     */
    public function forDocumentation(): array
    {
        return $this->map(fn ($enum) => [
            'value' => $enum->value,
            'label' => $enum->label(),
            'description' => $enum->description(),
            'icon' => $enum->icon(),
            'color' => $enum->color(),
            'priority' => $enum->priority(),
        ])->toArray();
    }

    /**
     * Get enum cases for form validation
     */
    public function forValidation(): string
    {
        return 'in:'.implode(',', $this->values());
    }

    /**
     * Get enum cases for database enum column
     */
    public function forDatabase(): string
    {
        return "enum('".implode("','", $this->values())."')";
    }

    /**
     * Filter enum cases by a property
     */
    public function filterBy(string $property, mixed $value): self
    {
        return $this->filter(fn ($enum) => $enum->$property() === $value);
    }

    /**
     * Filter enum cases by multiple properties
     */
    public function filterByMultiple(array $filters): self
    {
        return $this->filter(function ($enum) use ($filters) {
            foreach ($filters as $property => $value) {
                if ($enum->$property() !== $value) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Sort enum cases by a property
     */
    public function sortByProperty(string $property, bool $descending = false): self
    {
        $sorted = $this->sortBy(fn ($enum) => $enum->$property());

        return $descending ? $sorted->reverse() : $sorted;
    }

    /**
     * Sort enum cases by priority
     */
    public function sortByPriority(bool $descending = false): self
    {
        return $this->sortByProperty('priority', $descending);
    }

    /**
     * Sort enum cases by label
     */
    public function sortByLabel(bool $descending = false): self
    {
        return $this->sortByProperty('label', $descending);
    }

    /**
     * Sort enum cases by value
     */
    public function sortByValue(bool $descending = false): self
    {
        return $this->sortBy(fn ($enum) => $enum->value, $descending);
    }

    /**
     * Group enum cases by a property
     */
    public function groupByProperty(string $property): Collection
    {
        return $this->groupBy(fn ($enum) => $enum->$property());
    }

    /**
     * Group enum cases by color
     */
    public function groupByColor(): Collection
    {
        return $this->groupByProperty('color');
    }

    /**
     * Group enum cases by priority
     */
    public function groupByPriority(): Collection
    {
        return $this->groupByProperty('priority');
    }

    /**
     * Get enum cases with specific property value
     */
    public function whereProperty(string $property, mixed $value): self
    {
        return $this->filter(fn ($enum) => $enum->$property() === $value);
    }

    /**
     * Get enum cases with specific color
     */
    public function whereColor(string $color): self
    {
        return $this->whereProperty('color', $color);
    }

    /**
     * Get enum cases with specific priority
     */
    public function wherePriority(int $priority): self
    {
        return $this->whereProperty('priority', $priority);
    }

    /**
     * Get enum cases with priority greater than
     */
    public function wherePriorityGreaterThan(int $priority): self
    {
        return $this->filter(fn ($enum) => $enum->priority() > $priority);
    }

    /**
     * Get enum cases with priority less than
     */
    public function wherePriorityLessThan(int $priority): self
    {
        return $this->filter(fn ($enum) => $enum->priority() < $priority);
    }

    /**
     * Get enum cases with priority between
     */
    public function wherePriorityBetween(int $min, int $max): self
    {
        return $this->filter(fn ($enum) => $enum->priority() >= $min && $enum->priority() <= $max);
    }

    /**
     * Get enum cases with specific icon
     */
    public function whereIcon(string $icon): self
    {
        return $this->whereProperty('icon', $icon);
    }

    /**
     * Get enum cases with specific label
     */
    public function whereLabel(string $label): self
    {
        return $this->whereProperty('label', $label);
    }

    /**
     * Get enum cases with specific value
     */
    public function whereValue(string $value): self
    {
        return $this->whereProperty('value', $value);
    }

    /**
     * Get enum cases with specific description
     */
    public function whereDescription(string $description): self
    {
        return $this->whereProperty('description', $description);
    }

    /**
     * Get enum cases that contain text in label
     */
    public function whereLabelContains(string $text): self
    {
        return $this->filter(fn ($enum) => str_contains($enum->label(), $text));
    }

    /**
     * Get enum cases that contain text in description
     */
    public function whereDescriptionContains(string $text): self
    {
        return $this->filter(fn ($enum) => str_contains($enum->description(), $text));
    }

    /**
     * Get enum cases that contain text in value
     */
    public function whereValueContains(string $text): self
    {
        return $this->filter(fn ($enum) => str_contains($enum->value, $text));
    }

    /**
     * Get enum cases that start with text
     */
    public function whereValueStartsWith(string $text): self
    {
        return $this->filter(fn ($enum) => str_starts_with($enum->value, $text));
    }

    /**
     * Get enum cases that end with text
     */
    public function whereValueEndsWith(string $text): self
    {
        return $this->filter(fn ($enum) => str_ends_with($enum->value, $text));
    }

    /**
     * Get enum cases that match pattern
     */
    public function whereValueMatches(string $pattern): self
    {
        return $this->filter(fn ($enum) => preg_match($pattern, $enum->value));
    }

    /**
     * Get enum cases that match label pattern
     */
    public function whereLabelMatches(string $pattern): self
    {
        return $this->filter(fn ($enum) => preg_match($pattern, $enum->label()));
    }

    /**
     * Get enum cases that match description pattern
     */
    public function whereDescriptionMatches(string $pattern): self
    {
        return $this->filter(fn ($enum) => preg_match($pattern, $enum->description()));
    }

    /**
     * Get enum cases with pagination
     */
    public function paginate(int $perPage, int $page = 1): array
    {
        $offset = ($page - 1) * $perPage;
        $items = $this->slice($offset, $perPage)->values();

        return [
            'data' => $items->toArrays(),
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $this->count(),
            'last_page' => ceil($this->count() / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $this->count()),
        ];
    }

    /**
     * Get enum cases with search
     */
    public function search(string $query): self
    {
        return $this->filter(function ($enum) use ($query) {
            $query = strtolower($query);

            return str_contains(strtolower($enum->value), $query) ||
                   str_contains(strtolower($enum->label()), $query) ||
                   str_contains(strtolower($enum->description()), $query);
        });
    }

    /**
     * Get enum cases with random selection
     */
    public function random(int $count = 1): self
    {
        return new self($this->random($count));
    }

    /**
     * Get enum cases with unique values
     */
    public function unique(): self
    {
        return $this->unique(fn ($enum) => $enum->value);
    }

    /**
     * Get enum cases with unique labels
     */
    public function uniqueByLabel(): self
    {
        return $this->unique(fn ($enum) => $enum->label());
    }

    /**
     * Get enum cases with unique colors
     */
    public function uniqueByColor(): self
    {
        return $this->unique(fn ($enum) => $enum->color());
    }

    /**
     * Get enum cases with unique icons
     */
    public function uniqueByIcon(): self
    {
        return $this->unique(fn ($enum) => $enum->icon());
    }

    /**
     * Get enum cases with unique priorities
     */
    public function uniqueByPriority(): self
    {
        return $this->unique(fn ($enum) => $enum->priority());
    }

    /**
     * Get enum cases with unique descriptions
     */
    public function uniqueByDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->description());
    }

    /**
     * Get enum cases with unique values and labels
     */
    public function uniqueByValueAndLabel(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->label());
    }

    /**
     * Get enum cases with unique values and colors
     */
    public function uniqueByValueAndColor(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->color());
    }

    /**
     * Get enum cases with unique values and icons
     */
    public function uniqueByValueAndIcon(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->icon());
    }

    /**
     * Get enum cases with unique values and priorities
     */
    public function uniqueByValueAndPriority(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->priority());
    }

    /**
     * Get enum cases with unique values and descriptions
     */
    public function uniqueByValueAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->description());
    }

    /**
     * Get enum cases with unique labels and colors
     */
    public function uniqueByLabelAndColor(): self
    {
        return $this->unique(fn ($enum) => $enum->label().'|'.$enum->color());
    }

    /**
     * Get enum cases with unique labels and icons
     */
    public function uniqueByLabelAndIcon(): self
    {
        return $this->unique(fn ($enum) => $enum->label().'|'.$enum->icon());
    }

    /**
     * Get enum cases with unique labels and priorities
     */
    public function uniqueByLabelAndPriority(): self
    {
        return $this->unique(fn ($enum) => $enum->label().'|'.$enum->priority());
    }

    /**
     * Get enum cases with unique labels and descriptions
     */
    public function uniqueByLabelAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->label().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique colors and icons
     */
    public function uniqueByColorAndIcon(): self
    {
        return $this->unique(fn ($enum) => $enum->color().'|'.$enum->icon());
    }

    /**
     * Get enum cases with unique colors and priorities
     */
    public function uniqueByColorAndPriority(): self
    {
        return $this->unique(fn ($enum) => $enum->color().'|'.$enum->priority());
    }

    /**
     * Get enum cases with unique colors and descriptions
     */
    public function uniqueByColorAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->color().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique icons and priorities
     */
    public function uniqueByIconAndPriority(): self
    {
        return $this->unique(fn ($enum) => $enum->icon().'|'.$enum->priority());
    }

    /**
     * Get enum cases with unique icons and descriptions
     */
    public function uniqueByIconAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->icon().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique priorities and descriptions
     */
    public function uniqueByPriorityAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->priority().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique values, labels, and colors
     */
    public function uniqueByValueLabelAndColor(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->label().'|'.$enum->color());
    }

    /**
     * Get enum cases with unique values, labels, and icons
     */
    public function uniqueByValueLabelAndIcon(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->label().'|'.$enum->icon());
    }

    /**
     * Get enum cases with unique values, labels, and priorities
     */
    public function uniqueByValueLabelAndPriority(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->label().'|'.$enum->priority());
    }

    /**
     * Get enum cases with unique values, labels, and descriptions
     */
    public function uniqueByValueLabelAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->label().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique values, colors, and icons
     */
    public function uniqueByValueColorAndIcon(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->color().'|'.$enum->icon());
    }

    /**
     * Get enum cases with unique values, colors, and priorities
     */
    public function uniqueByValueColorAndPriority(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->color().'|'.$enum->priority());
    }

    /**
     * Get enum cases with unique values, colors, and descriptions
     */
    public function uniqueByValueColorAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->color().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique values, icons, and priorities
     */
    public function uniqueByValueIconAndPriority(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->icon().'|'.$enum->priority());
    }

    /**
     * Get enum cases with unique values, icons, and descriptions
     */
    public function uniqueByValueIconAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->icon().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique values, priorities, and descriptions
     */
    public function uniqueByValuePriorityAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->priority().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique labels, colors, and icons
     */
    public function uniqueByLabelColorAndIcon(): self
    {
        return $this->unique(fn ($enum) => $enum->label().'|'.$enum->color().'|'.$enum->icon());
    }

    /**
     * Get enum cases with unique labels, colors, and priorities
     */
    public function uniqueByLabelColorAndPriority(): self
    {
        return $this->unique(fn ($enum) => $enum->label().'|'.$enum->color().'|'.$enum->priority());
    }

    /**
     * Get enum cases with unique labels, colors, and descriptions
     */
    public function uniqueByLabelColorAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->label().'|'.$enum->color().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique labels, icons, and priorities
     */
    public function uniqueByLabelIconAndPriority(): self
    {
        return $this->unique(fn ($enum) => $enum->label().'|'.$enum->icon().'|'.$enum->priority());
    }

    /**
     * Get enum cases with unique labels, icons, and descriptions
     */
    public function uniqueByLabelIconAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->label().'|'.$enum->icon().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique labels, priorities, and descriptions
     */
    public function uniqueByLabelPriorityAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->label().'|'.$enum->priority().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique colors, icons, and priorities
     */
    public function uniqueByColorIconAndPriority(): self
    {
        return $this->unique(fn ($enum) => $enum->color().'|'.$enum->icon().'|'.$enum->priority());
    }

    /**
     * Get enum cases with unique colors, icons, and descriptions
     */
    public function uniqueByColorIconAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->color().'|'.$enum->icon().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique colors, priorities, and descriptions
     */
    public function uniqueByColorPriorityAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->color().'|'.$enum->priority().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique icons, priorities, and descriptions
     */
    public function uniqueByIconPriorityAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->icon().'|'.$enum->priority().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique values, labels, colors, and icons
     */
    public function uniqueByValueLabelColorAndIcon(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->label().'|'.$enum->color().'|'.$enum->icon());
    }

    /**
     * Get enum cases with unique values, labels, colors, and priorities
     */
    public function uniqueByValueLabelColorAndPriority(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->label().'|'.$enum->color().'|'.$enum->priority());
    }

    /**
     * Get enum cases with unique values, labels, colors, and descriptions
     */
    public function uniqueByValueLabelColorAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->label().'|'.$enum->color().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique values, labels, icons, and priorities
     */
    public function uniqueByValueLabelIconAndPriority(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->label().'|'.$enum->icon().'|'.$enum->priority());
    }

    /**
     * Get enum cases with unique values, labels, icons, and descriptions
     */
    public function uniqueByValueLabelIconAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->label().'|'.$enum->icon().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique values, labels, priorities, and descriptions
     */
    public function uniqueByValueLabelPriorityAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->label().'|'.$enum->priority().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique values, colors, icons, and priorities
     */
    public function uniqueByValueColorIconAndPriority(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->color().'|'.$enum->icon().'|'.$enum->priority());
    }

    /**
     * Get enum cases with unique values, colors, icons, and descriptions
     */
    public function uniqueByValueColorIconAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->color().'|'.$enum->icon().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique values, colors, priorities, and descriptions
     */
    public function uniqueByValueColorPriorityAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->color().'|'.$enum->priority().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique values, icons, priorities, and descriptions
     */
    public function uniqueByValueIconPriorityAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->icon().'|'.$enum->priority().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique labels, colors, icons, and priorities
     */
    public function uniqueByLabelColorIconAndPriority(): self
    {
        return $this->unique(fn ($enum) => $enum->label().'|'.$enum->color().'|'.$enum->icon().'|'.$enum->priority());
    }

    /**
     * Get enum cases with unique labels, colors, icons, and descriptions
     */
    public function uniqueByLabelColorIconAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->label().'|'.$enum->color().'|'.$enum->icon().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique labels, colors, priorities, and descriptions
     */
    public function uniqueByLabelColorPriorityAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->label().'|'.$enum->color().'|'.$enum->priority().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique labels, icons, priorities, and descriptions
     */
    public function uniqueByLabelIconPriorityAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->label().'|'.$enum->icon().'|'.$enum->priority().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique colors, icons, priorities, and descriptions
     */
    public function uniqueByColorIconPriorityAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->color().'|'.$enum->icon().'|'.$enum->priority().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique values, labels, colors, icons, and priorities
     */
    public function uniqueByValueLabelColorIconAndPriority(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->label().'|'.$enum->color().'|'.$enum->icon().'|'.$enum->priority());
    }

    /**
     * Get enum cases with unique values, labels, colors, icons, and descriptions
     */
    public function uniqueByValueLabelColorIconAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->label().'|'.$enum->color().'|'.$enum->icon().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique values, labels, colors, priorities, and descriptions
     */
    public function uniqueByValueLabelColorPriorityAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->label().'|'.$enum->color().'|'.$enum->priority().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique values, labels, icons, priorities, and descriptions
     */
    public function uniqueByValueLabelIconPriorityAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->label().'|'.$enum->icon().'|'.$enum->priority().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique values, colors, icons, priorities, and descriptions
     */
    public function uniqueByValueColorIconPriorityAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->color().'|'.$enum->icon().'|'.$enum->priority().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique labels, colors, icons, priorities, and descriptions
     */
    public function uniqueByLabelColorIconPriorityAndDescription(): self
    {
        return $this->unique(fn ($enum) => $enum->label().'|'.$enum->color().'|'.$enum->icon().'|'.$enum->priority().'|'.$enum->description());
    }

    /**
     * Get enum cases with unique values, labels, colors, icons, priorities, and descriptions
     */
    public function uniqueByAll(): self
    {
        return $this->unique(fn ($enum) => $enum->value.'|'.$enum->label().'|'.$enum->color().'|'.$enum->icon().'|'.$enum->priority().'|'.$enum->description());
    }
}
