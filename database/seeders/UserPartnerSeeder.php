<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\User;

final class UserPartnerSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have partners to assign. If not, create some.
        if (Partner::count() === 0) {
            $this->command->info('No partners found. Creating 3 default partners...');
            Partner::factory()->count(3)->create();
        }

        // Get all partner IDs to pick from
        $partnerIds = Partner::pluck('id');

        // Process users in chunks to handle large datasets efficiently
        User::chunk(100, function ($users) use ($partnerIds) {
            foreach ($users as $user) {
                // Pick one random partner ID
                $randomPartnerId = $partnerIds->random();

                // Assign exactly one partner to the user.
                // sync() ensures any previous associations are removed.
                $user->partners()->sync([$randomPartnerId]);
            }
        });

        $this->command->info('All users have been assigned to exactly one partner group.');
    }
}
