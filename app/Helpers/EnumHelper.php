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
use Illuminate\Support\Str;

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
        $enumClass = self::resolveEnumClass($name);

        if ($enumClass === null) {
            return [];
        }

        return $enumClass::options();
    }

    /**
     * Get enum options with descriptions by name
     */
    public static function getOptionsWithDescriptions(string $name): array
    {
        $enumClass = self::resolveEnumClass($name);

        if ($enumClass === null) {
            return [];
        }

        return $enumClass::optionsWithDescriptions();
    }

    /**
     * Get enum case by name and value
     */
    public static function getCase(string $name, string $value): ?EnumInterface
    {
        $enumClass = self::resolveEnumClass($name);

        if ($enumClass === null) {
            return null;
        }

        try {
            // from() throws a ValueError for unknown values, so we gracefully convert
            // that exception into a null result to keep helper consumers defensive-free.
            return $enumClass::from($value);
        } catch (\ValueError) {
            return null;
        }
    }

    /**
     * Get enum case by name and label
     */
    public static function getCaseByLabel(string $name, string $label): ?EnumInterface
    {
        $enumClass = self::resolveEnumClass($name);

        if ($enumClass === null || ! method_exists($enumClass, 'fromLabel')) {
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
        $enumClass = self::resolveEnumClass($name);

        if ($enumClass === null) {
            return [];
        }

        return $enumClass::values();
    }

    /**
     * Get enum labels by name
     */
    public static function getLabels(string $name): array
    {
        $enumClass = self::resolveEnumClass($name);

        if ($enumClass === null) {
            return [];
        }

        return $enumClass::labels();
    }

    /**
     * Get enum descriptions by name
     */
    public static function getDescriptions(string $name): array
    {
        $enumClass = self::resolveEnumClass($name);

        if ($enumClass === null) {
            return [];
        }

        return $enumClass::collection()
            ->filter(fn (EnumInterface $case): bool => method_exists($case, 'description'))
            ->map(fn (EnumInterface $case): string => $case->description())
            ->toArray();
    }

    /**
     * Get enum icons by name
     */
    public static function getIcons(string $name): array
    {
        $enumClass = self::resolveEnumClass($name);

        if ($enumClass === null) {
            return [];
        }

        return $enumClass::collection()
            ->filter(fn (EnumInterface $case): bool => method_exists($case, 'icon'))
            ->map(fn (EnumInterface $case): string => $case->icon())
            ->toArray();
    }

    /**
     * Get enum colors by name
     */
    public static function getColors(string $name): array
    {
        $enumClass = self::resolveEnumClass($name);

        if ($enumClass === null) {
            return [];
        }

        return $enumClass::collection()
            ->filter(fn (EnumInterface $case): bool => method_exists($case, 'color'))
            ->map(fn (EnumInterface $case): string => $case->color())
            ->toArray();
    }

    /**
     * Get enum priorities by name
     */
    public static function getPriorities(string $name): array
    {
        $enumClass = self::resolveEnumClass($name);

        if ($enumClass === null) {
            return [];
        }

        return $enumClass::collection()
            ->filter(fn (EnumInterface $case): bool => method_exists($case, 'priority'))
            ->map(fn (EnumInterface $case): int => $case->priority())
            ->toArray();
    }

    /**
     * Get enum arrays by name
     */
    public static function getArrays(string $name): array
    {
        $enumClass = self::resolveEnumClass($name);

        if ($enumClass === null) {
            return [];
        }

        return $enumClass::collection()
            ->filter(fn (EnumInterface $case): bool => method_exists($case, 'toArray'))
            ->map(fn (EnumInterface $case): array => $case->toArray())
            ->toArray();
    }

    /**
     * Get enum collection by name
     */
    public static function getCollection(string $name): Collection
    {
        $enumClass = self::resolveEnumClass($name);

        if ($enumClass === null) {
            return collect();
        }

        return $enumClass::collection();
    }

    /**
     * Get enum ordered collection by name
     */
    public static function getOrderedCollection(string $name): Collection
    {
        $enumClass = self::resolveEnumClass($name);

        if ($enumClass === null) {
            return collect();
        }

        return $enumClass::ordered();
    }

    /**
     * Get enum filtered collection by name and property
     */
    public static function getFilteredCollection(string $name, string $property, mixed $value): Collection
    {
        $enumClass = self::resolveEnumClass($name);

        if ($enumClass === null) {
            return collect();
        }

        return $enumClass::collection()->filter(
            function (EnumInterface $case) use ($property, $value): bool {
                if (! method_exists($case, $property)) {
                    return false;
                }

                return $case->{$property}() === $value;
            }
        );
    }

    /**
     * Get enum sorted collection by name and property
     */
    public static function getSortedCollection(string $name, string $property, bool $descending = false): Collection
    {
        $enumClass = self::resolveEnumClass($name);

        if ($enumClass === null) {
            return collect();
        }

        return $enumClass::collection()->sortBy(
            function (EnumInterface $case) use ($property) {
                if (! method_exists($case, $property)) {
                    return null;
                }

                return $case->{$property}();
            },
            SORT_REGULAR,
            $descending,
        );
    }

    /**
     * Get enum grouped collection by name and property
     */
    public static function getGroupedCollection(string $name, string $property): Collection
    {
        $enumClass = self::resolveEnumClass($name);

        if ($enumClass === null) {
            return collect();
        }

        return $enumClass::collection()->groupBy(function (EnumInterface $case) use ($property) {
            if (! method_exists($case, $property)) {
                return null;
            }

            return $case->{$property}();
        });
    }

    /**
     * Get enum searched collection by name and query
     */
    public static function getSearchedCollection(string $name, string $query): Collection
    {
        $enumClass = self::resolveEnumClass($name);

        if ($enumClass === null) {
            return collect();
        }

        $query = trim($query);

        if ($query === '') {
            return $enumClass::collection();
        }

        $needle = mb_strtolower($query);

        return $enumClass::collection()
            ->filter(function (EnumInterface $case) use ($needle): bool {
                $candidates = collect([$case->value]);

                foreach (['label', 'description'] as $method) {
                    if (method_exists($case, $method)) {
                        $candidates->push($case->{$method}());
                    }
                }

                return $candidates
                    ->filter(fn ($value) => is_string($value))
                    ->contains(fn (string $value) => Str::contains(mb_strtolower($value), $needle));
            })
            ->values();
    }

    /**
     * Get enum paginated collection by name
     */
    public static function getPaginatedCollection(string $name, int $perPage, int $page = 1): array
    {
        $enumClass = self::resolveEnumClass($name);

        if ($enumClass === null) {
            return [
                'data' => [],
                'total' => 0,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => 0,
            ];
        }

        $page = max(1, $page);
        $collection = $enumClass::collection();
        $total = $collection->count();
        $perPage = max(1, $perPage);
        $lastPage = (int) ceil($total / $perPage);

        return [
            'data' => $collection->forPage($page, $perPage)->values(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $lastPage,
        ];
    }

    /**
     * Get enum random collection by name
     */
    public static function getRandomCollection(string $name, int $count = 1): Collection
    {
        $enumClass = self::resolveEnumClass($name);

        if ($enumClass === null) {
            return collect();
        }

        return $enumClass::collection()->shuffle()->take($count);
    }

    /**
     * Get enum unique collection by name and property
     */
    public static function getUniqueCollection(string $name, string $property): Collection
    {
        $enumClass = self::resolveEnumClass($name);

        if ($enumClass === null) {
            return collect();
        }

        return $enumClass::collection()
            ->unique(function (EnumInterface $case) use ($property) {
                if (! method_exists($case, $property)) {
                    return null;
                }

                $value = $case->{$property}();

                return is_scalar($value) ? $value : serialize($value);
            })
            ->values();
    }

    /**
     * Get enum unique collection by name and multiple properties
     */
    public static function getUniqueCollectionByMultiple(string $name, array $properties): Collection
    {
        $enumClass = self::resolveEnumClass($name);

        if ($enumClass === null) {
            return collect();
        }

        return $enumClass::collection()
            ->unique(function (EnumInterface $case) use ($properties) {
                return collect($properties)
                    ->map(function (string $prop) use ($case) {
                        if (! method_exists($case, $prop)) {
                            return null;
                        }

                        $value = $case->{$prop}();

                        return is_scalar($value) ? $value : serialize($value);
                    })
                    ->implode('|');
            })
            ->values();
    }

    /**
     * Get enum unique collection by name and all properties
     */
    public static function getUniqueCollectionByAll(string $name): Collection
    {
        return self::getUniqueCollectionByMultiple($name, ['value', 'label', 'color', 'icon', 'priority', 'description']);
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

    /**
     * Resolve the configured enum to a concrete class implementing the expected contract.
     */
    private static function resolveEnumClass(string $name): ?string
    {
        $enumClass = self::getEnum($name);

        if (! $enumClass || ! class_exists($enumClass) || ! is_a($enumClass, EnumInterface::class, true)) {
            return null;
        }

        return $enumClass;
    }
}
