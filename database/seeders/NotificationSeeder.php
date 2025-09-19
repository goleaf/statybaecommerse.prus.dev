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

            // Create type-specific notifications with realistic data
            $this->createOrderNotifications($user);
            $this->createProductNotifications($user);
            $this->createUserNotifications($user);
            $this->createSystemNotifications($user);
            $this->createPaymentNotifications($user);
            $this->createShippingNotifications($user);
            $this->createReviewNotifications($user);
            $this->createPromotionNotifications($user);
            $this->createNewsletterNotifications($user);
            $this->createSupportNotifications($user);
        }

        $this->command->info('Created ' . Notification::count() . ' notifications for ' . $users->count() . ' users.');
    }

    private function createOrderNotifications(User $user): void
    {
        $orderNotifications = [
            ['title' => 'Naujas užsakymas', 'message' => 'Jūsų užsakymas #12345 buvo sėkmingai sukurtas', 'urgent' => false],
            ['title' => 'Užsakymas atnaujintas', 'message' => 'Užsakymas #12345 būsena pakeista į "Apdorojama"', 'urgent' => false],
            ['title' => 'Užsakymas išsiųstas', 'message' => 'Jūsų užsakymas #12345 buvo išsiųstas', 'urgent' => true],
            ['title' => 'Užsakymas pristatytas', 'message' => 'Jūsų užsakymas #12345 buvo pristatytas', 'urgent' => false],
        ];

        foreach ($orderNotifications as $notification) {
            Notification::create([
                'id' => fake()->uuid(),
                'type' => 'order',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => $notification,
                'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
            ]);
        }
    }

    private function createProductNotifications(User $user): void
    {
        $productNotifications = [
            ['title' => 'Naujas produktas', 'message' => 'Pridėtas naujas produktas "Profesionalus grąžtuvas"', 'urgent' => false],
            ['title' => 'Mažos atsargos', 'message' => 'Produktas "Profesionalus grąžtuvas" turi mažai atsargų', 'urgent' => true],
            ['title' => 'Kaina pakeista', 'message' => 'Produkto "Profesionalus grąžtuvas" kaina sumažėjo', 'urgent' => false],
        ];

        foreach ($productNotifications as $notification) {
            Notification::create([
                'id' => fake()->uuid(),
                'type' => 'product',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => $notification,
                'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
            ]);
        }
    }

    private function createUserNotifications(User $user): void
    {
        $userNotifications = [
            ['title' => 'Naujas vartotojas', 'message' => 'Registruotas naujas vartotojas', 'urgent' => false],
            ['title' => 'Profilis atnaujintas', 'message' => 'Vartotojo profilis buvo atnaujintas', 'urgent' => false],
        ];

        foreach ($userNotifications as $notification) {
            Notification::create([
                'id' => fake()->uuid(),
                'type' => 'user',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => $notification,
                'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
            ]);
        }
    }

    private function createSystemNotifications(User $user): void
    {
        $systemNotifications = [
            ['title' => 'Sistemos atnaujinimas', 'message' => 'Sistema buvo sėkmingai atnaujinta', 'urgent' => false],
            ['title' => 'Saugumo įspėjimas', 'message' => 'Aptiktas neįprastas veikimas sistemoje', 'urgent' => true],
            ['title' => 'Atsarginė kopija', 'message' => 'Sukurta sėkminga atsarginė kopija', 'urgent' => false],
        ];

        foreach ($systemNotifications as $notification) {
            Notification::create([
                'id' => fake()->uuid(),
                'type' => 'system',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => $notification,
                'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
            ]);
        }
    }

    private function createPaymentNotifications(User $user): void
    {
        $paymentNotifications = [
            ['title' => 'Mokėjimas gautas', 'message' => 'Mokėjimas už užsakymą #12345 buvo sėkmingai gautas', 'urgent' => true],
            ['title' => 'Mokėjimas nepavyko', 'message' => 'Mokėjimas už užsakymą #12345 nepavyko', 'urgent' => true],
            ['title' => 'Grąžinimas', 'message' => 'Grąžinimas už užsakymą #12345 buvo apdorotas', 'urgent' => false],
        ];

        foreach ($paymentNotifications as $notification) {
            Notification::create([
                'id' => fake()->uuid(),
                'type' => 'payment',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => $notification,
                'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
            ]);
        }
    }

    private function createShippingNotifications(User $user): void
    {
        $shippingNotifications = [
            ['title' => 'Siunta išsiųsta', 'message' => 'Jūsų siunta buvo išsiųsta', 'urgent' => false],
            ['title' => 'Siunta pristatyta', 'message' => 'Jūsų siunta buvo pristatyta', 'urgent' => false],
            ['title' => 'Siuntos problemos', 'message' => 'Aptiktos problemos su siunta', 'urgent' => true],
        ];

        foreach ($shippingNotifications as $notification) {
            Notification::create([
                'id' => fake()->uuid(),
                'type' => 'shipping',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => $notification,
                'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
            ]);
        }
    }

    private function createReviewNotifications(User $user): void
    {
        $reviewNotifications = [
            ['title' => 'Naujas atsiliepimas', 'message' => 'Gautas naujas atsiliepimas apie produktą', 'urgent' => false],
            ['title' => 'Atsiliepimas patvirtintas', 'message' => 'Jūsų atsiliepimas buvo patvirtintas', 'urgent' => false],
        ];

        foreach ($reviewNotifications as $notification) {
            Notification::create([
                'id' => fake()->uuid(),
                'type' => 'review',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => $notification,
                'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
            ]);
        }
    }

    private function createPromotionNotifications(User $user): void
    {
        $promotionNotifications = [
            ['title' => 'Nauja akcija', 'message' => 'Pradėta nauja akcija - iki 50% nuolaida', 'urgent' => true],
            ['title' => 'Akcija baigiasi', 'message' => 'Akcija baigiasi per 24 valandas', 'urgent' => true],
        ];

        foreach ($promotionNotifications as $notification) {
            Notification::create([
                'id' => fake()->uuid(),
                'type' => 'promotion',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => $notification,
                'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
            ]);
        }
    }

    private function createNewsletterNotifications(User $user): void
    {
        $newsletterNotifications = [
            ['title' => 'Naujienlaiškis išsiųstas', 'message' => 'Naujienlaiškis buvo sėkmingai išsiųstas', 'urgent' => false],
            ['title' => 'Prenumerata aktyvuota', 'message' => 'Jūsų prenumerata buvo aktyvuota', 'urgent' => false],
        ];

        foreach ($newsletterNotifications as $notification) {
            Notification::create([
                'id' => fake()->uuid(),
                'type' => 'newsletter',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => $notification,
                'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
            ]);
        }
    }

    private function createSupportNotifications(User $user): void
    {
        $supportNotifications = [
            ['title' => 'Naujas palaikymo užklausimas', 'message' => 'Gautas naujas palaikymo užklausimas', 'urgent' => true],
            ['title' => 'Užklausimas išspręstas', 'message' => 'Palaikymo užklausimas buvo išspręstas', 'urgent' => false],
        ];

        foreach ($supportNotifications as $notification) {
            Notification::create([
                'id' => fake()->uuid(),
                'type' => 'support',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => $notification,
                'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
            ]);
        }
    }
}
