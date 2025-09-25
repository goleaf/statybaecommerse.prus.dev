<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Seeder;

final class ActivityLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (ActivityLog::query()->exists()) {
            return;
        }

        $users = User::query()->limit(5)->get();

        if ($users->count() < 5) {
            $users = $users->concat(
                User::factory()->count(5 - $users->count())->create()
            );
        }

        $users->each(function (User $user): void {
            if ($user->activityLogs()->exists()) {
                return;
            }

            ActivityLog::factory()
                ->count(10)
                ->for($user, 'causer')
                ->create();
        });
    }
}
