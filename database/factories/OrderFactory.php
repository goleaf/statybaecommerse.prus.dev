<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 50, 2000);
        $taxRate = 0.21; // Lithuanian VAT rate
        $taxAmount = $subtotal * $taxRate;
        $shippingAmount = $this->faker->randomFloat(2, 0, 25);
        $discountAmount = $this->faker->boolean(20) ? $this->faker->randomFloat(2, 5, 100) : 0;
        $total = $subtotal + $taxAmount + $shippingAmount - $discountAmount;

        return [
            'number' => 'LT-' . $this->faker->unique()->numerify('######'),
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['pending', 'processing', 'shipped', 'delivered']),
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'discount_amount' => $discountAmount,
            'total' => $total,
            'currency' => 'EUR',
            'billing_address' => $this->generateLithuanianAddress(),
            'shipping_address' => $this->faker->boolean(70) ? $this->generateLithuanianAddress() : null,
            'notes' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
            'shipped_at' => $this->faker->boolean(60) ? $this->faker->dateTimeBetween('-10 days', 'now') : null,
            'delivered_at' => $this->faker->boolean(40) ? $this->faker->dateTimeBetween('-5 days', 'now') : null,
        ];
    }

    private function generateLithuanianAddress(): array
    {
        $lithuanianCities = [
            'Vilnius', 'Kaunas', 'Klaipėda', 'Šiauliai', 'Panevėžys',
            'Alytus', 'Marijampolė', 'Mažeikiai', 'Jonava', 'Utena',
            'Kėdainiai', 'Telšiai', 'Visaginas', 'Tauragė', 'Ukmergė'
        ];

        $streets = [
            'Gedimino pr.', 'Laisvės al.', 'Savanorių pr.', 'Vytauto g.',
            'Jonavos g.', 'Pramonės g.', 'Statybininkų g.', 'Taikos g.',
            'Žalgirio g.', 'Ateities g.', 'Technikos g.', 'Verslo g.'
        ];

        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'company' => $this->faker->boolean(40) ? $this->faker->company() . ' UAB' : null,
            'address_line_1' => $this->faker->randomElement($streets) . ' ' . $this->faker->numberBetween(1, 200),
            'address_line_2' => $this->faker->boolean(20) ? 'Buto ' . $this->faker->numberBetween(1, 50) : null,
            'city' => $this->faker->randomElement($lithuanianCities),
            'postal_code' => 'LT-' . $this->faker->numerify('#####'),
            'country' => 'Lithuania',
            'phone' => '+370 ' . $this->faker->numerify('### #####'),
        ];
    }
}