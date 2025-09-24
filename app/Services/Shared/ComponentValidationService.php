<?php

declare(strict_types=1);

namespace App\Services\Shared;

/**
 * ComponentValidationService
 *
 * Service class containing ComponentValidationService business logic, external integrations, and complex operations with proper error handling and logging.
 *
 * @property array $componentRules
 */
final class ComponentValidationService
{
    private array $componentRules = [];

    /**
     * Initialize the class instance with required dependencies.
     */
    public function __construct()
    {
        $this->initializeValidationRules();
    }

    /**
     * Handle validateComponent functionality with proper error handling.
     */
    public function validateComponent(string $component, array $props): array
    {
        $rules = $this->componentRules[$component] ?? [];
        $errors = [];
        foreach ($rules as $prop => $rule) {
            $value = $props[$prop] ?? null;
            $error = $this->validateProp($prop, $value, $rule);
            if ($error) {
                $errors[$prop] = $error;
            }
        }

        return $errors;
    }

    /**
     * Handle getComponentSchema functionality with proper error handling.
     */
    public function getComponentSchema(string $component): array
    {
        return $this->componentRules[$component] ?? [];
    }

    /**
     * Handle getAllComponentSchemas functionality with proper error handling.
     */
    public function getAllComponentSchemas(): array
    {
        return $this->componentRules;
    }

    /**
     * Handle validateProp functionality with proper error handling.
     */
    private function validateProp(string $prop, mixed $value, array $rule): ?string
    {
        // Required validation
        if (($rule['required'] ?? false) && $this->isEmpty($value)) {
            return "The {$prop} field is required.";
        }
        // Skip other validations if value is empty and not required
        if ($this->isEmpty($value)) {
            return null;
        }
        // Type validation
        if (isset($rule['type'])) {
            $typeError = $this->validateType($prop, $value, $rule['type']);
            if ($typeError) {
                return $typeError;
            }
        }
        // Enum validation
        if (isset($rule['enum'])) {
            if (! in_array($value, $rule['enum'])) {
                $validOptions = implode(', ', $rule['enum']);

                return "The {$prop} must be one of: {$validOptions}.";
            }
        }
        // Length validation
        if (isset($rule['max_length']) && is_string($value)) {
            if (strlen($value) > $rule['max_length']) {
                return "The {$prop} may not be greater than {$rule['max_length']} characters.";
            }
        }
        if (isset($rule['min_length']) && is_string($value)) {
            if (strlen($value) < $rule['min_length']) {
                return "The {$prop} must be at least {$rule['min_length']} characters.";
            }
        }

        return null;
    }

    /**
     * Handle validateType functionality with proper error handling.
     */
    private function validateType(string $prop, mixed $value, string $expectedType): ?string
    {
        $actualType = gettype($value);
        $typeMap = ['string' => 'string', 'int' => 'integer', 'integer' => 'integer', 'bool' => 'boolean', 'boolean' => 'boolean', 'array' => 'array', 'object' => 'object'];
        $expectedPhpType = $typeMap[$expectedType] ?? $expectedType;
        if ($actualType !== $expectedPhpType) {
            return "The {$prop} must be of type {$expectedType}.";
        }

        return null;
    }

    /**
     * Handle isEmpty functionality with proper error handling.
     */
    private function isEmpty(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        if (is_array($value) && empty($value)) {
            return true;
        }

        return false;
    }

    /**
     * Handle initializeValidationRules functionality with proper error handling.
     */
    private function initializeValidationRules(): void
    {
        $this->componentRules = ['shared.button' => ['variant' => ['type' => 'string', 'enum' => ['primary', 'secondary', 'danger', 'success', 'warning', 'ghost'], 'default' => 'primary'], 'size' => ['type' => 'string', 'enum' => ['sm', 'md', 'lg', 'xl'], 'default' => 'md'], 'href' => ['type' => 'string'], 'icon' => ['type' => 'string'], 'iconPosition' => ['type' => 'string', 'enum' => ['left', 'right'], 'default' => 'left'], 'loading' => ['type' => 'boolean', 'default' => false], 'disabled' => ['type' => 'boolean', 'default' => false]], 'shared.card' => ['padding' => ['type' => 'string', 'enum' => ['p-4', 'p-6', 'p-8'], 'default' => 'p-6'], 'shadow' => ['type' => 'string', 'enum' => ['shadow-sm', 'shadow-md', 'shadow-lg', 'shadow-xl'], 'default' => 'shadow-md'], 'rounded' => ['type' => 'string', 'enum' => ['rounded-md', 'rounded-lg', 'rounded-xl'], 'default' => 'rounded-lg'], 'hover' => ['type' => 'boolean', 'default' => true], 'border' => ['type' => 'boolean', 'default' => true]], 'shared.input' => ['type' => ['type' => 'string', 'enum' => ['text', 'email', 'password', 'search', 'number', 'tel', 'url'], 'default' => 'text'], 'label' => ['type' => 'string'], 'placeholder' => ['type' => 'string'], 'required' => ['type' => 'boolean', 'default' => false], 'error' => ['type' => 'string'], 'helpText' => ['type' => 'string'], 'icon' => ['type' => 'string'], 'iconPosition' => ['type' => 'string', 'enum' => ['left', 'right'], 'default' => 'left'], 'size' => ['type' => 'string', 'enum' => ['sm', 'md', 'lg'], 'default' => 'md']], 'shared.badge' => ['variant' => ['type' => 'string', 'enum' => ['primary', 'secondary', 'success', 'warning', 'danger', 'info', 'gray'], 'default' => 'primary'], 'size' => ['type' => 'string', 'enum' => ['sm', 'md', 'lg'], 'default' => 'md'], 'rounded' => ['type' => 'string', 'enum' => ['rounded-md', 'rounded-lg', 'rounded-full'], 'default' => 'rounded-full']], 'shared.product-card' => ['product' => ['type' => 'object', 'required' => true], 'showQuickAdd' => ['type' => 'boolean', 'default' => true], 'showWishlist' => ['type' => 'boolean', 'default' => true], 'showCompare' => ['type' => 'boolean', 'default' => true], 'showBrand' => ['type' => 'boolean', 'default' => true], 'showRating' => ['type' => 'boolean', 'default' => true], 'layout' => ['type' => 'string', 'enum' => ['grid', 'list'], 'default' => 'grid']], 'shared.section' => ['title' => ['type' => 'string'], 'description' => ['type' => 'string'], 'icon' => ['type' => 'string'], 'iconColor' => ['type' => 'string', 'default' => 'text-blue-600'], 'titleSize' => ['type' => 'string', 'enum' => ['text-lg', 'text-xl', 'text-2xl', 'text-3xl'], 'default' => 'text-2xl'], 'centered' => ['type' => 'boolean', 'default' => false]], 'shared.empty-state' => ['title' => ['type' => 'string', 'required' => true], 'description' => ['type' => 'string'], 'icon' => ['type' => 'string', 'default' => 'heroicon-o-cube'], 'actionText' => ['type' => 'string'], 'actionUrl' => ['type' => 'string'], 'actionWire' => ['type' => 'string']]];
    }
}
