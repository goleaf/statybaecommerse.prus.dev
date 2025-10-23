<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Spatie\Activitylog\ActivityLogStatus;

final class DatabaseSeeder extends Seeder
{
    private const PROFILE_MINIMAL = 'minimal';
    private const PROFILE_FULL = 'full';

    public function run(): void
    {
        $this->withLoggingSuppressed(function (): void {
            $profile = $this->determineProfile();

            $this->command?->info(sprintf('Using database seed profile: %s', $profile));

            $this->call($this->seedersForProfile($profile));
        });
    }

    /**
     * @param callable():void $callback
     */
    private function withLoggingSuppressed(callable $callback): void
    {
        /** @var ActivityLogStatus $activityLogStatus */
        $activityLogStatus = app(ActivityLogStatus::class);
        $wasLoggingDisabled = $activityLogStatus->disabled();

        if (! $wasLoggingDisabled) {
            activity()->disableLogging();
        }

        $this->call([
            CurrencySeeder::class,
            AttributeSeeder::class,
            AttributeValueSeeder::class,
            AdminAuthorizationSeeder::class,
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            DemoStoreSeeder::class,
        ]);

        if (! $wasLoggingDisabled) {
            activity()->enableLogging();
        }
    }

    private function determineProfile(): string
    {
        $configured = config('seeds.runtime_profile');

        if (! \is_string($configured) || $configured === '') {
            $configured = config('seeds.default_profile', self::PROFILE_MINIMAL);
        }

        $normalized = strtolower($configured);

        return \in_array($normalized, [self::PROFILE_MINIMAL, self::PROFILE_FULL], true)
            ? $normalized
            : self::PROFILE_MINIMAL;
    }

    /**
     * @return array<int, class-string<Seeder>>
     */
    private function seedersForProfile(string $profile): array
    {
        $profiles = config('seeds.profiles', []);
        $minimal = Arr::get($profiles, self::PROFILE_MINIMAL, []);

        if ($profile === self::PROFILE_FULL) {
            $full = array_merge($minimal, Arr::get($profiles, self::PROFILE_FULL, []));

            return $this->uniqueSeeders($full);
        }

        return $this->uniqueSeeders($minimal);
    }

    /**
     * @param array<int, class-string<Seeder>> $seeders
     * @return array<int, class-string<Seeder>>
     */
    private function uniqueSeeders(array $seeders): array
    {
        return array_values(array_unique($seeders));
    }
}
