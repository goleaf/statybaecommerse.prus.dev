<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Contracts\EnumInterface;
use App\Enums\AddressType;
use App\Enums\NavigationGroup;
use App\Enums\OrderStatus;
use App\Enums\PaymentType;
use App\Enums\ProductStatus;
use App\Enums\UserRole;
use Illuminate\Support\Collection;

final class EnumHelper
{
    /**
     * Get all available enums
     */
    public static function getAllEnums(): array
    {
        return [
            'address_type' => AddressType::class,
            'navigation_group' => NavigationGroup::class,
            'order_status' => OrderStatus::class,
            'payment_type' => PaymentType::class,
            'product_status' => ProductStatus::class,
            'user_role' => UserRole::class,
        ];
    }

    /**
     * Get enum by name
     */
    public static function getEnum(string $name): ?string
    {
        return self::getAllEnums()[$name] ?? null;
    }

    /**
     * Get enum options by name
     */
    public static function getOptions(string $name): array
    {
        $enumClass = self::getEnum($name);

        if (! $enumClass || ! class_exists($enumClass)) {
            return [];
        }

        return $enumClass::options();
    }

    /**
     * Get enum options with descriptions by name
     */
    public static function getOptionsWithDescriptions(string $name): array
    {
        $enumClass = self::getEnum($name);

        if (! $enumClass || ! class_exists($enumClass)) {
            return [];
        }

        return $enumClass::optionsWithDescriptions();
    }

    /**
     * Get enum case by name and value
     */
    public static function getCase(string $name, string $value): ?EnumInterface
    {
        $enumClass = self::getEnum($name);

        if (! $enumClass || ! class_exists($enumClass)) {
            return null;
        }

        try {
            return $enumClass::from($value);
        } catch (\ValueError $e) {
            return null;
        }
    }

    /**
     * Get enum case by name and label
     */
    public static function getCaseByLabel(string $name, string $label): ?EnumInterface
    {
        $enumClass = self::getEnum($name);

        if (! $enumClass || ! class_exists($enumClass)) {
            return null;
        }

        return $enumClass::fromLabel($label);
    }

    /**
     * Get enum label by name and value
     */
    public static function getLabel(string $name, string $value): ?string
    {
        $enum = self::getCase($name, $value);

        return $enum ? $enum->label() : null;
    }

    /**
     * Get enum description by name and value
     */
    public static function getDescription(string $name, string $value): ?string
    {
        $enum = self::getCase($name, $value);

        return $enum ? $enum->description() : null;
    }

    /**
     * Get enum icon by name and value
     */
    public static function getIcon(string $name, string $value): ?string
    {
        $enum = self::getCase($name, $value);

        return $enum ? $enum->icon() : null;
    }

    /**
     * Get enum color by name and value
     */
    public static function getColor(string $name, string $value): ?string
    {
        $enum = self::getCase($name, $value);

        return $enum ? $enum->color() : null;
    }

    /**
     * Get enum priority by name and value
     */
    public static function getPriority(string $name, string $value): ?int
    {
        $enum = self::getCase($name, $value);

        return $enum ? $enum->priority() : null;
    }

    /**
     * Get enum array by name and value
     */
    public static function getArray(string $name, string $value): ?array
    {
        $enum = self::getCase($name, $value);

        return $enum ? $enum->toArray() : null;
    }

    /**
     * Get enum values by name
     */
    public static function getValues(string $name): array
    {
        $enumClass = self::getEnum($name);

        if (! $enumClass || ! class_exists($enumClass)) {
            return [];
        }

        return $enumClass::values();
    }

    /**
     * Get enum labels by name
     */
    public static function getLabels(string $name): array
    {
        $enumClass = self::getEnum($name);

        if (! $enumClass || ! class_exists($enumClass)) {
            return [];
        }

        return $enumClass::labels();
    }

    /**
     * Get enum descriptions by name
     */
    public static function getDescriptions(string $name): array
    {
        $enumClass = self::getEnum($name);

        if (! $enumClass || ! class_exists($enumClass)) {
            return [];
        }

        return $enumClass::map(fn ($case) => $case->description());
    }

    /**
     * Get enum icons by name
     */
    public static function getIcons(string $name): array
    {
        $enumClass = self::getEnum($name);

        if (! $enumClass || ! class_exists($enumClass)) {
            return [];
        }

        return $enumClass::map(fn ($case) => $case->icon());
    }

    /**
     * Get enum colors by name
     */
    public static function getColors(string $name): array
    {
        $enumClass = self::getEnum($name);

        if (! $enumClass || ! class_exists($enumClass)) {
            return [];
        }

        return $enumClass::map(fn ($case) => $case->color());
    }

    /**
     * Get enum priorities by name
     */
    public static function getPriorities(string $name): array
    {
        $enumClass = self::getEnum($name);

        if (! $enumClass || ! class_exists($enumClass)) {
            return [];
        }

        return $enumClass::map(fn ($case) => $case->priority());
    }

    /**
     * Get enum arrays by name
     */
    public static function getArrays(string $name): array
    {
        $enumClass = self::getEnum($name);

        if (! $enumClass || ! class_exists($enumClass)) {
            return [];
        }

        return $enumClass::map(fn ($case) => $case->toArray());
    }

    /**
     * Get enum collection by name
     */
    public static function getCollection(string $name): Collection
    {
        $enumClass = self::getEnum($name);

        if (! $enumClass || ! class_exists($enumClass)) {
            return collect();
        }

        return $enumClass::collection();
    }

    /**
     * Get enum ordered collection by name
     */
    public static function getOrderedCollection(string $name): Collection
    {
        $enumClass = self::getEnum($name);

        if (! $enumClass || ! class_exists($enumClass)) {
            return collect();
        }

        return $enumClass::ordered();
    }

    /**
     * Get enum filtered collection by name and property
     */
    public static function getFilteredCollection(string $name, string $property, mixed $value): Collection
    {
        $enumClass = self::getEnum($name);

        if (! $enumClass || ! class_exists($enumClass)) {
            return collect();
        }

        return $enumClass::filter(fn ($case) => $case->$property() === $value);
    }

    /**
     * Get enum sorted collection by name and property
     */
    public static function getSortedCollection(string $name, string $property, bool $descending = false): Collection
    {
        $enumClass = self::getEnum($name);

        if (! $enumClass || ! class_exists($enumClass)) {
            return collect();
        }

        return $enumClass::sortBy($property, $descending);
    }

    /**
     * Get enum grouped collection by name and property
     */
    public static function getGroupedCollection(string $name, string $property): Collection
    {
        $enumClass = self::getEnum($name);

        if (! $enumClass || ! class_exists($enumClass)) {
            return collect();
        }

        return $enumClass::groupBy($property);
    }

    /**
     * Get enum searched collection by name and query
     */
    public static function getSearchedCollection(string $name, string $query): Collection
    {
        $enumClass = self::getEnum($name);

        if (! $enumClass || ! class_exists($enumClass)) {
            return collect();
        }

        return $enumClass::search($query);
    }

    /**
     * Get enum paginated collection by name
     */
    public static function getPaginatedCollection(string $name, int $perPage, int $page = 1): array
    {
        $enumClass = self::getEnum($name);

        if (! $enumClass || ! class_exists($enumClass)) {
            return [];
        }

        return $enumClass::paginate($perPage, $page);
    }

    /**
     * Get enum random collection by name
     */
    public static function getRandomCollection(string $name, int $count = 1): Collection
    {
        $enumClass = self::getEnum($name);

        if (! $enumClass || ! class_exists($enumClass)) {
            return collect();
        }

        return $enumClass::random($count);
    }

    /**
     * Get enum unique collection by name and property
     */
    public static function getUniqueCollection(string $name, string $property): Collection
    {
        $enumClass = self::getEnum($name);

        if (! $enumClass || ! class_exists($enumClass)) {
            return collect();
        }

        return $enumClass::unique(fn ($case) => $case->$property());
    }

    /**
     * Get enum unique collection by name and multiple properties
     */
    public static function getUniqueCollectionByMultiple(string $name, array $properties): Collection
    {
        $enumClass = self::getEnum($name);

        if (! $enumClass || ! class_exists($enumClass)) {
            return collect();
        }

        return $enumClass::unique(fn ($case) => implode('|', array_map(fn ($prop) => $case->$prop(), $properties)));
    }

    /**
     * Get enum unique collection by name and all properties
     */
    public static function getUniqueCollectionByAll(string $name): Collection
    {
        $enumClass = self::getEnum($name);

        if (! $enumClass || ! class_exists($enumClass)) {
            return collect();
        }

        return $enumClass::unique(fn ($case) => $case->value.'|'.$case->label().'|'.$case->color().'|'.$case->icon().'|'.$case->priority().'|'.$case->description());
    }

    /**
     * Get enum unique collection by name and value
     */
    public static function getUniqueCollectionByValue(string $name): Collection
    {
        return self::getUniqueCollection($name, 'value');
    }

    /**
     * Get enum unique collection by name and label
     */
    public static function getUniqueCollectionByLabel(string $name): Collection
    {
        return self::getUniqueCollection($name, 'label');
    }

    /**
     * Get enum unique collection by name and color
     */
    public static function getUniqueCollectionByColor(string $name): Collection
    {
        return self::getUniqueCollection($name, 'color');
    }

    /**
     * Get enum unique collection by name and icon
     */
    public static function getUniqueCollectionByIcon(string $name): Collection
    {
        return self::getUniqueCollection($name, 'icon');
    }

    /**
     * Get enum unique collection by name and priority
     */
    public static function getUniqueCollectionByPriority(string $name): Collection
    {
        return self::getUniqueCollection($name, 'priority');
    }

    /**
     * Get enum unique collection by name and description
     */
    public static function getUniqueCollectionByDescription(string $name): Collection
    {
        return self::getUniqueCollection($name, 'description');
    }

    /**
     * Get enum unique collection by name and value and label
     */
    public static function getUniqueCollectionByValueAndLabel(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'label']);
    }

    /**
     * Get enum unique collection by name and value and color
     */
    public static function getUniqueCollectionByValueAndColor(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'color']);
    }

    /**
     * Get enum unique collection by name and value and icon
     */
    public static function getUniqueCollectionByValueAndIcon(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'icon']);
    }

    /**
     * Get enum unique collection by name and value and priority
     */
    public static function getUniqueCollectionByValueAndPriority(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'priority']);
    }

    /**
     * Get enum unique collection by name and value and description
     */
    public static function getUniqueCollectionByValueAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'description']);
    }

    /**
     * Get enum unique collection by name and label and color
     */
    public static function getUniqueCollectionByLabelAndColor(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['label', 'color']);
    }

    /**
     * Get enum unique collection by name and label and icon
     */
    public static function getUniqueCollectionByLabelAndIcon(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['label', 'icon']);
    }

    /**
     * Get enum unique collection by name and label and priority
     */
    public static function getUniqueCollectionByLabelAndPriority(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['label', 'priority']);
    }

    /**
     * Get enum unique collection by name and label and description
     */
    public static function getUniqueCollectionByLabelAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['label', 'description']);
    }

    /**
     * Get enum unique collection by name and color and icon
     */
    public static function getUniqueCollectionByColorAndIcon(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['color', 'icon']);
    }

    /**
     * Get enum unique collection by name and color and priority
     */
    public static function getUniqueCollectionByColorAndPriority(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['color', 'priority']);
    }

    /**
     * Get enum unique collection by name and color and description
     */
    public static function getUniqueCollectionByColorAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['color', 'description']);
    }

    /**
     * Get enum unique collection by name and icon and priority
     */
    public static function getUniqueCollectionByIconAndPriority(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['icon', 'priority']);
    }

    /**
     * Get enum unique collection by name and icon and description
     */
    public static function getUniqueCollectionByIconAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['icon', 'description']);
    }

    /**
     * Get enum unique collection by name and priority and description
     */
    public static function getUniqueCollectionByPriorityAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['priority', 'description']);
    }

    /**
     * Get enum unique collection by name and value and label and color
     */
    public static function getUniqueCollectionByValueLabelAndColor(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'label', 'color']);
    }

    /**
     * Get enum unique collection by name and value and label and icon
     */
    public static function getUniqueCollectionByValueLabelAndIcon(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'label', 'icon']);
    }

    /**
     * Get enum unique collection by name and value and label and priority
     */
    public static function getUniqueCollectionByValueLabelAndPriority(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'label', 'priority']);
    }

    /**
     * Get enum unique collection by name and value and label and description
     */
    public static function getUniqueCollectionByValueLabelAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'label', 'description']);
    }

    /**
     * Get enum unique collection by name and value and color and icon
     */
    public static function getUniqueCollectionByValueColorAndIcon(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'color', 'icon']);
    }

    /**
     * Get enum unique collection by name and value and color and priority
     */
    public static function getUniqueCollectionByValueColorAndPriority(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'color', 'priority']);
    }

    /**
     * Get enum unique collection by name and value and color and description
     */
    public static function getUniqueCollectionByValueColorAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'color', 'description']);
    }

    /**
     * Get enum unique collection by name and value and icon and priority
     */
    public static function getUniqueCollectionByValueIconAndPriority(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'icon', 'priority']);
    }

    /**
     * Get enum unique collection by name and value and icon and description
     */
    public static function getUniqueCollectionByValueIconAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'icon', 'description']);
    }

    /**
     * Get enum unique collection by name and value and priority and description
     */
    public static function getUniqueCollectionByValuePriorityAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'priority', 'description']);
    }

    /**
     * Get enum unique collection by name and label and color and icon
     */
    public static function getUniqueCollectionByLabelColorAndIcon(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['label', 'color', 'icon']);
    }

    /**
     * Get enum unique collection by name and label and color and priority
     */
    public static function getUniqueCollectionByLabelColorAndPriority(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['label', 'color', 'priority']);
    }

    /**
     * Get enum unique collection by name and label and color and description
     */
    public static function getUniqueCollectionByLabelColorAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['label', 'color', 'description']);
    }

    /**
     * Get enum unique collection by name and label and icon and priority
     */
    public static function getUniqueCollectionByLabelIconAndPriority(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['label', 'icon', 'priority']);
    }

    /**
     * Get enum unique collection by name and label and icon and description
     */
    public static function getUniqueCollectionByLabelIconAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['label', 'icon', 'description']);
    }

    /**
     * Get enum unique collection by name and label and priority and description
     */
    public static function getUniqueCollectionByLabelPriorityAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['label', 'priority', 'description']);
    }

    /**
     * Get enum unique collection by name and color and icon and priority
     */
    public static function getUniqueCollectionByColorIconAndPriority(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['color', 'icon', 'priority']);
    }

    /**
     * Get enum unique collection by name and color and icon and description
     */
    public static function getUniqueCollectionByColorIconAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['color', 'icon', 'description']);
    }

    /**
     * Get enum unique collection by name and color and priority and description
     */
    public static function getUniqueCollectionByColorPriorityAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['color', 'priority', 'description']);
    }

    /**
     * Get enum unique collection by name and icon and priority and description
     */
    public static function getUniqueCollectionByIconPriorityAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['icon', 'priority', 'description']);
    }

    /**
     * Get enum unique collection by name and value and label and color and icon
     */
    public static function getUniqueCollectionByValueLabelColorAndIcon(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'label', 'color', 'icon']);
    }

    /**
     * Get enum unique collection by name and value and label and color and priority
     */
    public static function getUniqueCollectionByValueLabelColorAndPriority(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'label', 'color', 'priority']);
    }

    /**
     * Get enum unique collection by name and value and label and color and description
     */
    public static function getUniqueCollectionByValueLabelColorAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'label', 'color', 'description']);
    }

    /**
     * Get enum unique collection by name and value and label and icon and priority
     */
    public static function getUniqueCollectionByValueLabelIconAndPriority(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'label', 'icon', 'priority']);
    }

    /**
     * Get enum unique collection by name and value and label and icon and description
     */
    public static function getUniqueCollectionByValueLabelIconAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'label', 'icon', 'description']);
    }

    /**
     * Get enum unique collection by name and value and label and priority and description
     */
    public static function getUniqueCollectionByValueLabelPriorityAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'label', 'priority', 'description']);
    }

    /**
     * Get enum unique collection by name and value and color and icon and priority
     */
    public static function getUniqueCollectionByValueColorIconAndPriority(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'color', 'icon', 'priority']);
    }

    /**
     * Get enum unique collection by name and value and color and icon and description
     */
    public static function getUniqueCollectionByValueColorIconAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'color', 'icon', 'description']);
    }

    /**
     * Get enum unique collection by name and value and color and priority and description
     */
    public static function getUniqueCollectionByValueColorPriorityAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'color', 'priority', 'description']);
    }

    /**
     * Get enum unique collection by name and value and icon and priority and description
     */
    public static function getUniqueCollectionByValueIconPriorityAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'icon', 'priority', 'description']);
    }

    /**
     * Get enum unique collection by name and label and color and icon and priority
     */
    public static function getUniqueCollectionByLabelColorIconAndPriority(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['label', 'color', 'icon', 'priority']);
    }

    /**
     * Get enum unique collection by name and label and color and icon and description
     */
    public static function getUniqueCollectionByLabelColorIconAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['label', 'color', 'icon', 'description']);
    }

    /**
     * Get enum unique collection by name and label and color and priority and description
     */
    public static function getUniqueCollectionByLabelColorPriorityAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['label', 'color', 'priority', 'description']);
    }

    /**
     * Get enum unique collection by name and label and icon and priority and description
     */
    public static function getUniqueCollectionByLabelIconPriorityAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['label', 'icon', 'priority', 'description']);
    }

    /**
     * Get enum unique collection by name and color and icon and priority and description
     */
    public static function getUniqueCollectionByColorIconPriorityAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['color', 'icon', 'priority', 'description']);
    }

    /**
     * Get enum unique collection by name and value and label and color and icon and priority
     */
    public static function getUniqueCollectionByValueLabelColorIconAndPriority(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'label', 'color', 'icon', 'priority']);
    }

    /**
     * Get enum unique collection by name and value and label and color and icon and description
     */
    public static function getUniqueCollectionByValueLabelColorIconAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'label', 'color', 'icon', 'description']);
    }

    /**
     * Get enum unique collection by name and value and label and color and priority and description
     */
    public static function getUniqueCollectionByValueLabelColorPriorityAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'label', 'color', 'priority', 'description']);
    }

    /**
     * Get enum unique collection by name and value and label and icon and priority and description
     */
    public static function getUniqueCollectionByValueLabelIconPriorityAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'label', 'icon', 'priority', 'description']);
    }

    /**
     * Get enum unique collection by name and value and color and icon and priority and description
     */
    public static function getUniqueCollectionByValueColorIconPriorityAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'color', 'icon', 'priority', 'description']);
    }

    /**
     * Get enum unique collection by name and label and color and icon and priority and description
     */
    public static function getUniqueCollectionByLabelColorIconPriorityAndDescription(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['label', 'color', 'icon', 'priority', 'description']);
    }
}
