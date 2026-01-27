<?php

declare(strict_types=1);

namespace App\Support\Filament;

use App\Support\Filament\Components\SearchableInput;

/**
 * Lightweight registry that associates SearchableInput component instances with their
 * payload metadata for test-time assertions. The storage uses spl_object_id to avoid
 * relying on dynamic properties (which are deprecated in PHP 8.2+) while still giving
 * helpers deterministic read/write access.
 */
final class SearchableInputState
{
    /**
     * @var array<int, array<array-key, mixed>>
     */
    private static array $payload = [];

    /**
     * @var array<int, array<array-key, mixed>>
     */
    private static array $fallback = [];

    private function __construct()
    {
        // Static utility.
    }

    /**
     * Persist the canonical payload captured during component hydration.
     */
    public static function setPayload(SearchableInput $component, array $payload): void
    {
        self::$payload[spl_object_id($component)] = $payload;
    }

    /**
     * Persist the fallback payload used when the component is cleared.
     */
    public static function setFallback(SearchableInput $component, array $payload): void
    {
        self::$fallback[spl_object_id($component)] = $payload;
    }

    /**
     * Resolve the payload associated with the provided component instance.
     */
    public static function getPayload(SearchableInput $component): array
    {
        $id = spl_object_id($component);

        if (array_key_exists($id, self::$payload)) {
            return self::$payload[$id];
        }

        return self::$fallback[$id] ?? [];
    }

    /**
     * Clear cached payload metadata when the component resets during tests.
     */
    public static function forget(SearchableInput $component): void
    {
        $id = spl_object_id($component);
        unset(self::$payload[$id], self::$fallback[$id]);
    }
}
