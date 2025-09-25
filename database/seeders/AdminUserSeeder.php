<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user using factory for consistency (idempotent)
        if (!User::where('email', 'admin@example.com')->exists()) {
            User::factory()->admin()->create([
                'email' => 'admin@example.com',
                'name' => 'System Administrator',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        }

        // Create additional admin users (only if we have fewer than 6 total admins)
        $existingAdmins = User::where('is_admin', true)->count();
        $neededAdmins = max(0, 6 - $existingAdmins); // 1 main + 5 additional = 6 total
        
        if ($neededAdmins > 0) {
            User::factory()->count($neededAdmins)->admin()->create();
        }
    }
}
