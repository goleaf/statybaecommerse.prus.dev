<?php

declare (strict_types=1);
namespace App\Validators;

use App\Enums\AddressType;
use App\Enums\NavigationGroup;
use App\Enums\OrderStatus;
use App\Enums\PaymentType;
use App\Enums\ProductStatus;
use App\Enums\UserRole;
/**
 * EnumValidator
 * 
 * Validation class for EnumValidator data validation with comprehensive rules and custom error messages.
 * 
 */
final class EnumValidator
{
    /**
     * Handle validateEnumValue functionality with proper error handling.
     * @param string $enumName
     * @param string $value
     * @return bool
     */
    public static function validateEnumValue(string $enumName, string $value): bool
    {
        $enumClass = self::getEnumClass($enumName);
        if (!$enumClass) {
            return false;
        }
        return in_array($value, $enumClass::values());
    }
    /**
     * Handle validateEnumLabel functionality with proper error handling.
     * @param string $enumName
     * @param string $label
     * @return bool
     */
    public static function validateEnumLabel(string $enumName, string $label): bool
    {
        $enumClass = self::getEnumClass($enumName);
        if (!$enumClass) {
            return false;
        }
        return in_array($label, $enumClass::labels());
    }
    /**
     * Handle validateEnumCase functionality with proper error handling.
     * @param string $enumName
     * @param mixed $case
     * @return bool
     */
    public static function validateEnumCase(string $enumName, mixed $case): bool
    {
        $enumClass = self::getEnumClass($enumName);
        if (!$enumClass) {
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
     * Handle validateEnumArray functionality with proper error handling.
     * @param string $enumName
     * @param array $values
     * @return bool
     */
    public static function validateEnumArray(string $enumName, array $values): bool
    {
        $enumClass = self::getEnumClass($enumName);
        if (!$enumClass) {
            return false;
        }
        $validValues = $enumClass::values();
        foreach ($values as $value) {
            if (!in_array($value, $validValues)) {
                return false;
            }
        }
        return true;
    }
    /**
     * Handle validateEnumObject functionality with proper error handling.
     * @param string $enumName
     * @param object $object
     * @return bool
     */
    public static function validateEnumObject(string $enumName, object $object): bool
    {
        $enumClass = self::getEnumClass($enumName);
        if (!$enumClass) {
            return false;
        }
        return $object instanceof $enumClass;
    }
    /**
     * Handle validateEnumCollection functionality with proper error handling.
     * @param string $enumName
     * @param iterable $collection
     * @return bool
     */
    public static function validateEnumCollection(string $enumName, iterable $collection): bool
    {
        $enumClass = self::getEnumClass($enumName);
        if (!$enumClass) {
            return false;
        }
        foreach ($collection as $item) {
            if (!$item instanceof $enumClass) {
                return false;
            }
        }
        return true;
    }
    /**
     * Handle validateEnumRequest functionality with proper error handling.
     * @param string $enumName
     * @param mixed $requestData
     * @return bool
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
     * Handle validateEnumDatabase functionality with proper error handling.
     * @param string $enumName
     * @param mixed $databaseValue
     * @return bool
     */
    public static function validateEnumDatabase(string $enumName, mixed $databaseValue): bool
    {
        if ($databaseValue === null) {
            return true;
            // Null values are allowed
        }
        return self::validateEnumValue($enumName, (string) $databaseValue);
    }
    /**
     * Handle validateEnumApi functionality with proper error handling.
     * @param string $enumName
     * @param array $apiData
     * @return bool
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
     * Handle validateEnumForm functionality with proper error handling.
     * @param string $enumName
     * @param mixed $formData
     * @return bool
     */
    public static function validateEnumForm(string $enumName, mixed $formData): bool
    {
        if (is_string($formData) && !empty($formData)) {
            return self::validateEnumValue($enumName, $formData);
        }
        return true;
        // Empty values are allowed
    }
    /**
     * Handle validateEnumQuery functionality with proper error handling.
     * @param string $enumName
     * @param mixed $queryValue
     * @return bool
     */
    public static function validateEnumQuery(string $enumName, mixed $queryValue): bool
    {
        if (is_string($queryValue) && !empty($queryValue)) {
            return self::validateEnumValue($enumName, $queryValue);
        }
        return true;
        // Empty values are allowed
    }
    /**
     * Handle validateEnumSession functionality with proper error handling.
     * @param string $enumName
     * @param mixed $sessionValue
     * @return bool
     */
    public static function validateEnumSession(string $enumName, mixed $sessionValue): bool
    {
        if (is_string($sessionValue) && !empty($sessionValue)) {
            return self::validateEnumValue($enumName, $sessionValue);
        }
        return true;
        // Empty values are allowed
    }
    /**
     * Handle validateEnumCookie functionality with proper error handling.
     * @param string $enumName
     * @param mixed $cookieValue
     * @return bool
     */
    public static function validateEnumCookie(string $enumName, mixed $cookieValue): bool
    {
        if (is_string($cookieValue) && !empty($cookieValue)) {
            return self::validateEnumValue($enumName, $cookieValue);
        }
        return true;
        // Empty values are allowed
    }
    /**
     * Handle validateEnumConfig functionality with proper error handling.
     * @param string $enumName
     * @param mixed $configValue
     * @return bool
     */
    public static function validateEnumConfig(string $enumName, mixed $configValue): bool
    {
        if ($configValue === null) {
            return true;
            // Null values are allowed
        }
        return self::validateEnumValue($enumName, (string) $configValue);
    }
    /**
     * Handle validateEnumEnv functionality with proper error handling.
     * @param string $enumName
     * @param mixed $envValue
     * @return bool
     */
    public static function validateEnumEnv(string $enumName, mixed $envValue): bool
    {
        if ($envValue === null) {
            return true;
            // Null values are allowed
        }
        return self::validateEnumValue($enumName, (string) $envValue);
    }
    /**
     * Handle validateEnumWithRules functionality with proper error handling.
     * @param string $enumName
     * @param mixed $value
     * @param array $rules
     * @return bool
     */
    public static function validateEnumWithRules(string $enumName, mixed $value, array $rules): bool
    {
        if (!self::validateEnumValue($enumName, (string) $value)) {
            return false;
        }
        $enumClass = self::getEnumClass($enumName);
        $enum = $enumClass::from((string) $value);
        foreach ($rules as $rule => $expected) {
            $method = 'is' . ucfirst($rule);
            if (method_exists($enum, $method)) {
                $actual = $enum->{$method}();
                if ($actual !== $expected) {
                    return false;
                }
            }
        }
        return true;
    }
    /**
     * Handle validateEnumWithMultipleRules functionality with proper error handling.
     * @param string $enumName
     * @param mixed $value
     * @param array $rules
     * @return bool
     */
    public static function validateEnumWithMultipleRules(string $enumName, mixed $value, array $rules): bool
    {
        if (!self::validateEnumValue($enumName, (string) $value)) {
            return false;
        }
        $enumClass = self::getEnumClass($enumName);
        $enum = $enumClass::from((string) $value);
        foreach ($rules as $rule) {
            $method = 'is' . ucfirst($rule);
            if (method_exists($enum, $method)) {
                if (!$enum->{$method}()) {
                    return false;
                }
            }
        }
        return true;
    }
    /**
     * Handle validateEnumWithAnyRule functionality with proper error handling.
     * @param string $enumName
     * @param mixed $value
     * @param array $rules
     * @return bool
     */
    public static function validateEnumWithAnyRule(string $enumName, mixed $value, array $rules): bool
    {
        if (!self::validateEnumValue($enumName, (string) $value)) {
            return false;
        }
        $enumClass = self::getEnumClass($enumName);
        $enum = $enumClass::from((string) $value);
        foreach ($rules as $rule) {
            $method = 'is' . ucfirst($rule);
            if (method_exists($enum, $method)) {
                if ($enum->{$method}()) {
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * Handle validateEnumWithCallback functionality with proper error handling.
     * @param string $enumName
     * @param mixed $value
     * @param callable $callback
     * @return bool
     */
    public static function validateEnumWithCallback(string $enumName, mixed $value, callable $callback): bool
    {
        if (!self::validateEnumValue($enumName, (string) $value)) {
            return false;
        }
        $enumClass = self::getEnumClass($enumName);
        $enum = $enumClass::from((string) $value);
        return $callback($enum);
    }
    /**
     * Handle validateEnumWithValidation functionality with proper error handling.
     * @param string $enumName
     * @param mixed $value
     * @param array $validation
     * @return bool
     */
    public static function validateEnumWithValidation(string $enumName, mixed $value, array $validation): bool
    {
        if (!self::validateEnumValue($enumName, (string) $value)) {
            return false;
        }
        $enumClass = self::getEnumClass($enumName);
        $enum = $enumClass::from((string) $value);
        foreach ($validation as $rule => $expected) {
            if (is_string($rule)) {
                $method = 'is' . ucfirst($rule);
                if (method_exists($enum, $method)) {
                    $actual = $enum->{$method}();
                    if ($actual !== $expected) {
                        return false;
                    }
                }
            } else {
                $method = 'is' . ucfirst($rule);
                if (method_exists($enum, $method)) {
                    if (!$enum->{$method}()) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
    /**
     * Handle validateEnumWithValidationRules functionality with proper error handling.
     * @param string $enumName
     * @param mixed $value
     * @param array $validationRules
     * @return bool
     */
    public static function validateEnumWithValidationRules(string $enumName, mixed $value, array $validationRules): bool
    {
        if (!self::validateEnumValue($enumName, (string) $value)) {
            return false;
        }
        $enumClass = self::getEnumClass($enumName);
        $enum = $enumClass::from((string) $value);
        foreach ($validationRules as $rule => $expected) {
            if (is_string($rule)) {
                $method = 'is' . ucfirst($rule);
                if (method_exists($enum, $method)) {
                    $actual = $enum->{$method}();
                    if ($actual !== $expected) {
                        return false;
                    }
                }
            } else {
                $method = 'is' . ucfirst($rule);
                if (method_exists($enum, $method)) {
                    if (!$enum->{$method}()) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
    /**
     * Handle getEnumClass functionality with proper error handling.
     * @param string $enumName
     * @return string|null
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
     * Handle getAvailableEnums functionality with proper error handling.
     * @return array
     */
    public static function getAvailableEnums(): array
    {
        return ['address_type', 'navigation_group', 'order_status', 'payment_type', 'product_status', 'user_role'];
    }
    /**
     * Handle exists functionality with proper error handling.
     * @param string $enumName
     * @return bool
     */
    public static function exists(string $enumName): bool
    {
        return self::getEnumClass($enumName) !== null;
    }
    /**
     * Handle getEnumClassName functionality with proper error handling.
     * @param string $enumName
     * @return string|null
     */
    public static function getEnumClassName(string $enumName): ?string
    {
        return self::getEnumClass($enumName);
    }
    /**
     * Handle getEnumValues functionality with proper error handling.
     * @param string $enumName
     * @return array
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
     * Handle getEnumLabels functionality with proper error handling.
     * @param string $enumName
     * @return array
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
     * Handle getEnumOptions functionality with proper error handling.
     * @param string $enumName
     * @return array
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
     * Handle getEnumValidationRules functionality with proper error handling.
     * @param string $enumName
     * @return string
     */
    public static function getEnumValidationRules(string $enumName): string
    {
        $enumClass = self::getEnumClass($enumName);
        if (!$enumClass) {
            return '';
        }
        return $enumClass::forValidation();
    }
    /**
     * Handle getEnumDatabaseRules functionality with proper error handling.
     * @param string $enumName
     * @return string
     */
    public static function getEnumDatabaseRules(string $enumName): string
    {
        $enumClass = self::getEnumClass($enumName);
        if (!$enumClass) {
            return '';
        }
        return $enumClass::forDatabase();
    }
    /**
     * Handle getLaravelValidationRules functionality with proper error handling.
     * @param string $enumName
     * @return string
     */
    public static function getLaravelValidationRules(string $enumName): string
    {
        return self::getEnumValidationRules($enumName);
    }
    /**
     * Handle getLaravelValidationRulesNullable functionality with proper error handling.
     * @param string $enumName
     * @return string
     */
    public static function getLaravelValidationRulesNullable(string $enumName): string
    {
        return 'nullable|' . self::getEnumValidationRules($enumName);
    }
    /**
     * Handle getLaravelValidationRulesRequired functionality with proper error handling.
     * @param string $enumName
     * @return string
     */
    public static function getLaravelValidationRulesRequired(string $enumName): string
    {
        return 'required|' . self::getEnumValidationRules($enumName);
    }
    /**
     * Handle getLaravelValidationRulesSometimes functionality with proper error handling.
     * @param string $enumName
     * @return string
     */
    public static function getLaravelValidationRulesSometimes(string $enumName): string
    {
        return 'sometimes|' . self::getEnumValidationRules($enumName);
    }
    /**
     * Handle getLaravelValidationRulesPresent functionality with proper error handling.
     * @param string $enumName
     * @return string
     */
    public static function getLaravelValidationRulesPresent(string $enumName): string
    {
        return 'present|' . self::getEnumValidationRules($enumName);
    }
    /**
     * Handle getLaravelValidationRulesFilled functionality with proper error handling.
     * @param string $enumName
     * @return string
     */
    public static function getLaravelValidationRulesFilled(string $enumName): string
    {
        return 'filled|' . self::getEnumValidationRules($enumName);
    }
    /**
     * Handle getLaravelValidationRulesRequiredIf functionality with proper error handling.
     * @param string $enumName
     * @param string $field
     * @param mixed $value
     * @return string
     */
    public static function getLaravelValidationRulesRequiredIf(string $enumName, string $field, mixed $value): string
    {
        return 'required_if:' . $field . ',' . $value . '|' . self::getEnumValidationRules($enumName);
    }
    /**
     * Handle getLaravelValidationRulesRequiredUnless functionality with proper error handling.
     * @param string $enumName
     * @param string $field
     * @param mixed $value
     * @return string
     */
    public static function getLaravelValidationRulesRequiredUnless(string $enumName, string $field, mixed $value): string
    {
        return 'required_unless:' . $field . ',' . $value . '|' . self::getEnumValidationRules($enumName);
    }
    /**
     * Handle getLaravelValidationRulesRequiredWith functionality with proper error handling.
     * @param string $enumName
     * @param string $field
     * @return string
     */
    public static function getLaravelValidationRulesRequiredWith(string $enumName, string $field): string
    {
        return 'required_with:' . $field . '|' . self::getEnumValidationRules($enumName);
    }
    /**
     * Handle getLaravelValidationRulesRequiredWithAll functionality with proper error handling.
     * @param string $enumName
     * @param array $fields
     * @return string
     */
    public static function getLaravelValidationRulesRequiredWithAll(string $enumName, array $fields): string
    {
        return 'required_with_all:' . implode(',', $fields) . '|' . self::getEnumValidationRules($enumName);
    }
    /**
     * Handle getLaravelValidationRulesRequiredWithout functionality with proper error handling.
     * @param string $enumName
     * @param string $field
     * @return string
     */
    public static function getLaravelValidationRulesRequiredWithout(string $enumName, string $field): string
    {
        return 'required_without:' . $field . '|' . self::getEnumValidationRules($enumName);
    }
    /**
     * Handle getLaravelValidationRulesRequiredWithoutAll functionality with proper error handling.
     * @param string $enumName
     * @param array $fields
     * @return string
     */
    public static function getLaravelValidationRulesRequiredWithoutAll(string $enumName, array $fields): string
    {
        return 'required_without_all:' . implode(',', $fields) . '|' . self::getEnumValidationRules($enumName);
    }
}