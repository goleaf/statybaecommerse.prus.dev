<?php

declare(strict_types=1);

namespace App\Factories;

use App\Contracts\EnumInterface;
use App\Enums\PaymentType;
use InvalidArgumentException;
use ValueError;

final class EnumFactory
{
    /**
     * @var array<string, class-string<EnumInterface>>
     */
    private const DEFAULT_ENUMS = [
        'payment_type' => PaymentType::class,
    ];

    /**
     * @var array<string, class-string<EnumInterface>>|null
     */
    private static ?array $registry = null;

    public static function register(string $name, string $enumClass): void
    {
        if (! class_exists($enumClass) || ! is_a($enumClass, EnumInterface::class, true)) {
            throw new InvalidArgumentException("Enum [{$enumClass}] must implement " . EnumInterface::class . '.');
        }

        self::boot();
        self::$registry[$name] = $enumClass;
    }

    public static function reset(): void
    {
        self::$registry = self::DEFAULT_ENUMS;
    }

    public static function exists(string $name): bool
    {
        self::boot();

        return isset(self::$registry[$name]);
    }

    public static function getEnumClassName(string $name): ?string
    {
        self::boot();

        return self::$registry[$name] ?? null;
    }

    public static function create(string $name, string $value): ?EnumInterface
    {
        $enumClass = self::getEnumClassName($name);
        if ($enumClass === null || ! method_exists($enumClass, 'from')) {
            return null;
        }

        try {
            /** @var EnumInterface $enum */
            $enum = $enumClass::from($value);

            return $enum;
        } catch (ValueError) {
            return null;
        }
    }

    /**
     * @return array<int, string>
     */
    public static function getEnumValues(string $name): array
    {
        $enumClass = self::getEnumClassName($name);
        if ($enumClass === null) {
            return [];
        }

        if (method_exists($enumClass, 'values')) {
            /** @var array<int, string> $values */
            $values = $enumClass::values();

            return $values;
        }

        return [];
    }

    /**
     * @return array<int, string>
     */
    public static function getEnumLabels(string $name): array
    {
        $enumClass = self::getEnumClassName($name);
        if ($enumClass === null) {
            return [];
        }

        if (method_exists($enumClass, 'labels')) {
            /** @var array<int, string> $labels */
            $labels = $enumClass::labels();

            return $labels;
        }

        return [];
    }

    /**
     * @return array<string, string>
     */
    public static function getEnumOptions(string $name): array
    {
        $enumClass = self::getEnumClassName($name);
        if ($enumClass === null) {
            return [];
        }

        if (method_exists($enumClass, 'options')) {
            /** @var array<string, string> $options */
            $options = $enumClass::options();

            return $options;
        }

        return [];
    }

    public static function validateValue(string $name, string $value): bool
    {
        return in_array($value, self::getEnumValues($name), true);
    }

    private static function boot(): void
    {
        if (self::$registry === null) {
            self::$registry = self::DEFAULT_ENUMS;
        }
    }
}
