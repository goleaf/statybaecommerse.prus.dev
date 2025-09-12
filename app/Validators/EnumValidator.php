<?php

declare(strict_types=1);

namespace App\Validators;

use App\Enums\AddressType;
use App\Enums\NavigationGroup;
use App\Enums\OrderStatus;
use App\Enums\PaymentType;
use App\Enums\ProductStatus;
use App\Enums\UserRole;

final class EnumValidator
{
    /**
     * Validate enum value
     */
    public static function validateEnumValue(string $enumName, string $value): bool
    {
        $enumClass = self::getEnumClass($enumName);

        if (! $enumClass) {
            return false;
        }

        return in_array($value, $enumClass::values());
    }

    /**
     * Validate enum label
     */
    public static function validateEnumLabel(string $enumName, string $label): bool
    {
        $enumClass = self::getEnumClass($enumName);

        if (! $enumClass) {
            return false;
        }

        return in_array($label, $enumClass::labels());
    }

    /**
     * Validate enum case
     */
    public static function validateEnumCase(string $enumName, mixed $case): bool
    {
        $enumClass = self::getEnumClass($enumName);

        if (! $enumClass) {
            return false;
        }

        if (is_string($case)) {
            return self::validateEnumValue($enumName, $case);
        }

        if (is_object($case) && $case instanceof $enumClass) {
            return true;
        }

        return false;
    }

    /**
     * Validate enum array
     */
    public static function validateEnumArray(string $enumName, array $values): bool
    {
        $enumClass = self::getEnumClass($enumName);

        if (! $enumClass) {
            return false;
        }

        $validValues = $enumClass::values();

        foreach ($values as $value) {
            if (! in_array($value, $validValues)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate enum object
     */
    public static function validateEnumObject(string $enumName, object $object): bool
    {
        $enumClass = self::getEnumClass($enumName);

        if (! $enumClass) {
            return false;
        }

        return $object instanceof $enumClass;
    }

    /**
     * Validate enum collection
     */
    public static function validateEnumCollection(string $enumName, iterable $collection): bool
    {
        $enumClass = self::getEnumClass($enumName);

        if (! $enumClass) {
            return false;
        }

        foreach ($collection as $item) {
            if (! ($item instanceof $enumClass)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate enum request data
     */
    public static function validateEnumRequest(string $enumName, mixed $requestData): bool
    {
        if (is_string($requestData)) {
            return self::validateEnumValue($enumName, $requestData);
        }

        if (is_array($requestData)) {
            if (isset($requestData['value'])) {
                return self::validateEnumValue($enumName, $requestData['value']);
            }

            if (isset($requestData['label'])) {
                return self::validateEnumLabel($enumName, $requestData['label']);
            }

            return self::validateEnumArray($enumName, $requestData);
        }

        if (is_object($requestData)) {
            return self::validateEnumObject($enumName, $requestData);
        }

        return false;
    }

    /**
     * Validate enum database value
     */
    public static function validateEnumDatabase(string $enumName, mixed $databaseValue): bool
    {
        if ($databaseValue === null) {
            return true; // Null values are allowed
        }

        return self::validateEnumValue($enumName, (string) $databaseValue);
    }

    /**
     * Validate enum API data
     */
    public static function validateEnumApi(string $enumName, array $apiData): bool
    {
        if (isset($apiData['value'])) {
            return self::validateEnumValue($enumName, $apiData['value']);
        }

        if (isset($apiData['label'])) {
            return self::validateEnumLabel($enumName, $apiData['label']);
        }

        return false;
    }

    /**
     * Validate enum form data
     */
    public static function validateEnumForm(string $enumName, mixed $formData): bool
    {
        if (is_string($formData) && ! empty($formData)) {
            return self::validateEnumValue($enumName, $formData);
        }

        return true; // Empty values are allowed
    }

    /**
     * Validate enum query parameter
     */
    public static function validateEnumQuery(string $enumName, mixed $queryValue): bool
    {
        if (is_string($queryValue) && ! empty($queryValue)) {
            return self::validateEnumValue($enumName, $queryValue);
        }

        return true; // Empty values are allowed
    }

    /**
     * Validate enum session data
     */
    public static function validateEnumSession(string $enumName, mixed $sessionValue): bool
    {
        if (is_string($sessionValue) && ! empty($sessionValue)) {
            return self::validateEnumValue($enumName, $sessionValue);
        }

        return true; // Empty values are allowed
    }

    /**
     * Validate enum cookie data
     */
    public static function validateEnumCookie(string $enumName, mixed $cookieValue): bool
    {
        if (is_string($cookieValue) && ! empty($cookieValue)) {
            return self::validateEnumValue($enumName, $cookieValue);
        }

        return true; // Empty values are allowed
    }

    /**
     * Validate enum configuration
     */
    public static function validateEnumConfig(string $enumName, mixed $configValue): bool
    {
        if ($configValue === null) {
            return true; // Null values are allowed
        }

        return self::validateEnumValue($enumName, (string) $configValue);
    }

    /**
     * Validate enum environment variable
     */
    public static function validateEnumEnv(string $enumName, mixed $envValue): bool
    {
        if ($envValue === null) {
            return true; // Null values are allowed
        }

        return self::validateEnumValue($enumName, (string) $envValue);
    }

    /**
     * Validate enum with custom rules
     */
    public static function validateEnumWithRules(string $enumName, mixed $value, array $rules): bool
    {
        if (! self::validateEnumValue($enumName, (string) $value)) {
            return false;
        }

        $enumClass = self::getEnumClass($enumName);
        $enum = $enumClass::from((string) $value);

        foreach ($rules as $rule => $expected) {
            $method = 'is'.ucfirst($rule);

            if (method_exists($enum, $method)) {
                $actual = $enum->$method();

                if ($actual !== $expected) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Validate enum with multiple rules
     */
    public static function validateEnumWithMultipleRules(string $enumName, mixed $value, array $rules): bool
    {
        if (! self::validateEnumValue($enumName, (string) $value)) {
            return false;
        }

        $enumClass = self::getEnumClass($enumName);
        $enum = $enumClass::from((string) $value);

        foreach ($rules as $rule) {
            $method = 'is'.ucfirst($rule);

            if (method_exists($enum, $method)) {
                if (! $enum->$method()) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Validate enum with any rule
     */
    public static function validateEnumWithAnyRule(string $enumName, mixed $value, array $rules): bool
    {
        if (! self::validateEnumValue($enumName, (string) $value)) {
            return false;
        }

        $enumClass = self::getEnumClass($enumName);
        $enum = $enumClass::from((string) $value);

        foreach ($rules as $rule) {
            $method = 'is'.ucfirst($rule);

            if (method_exists($enum, $method)) {
                if ($enum->$method()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Validate enum with custom callback
     */
    public static function validateEnumWithCallback(string $enumName, mixed $value, callable $callback): bool
    {
        if (! self::validateEnumValue($enumName, (string) $value)) {
            return false;
        }

        $enumClass = self::getEnumClass($enumName);
        $enum = $enumClass::from((string) $value);

        return $callback($enum);
    }

    /**
     * Validate enum with custom validation
     */
    public static function validateEnumWithValidation(string $enumName, mixed $value, array $validation): bool
    {
        if (! self::validateEnumValue($enumName, (string) $value)) {
            return false;
        }

        $enumClass = self::getEnumClass($enumName);
        $enum = $enumClass::from((string) $value);

        foreach ($validation as $rule => $expected) {
            if (is_string($rule)) {
                $method = 'is'.ucfirst($rule);

                if (method_exists($enum, $method)) {
                    $actual = $enum->$method();

                    if ($actual !== $expected) {
                        return false;
                    }
                }
            } else {
                $method = 'is'.ucfirst($rule);

                if (method_exists($enum, $method)) {
                    if (! $enum->$method()) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Validate enum with custom validation rules
     */
    public static function validateEnumWithValidationRules(string $enumName, mixed $value, array $validationRules): bool
    {
        if (! self::validateEnumValue($enumName, (string) $value)) {
            return false;
        }

        $enumClass = self::getEnumClass($enumName);
        $enum = $enumClass::from((string) $value);

        foreach ($validationRules as $rule => $expected) {
            if (is_string($rule)) {
                $method = 'is'.ucfirst($rule);

                if (method_exists($enum, $method)) {
                    $actual = $enum->$method();

                    if ($actual !== $expected) {
                        return false;
                    }
                }
            } else {
                $method = 'is'.ucfirst($rule);

                if (method_exists($enum, $method)) {
                    if (! $enum->$method()) {
                        return false;
                    }
                }
            }
        }

        return true;
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

        if (! $enumClass) {
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

        if (! $enumClass) {
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

        if (! $enumClass) {
            return [];
        }

        return $enumClass::options();
    }

    /**
     * Get enum validation rules
     */
    public static function getEnumValidationRules(string $enumName): string
    {
        $enumClass = self::getEnumClass($enumName);

        if (! $enumClass) {
            return '';
        }

        return $enumClass::forValidation();
    }

    /**
     * Get enum database rules
     */
    public static function getEnumDatabaseRules(string $enumName): string
    {
        $enumClass = self::getEnumClass($enumName);

        if (! $enumClass) {
            return '';
        }

        return $enumClass::forDatabase();
    }

    /**
     * Get enum validation rules for Laravel
     */
    public static function getLaravelValidationRules(string $enumName): string
    {
        return self::getEnumValidationRules($enumName);
    }

    /**
     * Get enum validation rules for Laravel with nullable
     */
    public static function getLaravelValidationRulesNullable(string $enumName): string
    {
        return 'nullable|'.self::getEnumValidationRules($enumName);
    }

    /**
     * Get enum validation rules for Laravel with required
     */
    public static function getLaravelValidationRulesRequired(string $enumName): string
    {
        return 'required|'.self::getEnumValidationRules($enumName);
    }

    /**
     * Get enum validation rules for Laravel with sometimes
     */
    public static function getLaravelValidationRulesSometimes(string $enumName): string
    {
        return 'sometimes|'.self::getEnumValidationRules($enumName);
    }

    /**
     * Get enum validation rules for Laravel with present
     */
    public static function getLaravelValidationRulesPresent(string $enumName): string
    {
        return 'present|'.self::getEnumValidationRules($enumName);
    }

    /**
     * Get enum validation rules for Laravel with filled
     */
    public static function getLaravelValidationRulesFilled(string $enumName): string
    {
        return 'filled|'.self::getEnumValidationRules($enumName);
    }

    /**
     * Get enum validation rules for Laravel with required if
     */
    public static function getLaravelValidationRulesRequiredIf(string $enumName, string $field, mixed $value): string
    {
        return 'required_if:'.$field.','.$value.'|'.self::getEnumValidationRules($enumName);
    }

    /**
     * Get enum validation rules for Laravel with required unless
     */
    public static function getLaravelValidationRulesRequiredUnless(string $enumName, string $field, mixed $value): string
    {
        return 'required_unless:'.$field.','.$value.'|'.self::getEnumValidationRules($enumName);
    }

    /**
     * Get enum validation rules for Laravel with required with
     */
    public static function getLaravelValidationRulesRequiredWith(string $enumName, string $field): string
    {
        return 'required_with:'.$field.'|'.self::getEnumValidationRules($enumName);
    }

    /**
     * Get enum validation rules for Laravel with required with all
     */
    public static function getLaravelValidationRulesRequiredWithAll(string $enumName, array $fields): string
    {
        return 'required_with_all:'.implode(',', $fields).'|'.self::getEnumValidationRules($enumName);
    }

    /**
     * Get enum validation rules for Laravel with required without
     */
    public static function getLaravelValidationRulesRequiredWithout(string $enumName, string $field): string
    {
        return 'required_without:'.$field.'|'.self::getEnumValidationRules($enumName);
    }

    /**
     * Get enum validation rules for Laravel with required without all
     */
    public static function getLaravelValidationRulesRequiredWithoutAll(string $enumName, array $fields): string
    {
        return 'required_without_all:'.implode(',', $fields).'|'.self::getEnumValidationRules($enumName);
    }
}
