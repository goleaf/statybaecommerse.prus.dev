<?php

declare (strict_types=1);
namespace App\Services;

use App\Contracts\EnumInterface;
use App\Enums\AddressType;
use App\Enums\NavigationGroup;
use App\Enums\OrderStatus;
use App\Enums\PaymentType;
use App\Enums\ProductStatus;
use App\Enums\UserRole;
/**
 * EnumService
 * 
 * Service class containing EnumService business logic, external integrations, and complex operations with proper error handling and logging.
 * 
 */
final class EnumService
{
    /**
     * Handle getAllEnums functionality with proper error handling.
     * @return array
     */
    public function getAllEnums(): array
    {
        return ['address_types' => AddressType::class, 'navigation_groups' => NavigationGroup::class, 'order_statuses' => OrderStatus::class, 'payment_types' => PaymentType::class, 'product_statuses' => ProductStatus::class, 'user_roles' => UserRole::class];
    }
    /**
     * Handle getEnum functionality with proper error handling.
     * @param string $name
     * @return string|null
     */
    public function getEnum(string $name): ?string
    {
        return $this->getAllEnums()[$name] ?? null;
    }
    /**
     * Handle getEnumOptions functionality with proper error handling.
     * @param string $name
     * @return array
     */
    public function getEnumOptions(string $name): array
    {
        $enumClass = $this->getEnum($name);
        if (!$enumClass || !class_exists($enumClass)) {
            return [];
        }
        return $enumClass::options();
    }
    /**
     * Handle getEnumOptionsWithDescriptions functionality with proper error handling.
     * @param string $name
     * @return array
     */
    public function getEnumOptionsWithDescriptions(string $name): array
    {
        $enumClass = $this->getEnum($name);
        if (!$enumClass || !class_exists($enumClass)) {
            return [];
        }
        return $enumClass::optionsWithDescriptions();
    }
    /**
     * Handle getEnumCase functionality with proper error handling.
     * @param string $name
     * @param string $value
     * @return EnumInterface|null
     */
    public function getEnumCase(string $name, string $value): ?EnumInterface
    {
        $enumClass = $this->getEnum($name);
        if (!$enumClass || !class_exists($enumClass)) {
            return null;
        }
        try {
            return $enumClass::from($value);
        } catch (\ValueError $e) {
            return null;
        }
    }
    /**
     * Handle getEnumCaseByLabel functionality with proper error handling.
     * @param string $name
     * @param string $label
     * @return EnumInterface|null
     */
    public function getEnumCaseByLabel(string $name, string $label): ?EnumInterface
    {
        $enumClass = $this->getEnum($name);
        if (!$enumClass || !class_exists($enumClass)) {
            return null;
        }
        return $enumClass::fromLabel($label);
    }
    /**
     * Handle getAllEnumData functionality with proper error handling.
     * @return array
     */
    public function getAllEnumData(): array
    {
        $data = [];
        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass)) {
                $data[$name] = $enumClass::optionsWithDescriptions();
            }
        }
        return $data;
    }
    /**
     * Handle getEnumData functionality with proper error handling.
     * @param array $names
     * @return array
     */
    public function getEnumData(array $names): array
    {
        $data = [];
        foreach ($names as $name) {
            $enumClass = $this->getEnum($name);
            if ($enumClass && class_exists($enumClass)) {
                $data[$name] = $enumClass::optionsWithDescriptions();
            }
        }
        return $data;
    }
    /**
     * Handle getEnumStatistics functionality with proper error handling.
     * @return array
     */
    public function getEnumStatistics(): array
    {
        $statistics = [];
        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass)) {
                $statistics[$name] = ['total' => $enumClass::count(), 'values' => $enumClass::values(), 'labels' => $enumClass::labels()];
            }
        }
        return $statistics;
    }
    /**
     * Handle getValidationRules functionality with proper error handling.
     * @return array
     */
    public function getValidationRules(): array
    {
        $rules = [];
        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass)) {
                $rules[$name] = $enumClass::forValidation();
            }
        }
        return $rules;
    }
    /**
     * Handle getDatabaseEnums functionality with proper error handling.
     * @return array
     */
    public function getDatabaseEnums(): array
    {
        $enums = [];
        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass)) {
                $enums[$name] = $enumClass::forDatabase();
            }
        }
        return $enums;
    }
    /**
     * Handle getTypeScriptEnums functionality with proper error handling.
     * @return array
     */
    public function getTypeScriptEnums(): array
    {
        $enums = [];
        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass)) {
                $enums[$name] = $enumClass::forTypeScript();
            }
        }
        return $enums;
    }
    /**
     * Handle getJavaScriptEnums functionality with proper error handling.
     * @return array
     */
    public function getJavaScriptEnums(): array
    {
        $enums = [];
        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass)) {
                $enums[$name] = $enumClass::forJavaScript();
            }
        }
        return $enums;
    }
    /**
     * Handle getCssEnums functionality with proper error handling.
     * @return array
     */
    public function getCssEnums(): array
    {
        $enums = [];
        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass)) {
                $enums[$name] = $enumClass::forCss();
            }
        }
        return $enums;
    }
    /**
     * Handle getDocumentation functionality with proper error handling.
     * @return array
     */
    public function getDocumentation(): array
    {
        $documentation = [];
        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass)) {
                $documentation[$name] = $enumClass::forDocumentation();
            }
        }
        return $documentation;
    }
    /**
     * Handle search functionality with proper error handling.
     * @param string $query
     * @return array
     */
    public function search(string $query): array
    {
        $results = [];
        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass)) {
                $searchResults = $enumClass::search($query);
                if ($searchResults->isNotEmpty()) {
                    $results[$name] = $searchResults->map(fn($case) => $case->toArray())->toArray();
                }
            }
        }
        return $results;
    }
    /**
     * Handle groupBy functionality with proper error handling.
     * @param string $property
     * @return array
     */
    public function groupBy(string $property): array
    {
        $grouped = [];
        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass)) {
                $grouped[$name] = $enumClass::groupBy($property)->toArray();
            }
        }
        return $grouped;
    }
    /**
     * Handle sortBy functionality with proper error handling.
     * @param string $property
     * @param bool $descending
     * @return array
     */
    public function sortBy(string $property, bool $descending = false): array
    {
        $sorted = [];
        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass)) {
                $sorted[$name] = $enumClass::sortBy($property, $descending)->toArray();
            }
        }
        return $sorted;
    }
    /**
     * Handle paginate functionality with proper error handling.
     * @param string $name
     * @param int $perPage
     * @param int $page
     * @return array
     */
    public function paginate(string $name, int $perPage, int $page = 1): array
    {
        $enumClass = $this->getEnum($name);
        if (!$enumClass || !class_exists($enumClass)) {
            return [];
        }
        return $enumClass::paginate($perPage, $page);
    }
    /**
     * Handle getApiData functionality with proper error handling.
     * @return array
     */
    public function getApiData(): array
    {
        $data = [];
        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass)) {
                $data[$name] = $enumClass::forApi();
            }
        }
        return $data;
    }
    /**
     * Handle getGraphQLData functionality with proper error handling.
     * @return array
     */
    public function getGraphQLData(): array
    {
        $data = [];
        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass)) {
                $data[$name] = $enumClass::forGraphQL();
            }
        }
        return $data;
    }
    /**
     * Handle getForUseCase functionality with proper error handling.
     * @param string $useCase
     * @return array
     */
    public function getForUseCase(string $useCase): array
    {
        return match ($useCase) {
            'api' => $this->getApiData(),
            'graphql' => $this->getGraphQLData(),
            'typescript' => $this->getTypeScriptEnums(),
            'javascript' => $this->getJavaScriptEnums(),
            'css' => $this->getCssEnums(),
            'documentation' => $this->getDocumentation(),
            'validation' => $this->getValidationRules(),
            'database' => $this->getDatabaseEnums(),
            default => $this->getAllEnumData(),
        };
    }
    /**
     * Handle getForUseCaseAndEnums functionality with proper error handling.
     * @param string $useCase
     * @param array $names
     * @return array
     */
    public function getForUseCaseAndEnums(string $useCase, array $names): array
    {
        $data = [];
        foreach ($names as $name) {
            $enumClass = $this->getEnum($name);
            if ($enumClass && class_exists($enumClass)) {
                $data[$name] = match ($useCase) {
                    'api' => $enumClass::forApi(),
                    'graphql' => $enumClass::forGraphQL(),
                    'typescript' => $enumClass::forTypeScript(),
                    'javascript' => $enumClass::forJavaScript(),
                    'css' => $enumClass::forCss(),
                    'documentation' => $enumClass::forDocumentation(),
                    'validation' => $enumClass::forValidation(),
                    'database' => $enumClass::forDatabase(),
                    default => $enumClass::optionsWithDescriptions(),
                };
            }
        }
        return $data;
    }
}