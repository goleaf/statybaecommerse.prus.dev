<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

abstract class BaseSeeder extends Seeder
{
    protected function seedFastModeEnabled(): bool
    {
        return (bool) config('seeds.fast_mode', false);
    }

    protected function seedShouldGenerateMedia(): bool
    {
        if (! $this->seedFastModeEnabled()) {
            return true;
        }

        return (bool) config('seeds.fast.generate_media', false);
    }

    protected function seedFastInt(string $key, int $default, int $minimum = 1): int
    {
        return max($minimum, (int) config('seeds.fast.' . $key, $default));
    }

    /**
     * @param  array<int, string> $fallback
     * @return array<int, string>
     */
    protected function seedFastLocales(array $fallback = ['lt', 'en']): array
    {
        $locales = collect(config('seeds.fast.locales', $fallback))
            ->map(static fn (mixed $locale): string => strtolower(trim((string) $locale)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($locales !== []) {
            return $locales;
        }

        return $fallback;
    }
}
