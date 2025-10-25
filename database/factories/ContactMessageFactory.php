<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessage>
 */
final class ContactMessageFactory extends Factory
{
    protected $model = ContactMessage::class;

    public function definition(): array
    {
        // Build a deterministic payload so localisation tests remain predictable across environments.
        return [
            'name'         => $this->faker->name(),
            'email'        => $this->faker->safeEmail(),
            'subject'      => $this->faker->sentence(),
            'phone'        => $this->faker->optional()->phoneNumber(),
            'order_number' => $this->faker->optional()->bothify('ORD-####'),
            'message'      => $this->faker->paragraph(),
            'ip_address'   => $this->faker->ipv4(),
            'user_agent'   => 'Mozilla/5.0',
        ];
    }
}
