<?php declare(strict_types=1);

namespace App\Factories;

use App\Contracts\EnumInterface;
use App\Enums\AddressType;
use App\Enums\NavigationGroup;
use App\Enums\OrderStatus;
use App\Enums\PaymentType;
use App\Enums\ProductStatus;
use App\Enums\UserRole;
use InvalidArgumentException;

final class EnumFactory
{
    /**
     * Create enum instance by name and value
     */
    public static function create(string $enumName, string $value): EnumInterface
    {
        $enumClass = self::getEnumClass($enumName);
        
        if (!$enumClass) {
            throw new InvalidArgumentException("Enum '{$enumName}' not found");
        }

        try {
            return $enumClass::from($value);
        } catch (\ValueError $e) {
            throw new InvalidArgumentException("Invalid value '{$value}' for enum '{$enumName}'");
        }
    }

    /**
     * Create enum instance by name and label
     */
    public static function createByLabel(string $enumName, string $label): EnumInterface
    {
        $enumClass = self::getEnumClass($enumName);
        
        if (!$enumClass) {
            throw new InvalidArgumentException("Enum '{$enumName}' not found");
        }

        $enum = $enumClass::fromLabel($label);
        
        if (!$enum) {
            throw new InvalidArgumentException("Invalid label '{$label}' for enum '{$enumName}'");
        }

        return $enum;
    }

    /**
     * Create enum instance from array data
     */
    public static function createFromArray(string $enumName, array $data): EnumInterface
    {
        if (!isset($data['value'])) {
            throw new InvalidArgumentException("Array must contain 'value' key");
        }

        return self::create($enumName, $data['value']);
    }

    /**
     * Create multiple enum instances
     */
    public static function createMultiple(string $enumName, array $values): array
    {
        $enums = [];
        
        foreach ($values as $value) {
            $enums[] = self::create($enumName, $value);
        }
        
        return $enums;
    }

    /**
     * Create enum instances from array of arrays
     */
    public static function createMultipleFromArrays(string $enumName, array $dataArray): array
    {
        $enums = [];
        
        foreach ($dataArray as $data) {
            $enums[] = self::createFromArray($enumName, $data);
        }
        
        return $enums;
    }

    /**
     * Create enum instance with validation
     */
    public static function createWithValidation(string $enumName, string $value, array $rules = []): EnumInterface
    {
        $enum = self::create($enumName, $value);
        
        // Apply custom validation rules
        foreach ($rules as $rule => $expected) {
            $method = 'is' . ucfirst($rule);
            
            if (method_exists($enum, $method)) {
                $actual = $enum->$method();
                
                if ($actual !== $expected) {
                    throw new InvalidArgumentException(
                        "Enum '{$enumName}' with value '{$value}' failed validation rule '{$rule}'"
                    );
                }
            }
        }
        
        return $enum;
    }

    /**
     * Create enum instance with fallback
     */
    public static function createWithFallback(string $enumName, string $value, string $fallbackValue): EnumInterface
    {
        try {
            return self::create($enumName, $value);
        } catch (InvalidArgumentException $e) {
            return self::create($enumName, $fallbackValue);
        }
    }

    /**
     * Create enum instance with default
     */
    public static function createWithDefault(string $enumName, ?string $value = null): EnumInterface
    {
        if ($value === null) {
            $enumClass = self::getEnumClass($enumName);
            
            if (!$enumClass) {
                throw new InvalidArgumentException("Enum '{$enumName}' not found");
            }

            return $enumClass::first();
        }

        return self::create($enumName, $value);
    }

    /**
     * Create enum instance from request data
     */
    public static function createFromRequest(string $enumName, mixed $requestValue): EnumInterface
    {
        if (is_array($requestValue)) {
            return self::createFromArray($enumName, $requestValue);
        }

        if (is_string($requestValue)) {
            return self::create($enumName, $requestValue);
        }

        throw new InvalidArgumentException("Invalid request value type for enum '{$enumName}'");
    }

    /**
     * Create enum instance from database value
     */
    public static function createFromDatabase(string $enumName, mixed $databaseValue): EnumInterface
    {
        if ($databaseValue === null) {
            return self::createWithDefault($enumName);
        }

        return self::create($enumName, (string) $databaseValue);
    }

    /**
     * Create enum instance from API data
     */
    public static function createFromApi(string $enumName, array $apiData): EnumInterface
    {
        if (isset($apiData['value'])) {
            return self::create($enumName, $apiData['value']);
        }

        if (isset($apiData['label'])) {
            return self::createByLabel($enumName, $apiData['label']);
        }

        throw new InvalidArgumentException("API data must contain 'value' or 'label' key");
    }

    /**
     * Create enum instance from form data
     */
    public static function createFromForm(string $enumName, mixed $formValue): EnumInterface
    {
        if (is_string($formValue) && !empty($formValue)) {
            return self::create($enumName, $formValue);
        }

        return self::createWithDefault($enumName);
    }

    /**
     * Create enum instance from query parameter
     */
    public static function createFromQuery(string $enumName, mixed $queryValue): EnumInterface
    {
        if (is_string($queryValue) && !empty($queryValue)) {
            return self::create($enumName, $queryValue);
        }

        return self::createWithDefault($enumName);
    }

    /**
     * Create enum instance from session data
     */
    public static function createFromSession(string $enumName, mixed $sessionValue): EnumInterface
    {
        if (is_string($sessionValue) && !empty($sessionValue)) {
            return self::create($enumName, $sessionValue);
        }

        return self::createWithDefault($enumName);
    }

    /**
     * Create enum instance from cookie data
     */
    public static function createFromCookie(string $enumName, mixed $cookieValue): EnumInterface
    {
        if (is_string($cookieValue) && !empty($cookieValue)) {
            return self::create($enumName, $cookieValue);
        }

        return self::createWithDefault($enumName);
    }

    /**
     * Create enum instance from configuration
     */
    public static function createFromConfig(string $enumName, string $configKey): EnumInterface
    {
        $value = config($configKey);
        
        if ($value === null) {
            return self::createWithDefault($enumName);
        }

        return self::create($enumName, (string) $value);
    }

    /**
     * Create enum instance from environment variable
     */
    public static function createFromEnv(string $enumName, string $envKey): EnumInterface
    {
        $value = env($envKey);
        
        if ($value === null) {
            return self::createWithDefault($enumName);
        }

        return self::create($enumName, (string) $value);
    }

    /**
     * Get enum class by name
     */
    private static function getEnumClass(string $enumName): ?string
    {
        return match ($enumName) {
            'address_type' => AddressType::class,
            'navigation_group' => NavigationGroup::class,
            'order_status' => OrderStatus::class,
            'payment_type' => PaymentType::class,
            'product_status' => ProductStatus::class,
            'user_role' => UserRole::class,
            default => null,
        };
    }

    /**
     * Get all available enum names
     */
    public static function getAvailableEnums(): array
    {
        return [
            'address_type',
            'navigation_group',
            'order_status',
            'payment_type',
            'product_status',
            'user_role',
        ];
    }

    /**
     * Check if enum exists
     */
    public static function exists(string $enumName): bool
    {
        return self::getEnumClass($enumName) !== null;
    }

    /**
     * Get enum class name
     */
    public static function getEnumClassName(string $enumName): ?string
    {
        return self::getEnumClass($enumName);
    }

    /**
     * Get enum values
     */
    public static function getEnumValues(string $enumName): array
    {
        $enumClass = self::getEnumClass($enumName);
        
        if (!$enumClass) {
            return [];
        }

        return $enumClass::values();
    }

    /**
     * Get enum labels
     */
    public static function getEnumLabels(string $enumName): array
    {
        $enumClass = self::getEnumClass($enumName);
        
        if (!$enumClass) {
            return [];
        }

        return $enumClass::labels();
    }

    /**
     * Get enum options
     */
    public static function getEnumOptions(string $enumName): array
    {
        $enumClass = self::getEnumClass($enumName);
        
        if (!$enumClass) {
            return [];
        }

        return $enumClass::options();
    }

    /**
     * Validate enum value
     */
    public static function validateValue(string $enumName, string $value): bool
    {
        $enumClass = self::getEnumClass($enumName);
        
        if (!$enumClass) {
            return false;
        }

        return in_array($value, $enumClass::values());
    }

    /**
     * Validate enum label
     */
    public static function validateLabel(string $enumName, string $label): bool
    {
        $enumClass = self::getEnumClass($enumName);
        
        if (!$enumClass) {
            return false;
        }

        return in_array($label, $enumClass::labels());
    }
}
