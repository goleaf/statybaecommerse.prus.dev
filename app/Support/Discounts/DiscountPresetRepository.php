<?php

declare(strict_types=1);

namespace App\Support\Discounts;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;

/**
 * DiscountPresetRepository
 *
 * Repository responsible for storing and retrieving discount presets from the
 * filesystem. Using the filesystem keeps the implementation lightweight while
 * still allowing administrators to manage presets without touching the codebase.
 */
final class DiscountPresetRepository
{
    /**
     * The relative path where the JSON data will be stored.
     */
    private const STORAGE_PATH = 'discount-presets.json';

    /**
     * Return every available preset, falling back to defaults if needed.
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        // Attempt to read any previously stored presets from disk.
        $presets = $this->readFromStorage();

        if ($presets === null) {
            // If no data exists we merge in default presets defined in config.
            return $this->seedDefaults();
        }

        return $presets;
    }

    /**
     * Persist a new preset to the storage layer.
     *
     * @param array<string, mixed> $attributes
     *
     * @return array<string, mixed>
     */
    public function create(array $attributes): array
    {
        // Retrieve any presets currently saved, falling back to defaults.
        $existing = $this->all();

        // Generate a fresh identifier that can be used later for edits/removal.
        $preset = [
            'id'          => (string) Str::uuid(),
            'name'        => (string) ($attributes['name'] ?? ''),
            'description' => (string) ($attributes['description'] ?? ''),
            'type'        => (string) ($attributes['type'] ?? 'percentage'),
            'value'       => (float) ($attributes['value'] ?? 0),
            'conditions'  => Arr::wrap($attributes['conditions'] ?? []),
            'created_at'  => now()->toIso8601String(),
        ];

        // Merge the new preset with existing entries and ensure persistence.
        $payload = [...$existing, $preset];
        $this->writeToStorage($payload);

        return $preset;
    }

    /**
     * Remove storage file to allow refreshing with defaults.
     */
    public function reset(): void
    {
        // Clearing the file lets the system fall back to configuration defaults.
        Storage::disk('local')->delete(self::STORAGE_PATH);
    }

    /**
     * Attempt to read stored presets from the filesystem.
     *
     * @return array<int, array<string, mixed>>|null
     */
    private function readFromStorage(): ?array
    {
        try {
            $contents = Storage::disk('local')->get(self::STORAGE_PATH);
        } catch (FileNotFoundException) {
            // No stored presets yet, signal caller to use defaults.
            return null;
        }

        if ($contents === '' || $contents === null) {
            // Treat empty files as missing data.
            return null;
        }

        try {
            /** @var array<int, array<string, mixed>> $data */
            $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            // Log the decoding error and fall back to defaults.
            Log::warning('Unable to decode discount preset storage.', [
                'exception' => $exception,
            ]);

            return null;
        }

        return $data;
    }

    /**
     * Persist the provided presets to the filesystem.
     *
     * @param array<int, array<string, mixed>> $presets
     */
    private function writeToStorage(array $presets): void
    {
        // Encode the data with JSON pretty print for readability.
        $encoded = json_encode($presets, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        // Store the JSON representation to the local disk atomically.
        Storage::disk('local')->put(self::STORAGE_PATH, $encoded);
    }

    /**
     * Seed the storage with default presets defined in configuration.
     *
     * @return array<int, array<string, mixed>>
     */
    private function seedDefaults(): array
    {
        $defaults = config('discount_presets.defaults', []);

        // Ensure every default has a unique identifier for consistency.
        $withIds = collect($defaults)
            ->map(function (array $preset): array {
                return [
                    'id'          => $preset['id'] ?? (string) Str::uuid(),
                    'name'        => $preset['name'] ?? 'Untitled Preset',
                    'description' => $preset['description'] ?? '',
                    'type'        => $preset['type'] ?? 'percentage',
                    'value'       => (float) ($preset['value'] ?? 0),
                    'conditions'  => Arr::wrap($preset['conditions'] ?? []),
                    'created_at'  => $preset['created_at'] ?? now()->toIso8601String(),
                ];
            })
            ->values()
            ->all();

        // Persist defaults so subsequent reads do not hit configuration again.
        $this->writeToStorage($withIds);

        return $withIds;
    }
}
