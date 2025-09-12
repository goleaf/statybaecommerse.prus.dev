<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\EnumInterface;
use App\Enums\AddressType;
use App\Enums\NavigationGroup;
use App\Enums\OrderStatus;
use App\Enums\PaymentType;
use App\Enums\ProductStatus;
use App\Enums\UserRole;

final class EnumService
{
    /**
     * Get all available enums
     */
    public function getAllEnums(): array
    {
        return [
            'address_types' => AddressType::class,
            'navigation_groups' => NavigationGroup::class,
            'order_statuses' => OrderStatus::class,
            'payment_types' => PaymentType::class,
            'product_statuses' => ProductStatus::class,
            'user_roles' => UserRole::class,
        ];
    }

    /**
     * Get enum by name
     */
    public function getEnum(string $name): ?string
    {
        return $this->getAllEnums()[$name] ?? null;
    }

    /**
     * Get enum options by name
     */
    public function getEnumOptions(string $name): array
    {
        $enumClass = $this->getEnum($name);

        if (! $enumClass || ! class_exists($enumClass)) {
            return [];
        }

        return $enumClass::options();
    }

    /**
     * Get enum options with descriptions by name
     */
    public function getEnumOptionsWithDescriptions(string $name): array
    {
        $enumClass = $this->getEnum($name);

        if (! $enumClass || ! class_exists($enumClass)) {
            return [];
        }

        return $enumClass::optionsWithDescriptions();
    }

    /**
     * Get enum case by name and value
     */
    public function getEnumCase(string $name, string $value): ?EnumInterface
    {
        $enumClass = $this->getEnum($name);

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
    public function getEnumCaseByLabel(string $name, string $label): ?EnumInterface
    {
        $enumClass = $this->getEnum($name);

        if (! $enumClass || ! class_exists($enumClass)) {
            return null;
        }

        return $enumClass::fromLabel($label);
    }

    /**
     * Get all enum data for API responses
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
     * Get enum data for specific enums
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
     * Get enum statistics
     */
    public function getEnumStatistics(): array
    {
        $statistics = [];

        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass)) {
                $statistics[$name] = [
                    'total' => $enumClass::count(),
                    'values' => $enumClass::values(),
                    'labels' => $enumClass::labels(),
                ];
            }
        }

        return $statistics;
    }

    /**
     * Get enum cases for form validation
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
     * Get enum cases for database migrations
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
     * Get enum cases for TypeScript
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
     * Get enum cases for JavaScript
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
     * Get enum cases for CSS
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
     * Get enum cases for documentation
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
     * Search across all enums
     */
    public function search(string $query): array
    {
        $results = [];

        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass)) {
                $searchResults = $enumClass::search($query);

                if ($searchResults->isNotEmpty()) {
                    $results[$name] = $searchResults->map(fn ($case) => $case->toArray())->toArray();
                }
            }
        }

        return $results;
    }

    /**
     * Get enum cases grouped by a property
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
     * Get enum cases sorted by a property
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
     * Get enum cases with pagination
     */
    public function paginate(string $name, int $perPage, int $page = 1): array
    {
        $enumClass = $this->getEnum($name);

        if (! $enumClass || ! class_exists($enumClass)) {
            return [];
        }

        return $enumClass::paginate($perPage, $page);
    }

    /**
     * Get enum cases for API responses
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
     * Get enum cases for GraphQL
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
     * Get enum cases for specific use cases
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
     * Get enum cases for specific enums and use case
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
