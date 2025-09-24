<?php

declare(strict_types=1);

namespace Tests\Feature;

/**
 * Test Configuration
 *
 * Centralized configuration for test execution
 */
class TestConfig
{
    /**
     * Test execution settings
     */
    public static function getExecutionSettings(): array
    {
        return [
            'stop_on_failure' => true,
            'parallel' => false,
            'coverage' => false,
            'verbose' => true,
            'memory_limit' => '1G',
            'timeout' => 300,
        ];
    }

    /**
     * Test database settings
     */
    public static function getDatabaseSettings(): array
    {
        return [
            'connection' => 'sqlite',
            'database' => ':memory:',
            'migrate' => true,
            'seed' => false,
        ];
    }

    /**
     * Test environment settings
     */
    public static function getEnvironmentSettings(): array
    {
        return [
            'APP_ENV' => 'testing',
            'APP_DEBUG' => true,
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
            'CACHE_DRIVER' => 'array',
            'SESSION_DRIVER' => 'array',
            'QUEUE_CONNECTION' => 'sync',
        ];
    }

    /**
     * Test groups configuration
     */
    public static function getGroupSettings(): array
    {
        return [
            'admin' => [
                'enabled' => true,
                'priority' => 1,
                'timeout' => 600,
                'memory_limit' => '2G',
            ],
            'widgets' => [
                'enabled' => true,
                'priority' => 2,
                'timeout' => 300,
                'memory_limit' => '1G',
            ],
            'api' => [
                'enabled' => true,
                'priority' => 3,
                'timeout' => 400,
                'memory_limit' => '1G',
            ],
            'integration' => [
                'enabled' => true,
                'priority' => 4,
                'timeout' => 800,
                'memory_limit' => '2G',
            ],
            'unit' => [
                'enabled' => true,
                'priority' => 5,
                'timeout' => 200,
                'memory_limit' => '512M',
            ],
            'controllers' => [
                'enabled' => true,
                'priority' => 6,
                'timeout' => 300,
                'memory_limit' => '1G',
            ],
            'models' => [
                'enabled' => true,
                'priority' => 7,
                'timeout' => 400,
                'memory_limit' => '1G',
            ],
            'pages' => [
                'enabled' => true,
                'priority' => 8,
                'timeout' => 300,
                'memory_limit' => '1G',
            ],
        ];
    }

    /**
     * Test execution order
     */
    public static function getExecutionOrder(): array
    {
        $groups = self::getGroupSettings();
        uasort($groups, fn ($a, $b) => $a['priority'] <=> $b['priority']);

        return array_keys($groups);
    }

    /**
     * Get enabled groups
     */
    public static function getEnabledGroups(): array
    {
        $groups = self::getGroupSettings();

        return array_filter($groups, fn ($group) => $group['enabled']);
    }

    /**
     * Get test timeout for group
     */
    public static function getGroupTimeout(string $group): int
    {
        $settings = self::getGroupSettings();

        return $settings[$group]['timeout'] ?? 300;
    }

    /**
     * Get memory limit for group
     */
    public static function getGroupMemoryLimit(string $group): string
    {
        $settings = self::getGroupSettings();

        return $settings[$group]['memory_limit'] ?? '1G';
    }
}
