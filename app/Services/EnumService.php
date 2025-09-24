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

/**
 * EnumService
 *
 * Service class containing EnumService business logic, external integrations, and complex operations with proper error handling and logging.
 */
final class EnumService
{
    /**
     * Handle getAllEnums functionality with proper error handling.
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
     * Handle getEnum functionality with proper error handling.
     */
    public function getEnum(string $name): ?string
    {
        return $this->getAllEnums()[$name] ?? null;
    }

    /**
     * Handle getEnumOptions functionality with proper error handling.
     */
    public function getEnumOptions(string $name): array
    {
        $enumClass = $this->getEnum($name);
        if (! $enumClass || ! class_exists($enumClass)) {
            return [];
        }

        return is_callable([$enumClass, 'options']) ? $enumClass::options() : [];
    }

    /**
     * Handle getEnumOptionsWithDescriptions functionality with proper error handling.
     */
    public function getEnumOptionsWithDescriptions(string $name): array
    {
        $enumClass = $this->getEnum($name);
        if (! $enumClass || ! class_exists($enumClass)) {
            return [];
        }

        return is_callable([$enumClass, 'optionsWithDescriptions']) ? $enumClass::optionsWithDescriptions() : [];
    }

    /**
     * Handle getEnumCase functionality with proper error handling.
     */
    public function getEnumCase(string $name, string $value): ?EnumInterface
    {
        $enumClass = $this->getEnum($name);
        if (! $enumClass || ! class_exists($enumClass)) {
            return null;
        }
        try {
            return is_callable([$enumClass, 'from']) ? $enumClass::from($value) : null;
        } catch (\ValueError $e) {
            return null;
        }
    }

    /**
     * Handle getEnumCaseByLabel functionality with proper error handling.
     */
    public function getEnumCaseByLabel(string $name, string $label): ?EnumInterface
    {
        $enumClass = $this->getEnum($name);
        if (! $enumClass || ! class_exists($enumClass)) {
            return null;
        }

        return is_callable([$enumClass, 'fromLabel']) ? $enumClass::fromLabel($label) : null;
    }

    /**
     * Handle getAllEnumData functionality with proper error handling.
     */
    public function getAllEnumData(): array
    {
        $data = [];
        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass) && is_callable([$enumClass, 'optionsWithDescriptions'])) {
                $data[$name] = $enumClass::optionsWithDescriptions();
            }
        }

        return $data;
    }

    /**
     * Handle getEnumData functionality with proper error handling.
     */
    public function getEnumData(array $names): array
    {
        $data = [];
        foreach ($names as $name) {
            $enumClass = $this->getEnum($name);
            if ($enumClass && class_exists($enumClass) && is_callable([$enumClass, 'optionsWithDescriptions'])) {
                $data[$name] = $enumClass::optionsWithDescriptions();
            }
        }

        return $data;
    }

    /**
     * Handle getEnumStatistics functionality with proper error handling.
     */
    public function getEnumStatistics(): array
    {
        $statistics = [];
        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (! class_exists($enumClass)) {
                continue;
            }
            $statistics[$name] = [
                'total' => is_callable([$enumClass, 'count']) ? $enumClass::count() : 0,
                'values' => is_callable([$enumClass, 'values']) ? $enumClass::values() : [],
                'labels' => is_callable([$enumClass, 'labels']) ? $enumClass::labels() : [],
            ];
        }

        return $statistics;
    }

    /**
     * Handle getValidationRules functionality with proper error handling.
     */
    public function getValidationRules(): array
    {
        $rules = [];
        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass) && is_callable([$enumClass, 'forValidation'])) {
                $rules[$name] = $enumClass::forValidation();
            }
        }

        return $rules;
    }

    /**
     * Handle getDatabaseEnums functionality with proper error handling.
     */
    public function getDatabaseEnums(): array
    {
        $enums = [];
        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass) && is_callable([$enumClass, 'forDatabase'])) {
                $enums[$name] = $enumClass::forDatabase();
            }
        }

        return $enums;
    }

    /**
     * Handle getTypeScriptEnums functionality with proper error handling.
     */
    public function getTypeScriptEnums(): array
    {
        $enums = [];
        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass) && is_callable([$enumClass, 'forTypeScript'])) {
                $enums[$name] = $enumClass::forTypeScript();
            }
        }

        return $enums;
    }

    /**
     * Handle getJavaScriptEnums functionality with proper error handling.
     */
    public function getJavaScriptEnums(): array
    {
        $enums = [];
        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass) && is_callable([$enumClass, 'forJavaScript'])) {
                $enums[$name] = $enumClass::forJavaScript();
            }
        }

        return $enums;
    }

    /**
     * Handle getCssEnums functionality with proper error handling.
     */
    public function getCssEnums(): array
    {
        $enums = [];
        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass) && is_callable([$enumClass, 'forCss'])) {
                $enums[$name] = $enumClass::forCss();
            }
        }

        return $enums;
    }

    /**
     * Handle getDocumentation functionality with proper error handling.
     */
    public function getDocumentation(): array
    {
        $documentation = [];
        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass) && is_callable([$enumClass, 'forDocumentation'])) {
                $documentation[$name] = $enumClass::forDocumentation();
            }
        }

        return $documentation;
    }

    /**
     * Handle search functionality with proper error handling.
     */
    public function search(string $query): array
    {
        $results = [];
        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass) && is_callable([$enumClass, 'search'])) {
                $searchResults = $enumClass::search($query);
                if (method_exists($searchResults, 'isNotEmpty') ? $searchResults->isNotEmpty() : ! empty($searchResults)) {
                    $results[$name] = method_exists($searchResults, 'map')
                        ? $searchResults->map(fn ($case) => $case->toArray())->toArray()
                        : (array) $searchResults;
                }
            }
        }

        return $results;
    }

    /**
     * Handle groupBy functionality with proper error handling.
     */
    public function groupBy(string $property): array
    {
        $grouped = [];
        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass) && is_callable([$enumClass, 'groupBy'])) {
                $grouped[$name] = $enumClass::groupBy($property)->toArray();
            }
        }

        return $grouped;
    }

    /**
     * Handle sortBy functionality with proper error handling.
     */
    public function sortBy(string $property, bool $descending = false): array
    {
        $sorted = [];
        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass) && is_callable([$enumClass, 'sortBy'])) {
                $sorted[$name] = $enumClass::sortBy($property, $descending)->toArray();
            }
        }

        return $sorted;
    }

    /**
     * Handle paginate functionality with proper error handling.
     */
    public function paginate(string $name, int $perPage, int $page = 1): array
    {
        $enumClass = $this->getEnum($name);
        if (! $enumClass || ! class_exists($enumClass) || ! is_callable([$enumClass, 'paginate'])) {
            return [];
        }

        return $enumClass::paginate($perPage, $page);
    }

    /**
     * Handle getApiData functionality with proper error handling.
     */
    public function getApiData(): array
    {
        $data = [];
        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass) && is_callable([$enumClass, 'forApi'])) {
                $data[$name] = $enumClass::forApi();
            }
        }

        return $data;
    }

    /**
     * Handle getGraphQLData functionality with proper error handling.
     */
    public function getGraphQLData(): array
    {
        $data = [];
        foreach ($this->getAllEnums() as $name => $enumClass) {
            if (class_exists($enumClass) && is_callable([$enumClass, 'forGraphQL'])) {
                $data[$name] = $enumClass::forGraphQL();
            }
        }

        return $data;
    }

    /**
     * Handle getForUseCase functionality with proper error handling.
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
     */
    public function getForUseCaseAndEnums(string $useCase, array $names): array
    {
        $data = [];
        foreach ($names as $name) {
            $enumClass = $this->getEnum($name);
            if ($enumClass && class_exists($enumClass)) {
                $data[$name] = match ($useCase) {
                    'api' => is_callable([$enumClass, 'forApi']) ? $enumClass::forApi() : [],
                    'graphql' => is_callable([$enumClass, 'forGraphQL']) ? $enumClass::forGraphQL() : [],
                    'typescript' => is_callable([$enumClass, 'forTypeScript']) ? $enumClass::forTypeScript() : [],
                    'javascript' => is_callable([$enumClass, 'forJavaScript']) ? $enumClass::forJavaScript() : [],
                    'css' => is_callable([$enumClass, 'forCss']) ? $enumClass::forCss() : [],
                    'documentation' => is_callable([$enumClass, 'forDocumentation']) ? $enumClass::forDocumentation() : [],
                    'validation' => is_callable([$enumClass, 'forValidation']) ? $enumClass::forValidation() : [],
                    'database' => is_callable([$enumClass, 'forDatabase']) ? $enumClass::forDatabase() : [],
                    default => is_callable([$enumClass, 'optionsWithDescriptions']) ? $enumClass::optionsWithDescriptions() : [],
                };
            }
        }

        return $data;
    }
}
