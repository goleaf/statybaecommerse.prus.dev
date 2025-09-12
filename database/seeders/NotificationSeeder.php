<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

final class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        // Get all users or create a test user
        $users = User::all();
        
        if ($users->isEmpty()) {
            $users = collect([User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ])]);
        }

        // Create notifications for each user
        foreach ($users as $user) {
            // Create 20-50 random notifications per user
            $notificationCount = fake()->numberBetween(20, 50);
            
            for ($i = 0; $i < $notificationCount; $i++) {
                Notification::factory()
                    ->for($user, 'notifiable')
                    ->create();
            }

            // Create some urgent notifications
            Notification::factory()
                ->for($user, 'notifiable')
                ->urgent()
                ->count(5)
                ->create();

            // Create some read notifications
            Notification::factory()
                ->for($user, 'notifiable')
                ->read()
                ->count(10)
                ->create();

            // Create some unread notifications
            Notification::factory()
                ->for($user, 'notifiable')
                ->unread()
                ->count(15)
                ->create();

            // Create type-specific notifications
            $types = ['order', 'product', 'user', 'system', 'payment', 'shipping', 'review', 'promotion', 'newsletter', 'support'];
            
            foreach ($types as $type) {
                Notification::factory()
                    ->for($user, 'notifiable')
                    ->ofType($type)
                    ->count(2)
                    ->create();
            }
        }

        $this->command->info('Created ' . Notification::count() . ' notifications for ' . $users->count() . ' users.');
    }
}
