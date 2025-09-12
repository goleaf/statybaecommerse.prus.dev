<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
final class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        $types = ['order', 'product', 'user', 'system', 'payment', 'shipping', 'review', 'promotion', 'newsletter', 'support'];
        $type = $this->faker->randomElement($types);
        
        $data = [
            'title' => $this->getTitleForType($type),
            'message' => $this->getMessageForType($type),
            'type' => $type,
            'urgent' => $this->faker->boolean(20), // 20% chance of being urgent
            'color' => $this->getColorForType($type),
            'tags' => $this->faker->randomElements(['important', 'update', 'alert', 'info', 'warning'], $this->faker->numberBetween(0, 3)),
        ];

        // Add type-specific data
        $data = array_merge($data, $this->getTypeSpecificData($type));

        return [
            'type' => 'App\\Notifications\\' . ucfirst($type) . 'Notification',
            'notifiable_type' => User::class,
            'notifiable_id' => User::factory(),
            'data' => $data,
            'read_at' => $this->faker->optional(0.3)->dateTimeBetween('-30 days', 'now'), // 30% chance of being read
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'updated_at' => function (array $attributes) {
                return $attributes['created_at'];
            },
        ];
    }

    private function getTitleForType(string $type): string
    {
        return match ($type) {
            'order' => $this->faker->randomElement([
                'Naujas užsakymas',
                'Užsakymas atnaujintas',
                'Užsakymas išsiųstas',
                'Užsakymas pristatytas',
            ]),
            'product' => $this->faker->randomElement([
                'Naujas produktas',
                'Produktas atnaujintas',
                'Mažos atsargos',
                'Atsargos baigėsi',
            ]),
            'user' => $this->faker->randomElement([
                'Naujas vartotojas',
                'Profilis atnaujintas',
                'Slaptažodis pakeistas',
                'El. paštas patvirtintas',
            ]),
            'system' => $this->faker->randomElement([
                'Techninis aptarnavimas',
                'Saugumo įspėjimas',
                'Sistemos atnaujinimas',
                'Veikimo problema',
            ]),
            'payment' => $this->faker->randomElement([
                'Mokėjimas gautas',
                'Mokėjimas nepavyko',
                'Grąžinimas apdorotas',
                'Mokėjimo problema',
            ]),
            'shipping' => $this->faker->randomElement([
                'Pristatymas pradėtas',
                'Pristatymas užbaigtas',
                'Pristatymo problema',
                'Pristatymo atnaujinimas',
            ]),
            'review' => $this->faker->randomElement([
                'Naujas atsiliepimas',
                'Atsiliepimas patvirtintas',
                'Atsiliepimas ištrintas',
                'Atsiliepimo atnaujinimas',
            ]),
            'promotion' => $this->faker->randomElement([
                'Nauja akcija',
                'Akcija baigėsi',
                'Specialus pasiūlymas',
                'Nuolaidos atnaujinimas',
            ]),
            'newsletter' => $this->faker->randomElement([
                'Naujienlaiškis išsiųstas',
                'Naujienlaiškio prenumerata',
                'Naujienlaiškio atnaujinimas',
                'Naujienlaiškio problema',
            ]),
            'support' => $this->faker->randomElement([
                'Naujas palaikymo užklausimas',
                'Palaikymo atsakymas',
                'Palaikymo problema išspręsta',
                'Palaikymo atnaujinimas',
            ]),
            default => 'Pranešimas',
        };
    }

    private function getMessageForType(string $type): string
    {
        return match ($type) {
            'order' => $this->faker->randomElement([
                'Jūsų užsakymas buvo sėkmingai sukurtas ir apdorojamas.',
                'Užsakymo būsena buvo atnaujinta.',
                'Jūsų užsakymas buvo išsiųstas ir netrukus bus pristatytas.',
                'Užsakymas sėkmingai pristatytas.',
            ]),
            'product' => $this->faker->randomElement([
                'Naujas produktas buvo pridėtas į katalogą.',
                'Produkto informacija buvo atnaujinta.',
                'Šis produktas turi mažai atsargų.',
                'Produkto atsargos baigėsi.',
            ]),
            'user' => $this->faker->randomElement([
                'Sveiki! Jūsų paskyra buvo sėkmingai sukurta.',
                'Jūsų profilio informacija buvo atnaujinta.',
                'Jūsų slaptažodis buvo sėkmingai pakeistas.',
                'Jūsų el. pašto adresas buvo patvirtintas.',
            ]),
            'system' => $this->faker->randomElement([
                'Sistema pradėjo techninį aptarnavimą.',
                'Aptiktas saugumo įspėjimas, prašome patikrinti savo paskyrą.',
                'Galimas sistemos atnaujinimas.',
                'Aptikta veikimo problema, darbuotojai ją sprendžia.',
            ]),
            'payment' => $this->faker->randomElement([
                'Jūsų mokėjimas buvo sėkmingai apdorotas.',
                'Mokėjimo apdorojimas nepavyko, prašome bandyti dar kartą.',
                'Jūsų grąžinimas buvo apdorotas.',
                'Aptikta mokėjimo problema.',
            ]),
            'shipping' => $this->faker->randomElement([
                'Jūsų užsakymas buvo išsiųstas.',
                'Užsakymas sėkmingai pristatytas.',
                'Aptikta pristatymo problema.',
                'Pristatymo informacija buvo atnaujinta.',
            ]),
            'review' => $this->faker->randomElement([
                'Jūsų atsiliepimas buvo pridėtas.',
                'Atsiliepimas buvo patvirtintas ir rodomas.',
                'Atsiliepimas buvo ištrintas dėl netinkamo turinio.',
                'Atsiliepimo informacija buvo atnaujinta.',
            ]),
            'promotion' => $this->faker->randomElement([
                'Nauja akcija pradėta! Pasinaudokite specialiais pasiūlymais.',
                'Akcija baigėsi, bet vis dar galite rasti gerų pasiūlymų.',
                'Specialus pasiūlymas tik jums.',
                'Nuolaidų sąrašas buvo atnaujintas.',
            ]),
            'newsletter' => $this->faker->randomElement([
                'Naujienlaiškis buvo išsiųstas į jūsų el. paštą.',
                'Jūsų prenumerata buvo aktyvuota.',
                'Naujienlaiškio turinys buvo atnaujintas.',
                'Aptikta problema su naujienlaiškio siuntimu.',
            ]),
            'support' => $this->faker->randomElement([
                'Jūsų palaikymo užklausimas buvo gautas.',
                'Palaikymo komanda atsakė į jūsų užklausimą.',
                'Jūsų problema buvo išspręsta.',
                'Palaikymo informacija buvo atnaujinta.',
            ]),
            default => 'Pranešimo turinys.',
        };
    }

    private function getColorForType(string $type): string
    {
        return match ($type) {
            'order' => '#3B82F6', // blue
            'product' => '#10B981', // green
            'user' => '#8B5CF6', // purple
            'system' => '#F59E0B', // orange
            'payment' => '#EAB308', // yellow
            'shipping' => '#6366F1', // indigo
            'review' => '#EC4899', // pink
            'promotion' => '#EF4444', // red
            'newsletter' => '#06B6D4', // cyan
            'support' => '#6B7280', // gray
            default => '#6B7280',
        };
    }

    private function getTypeSpecificData(string $type): array
    {
        return match ($type) {
            'order' => [
                'order_id' => $this->faker->numberBetween(1, 1000),
                'order_number' => 'ORD-' . $this->faker->unique()->numberBetween(1000, 9999),
                'order_total' => $this->faker->randomFloat(2, 10, 500),
            ],
            'product' => [
                'product_id' => $this->faker->numberBetween(1, 100),
                'product_name' => $this->faker->words(2, true),
                'product_sku' => 'SKU-' . $this->faker->unique()->numberBetween(1000, 9999),
            ],
            'user' => [
                'user_id' => $this->faker->numberBetween(1, 100),
                'user_name' => $this->faker->name(),
                'user_email' => $this->faker->email(),
            ],
            'system' => [
                'system_version' => $this->faker->semver(),
                'maintenance_duration' => $this->faker->numberBetween(30, 120) . ' min',
            ],
            'payment' => [
                'payment_id' => $this->faker->numberBetween(1, 1000),
                'payment_amount' => $this->faker->randomFloat(2, 10, 500),
                'payment_method' => $this->faker->randomElement(['card', 'bank_transfer', 'paypal']),
            ],
            'shipping' => [
                'shipping_id' => $this->faker->numberBetween(1, 1000),
                'tracking_number' => $this->faker->bothify('TRK-####-####'),
                'shipping_method' => $this->faker->randomElement(['standard', 'express', 'overnight']),
            ],
            'review' => [
                'review_id' => $this->faker->numberBetween(1, 1000),
                'review_rating' => $this->faker->numberBetween(1, 5),
                'reviewer_name' => $this->faker->name(),
            ],
            'promotion' => [
                'promotion_id' => $this->faker->numberBetween(1, 100),
                'discount_percentage' => $this->faker->numberBetween(5, 50),
                'promotion_code' => $this->faker->bothify('PROMO-####'),
            ],
            'newsletter' => [
                'newsletter_id' => $this->faker->numberBetween(1, 100),
                'newsletter_subject' => $this->faker->sentence(4),
                'subscriber_count' => $this->faker->numberBetween(100, 10000),
            ],
            'support' => [
                'ticket_id' => $this->faker->numberBetween(1, 1000),
                'ticket_priority' => $this->faker->randomElement(['low', 'medium', 'high', 'urgent']),
                'support_agent' => $this->faker->name(),
            ],
            default => [],
        };
    }

    /**
     * Create an urgent notification
     */
    public function urgent(): static
    {
        return $this->state(function (array $attributes) {
            $attributes['data']['urgent'] = true;
            $attributes['data']['color'] = '#EF4444'; // red
            return $attributes;
        });
    }

    /**
     * Create a read notification
     */
    public function read(): static
    {
        return $this->state(function (array $attributes) {
            $attributes['read_at'] = $this->faker->dateTimeBetween('-30 days', 'now');
            return $attributes;
        });
    }

    /**
     * Create an unread notification
     */
    public function unread(): static
    {
        return $this->state(function (array $attributes) {
            $attributes['read_at'] = null;
            return $attributes;
        });
    }

    /**
     * Create a notification for a specific type
     */
    public function ofType(string $type): static
    {
        return $this->state(function (array $attributes) use ($type) {
            $attributes['type'] = 'App\\Notifications\\' . ucfirst($type) . 'Notification';
            $attributes['data']['type'] = $type;
            $attributes['data']['title'] = $this->getTitleForType($type);
            $attributes['data']['message'] = $this->getMessageForType($type);
            $attributes['data']['color'] = $this->getColorForType($type);
            $attributes['data'] = array_merge($attributes['data'], $this->getTypeSpecificData($type));
            return $attributes;
        });
    }
}