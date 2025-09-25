<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\NotificationTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\NotificationTemplate>
 */
class NotificationTemplateFactory extends Factory
{
    protected $model = NotificationTemplate::class;

    public function definition(): array
    {
        $name = fake()->unique()->sentence(2);

        return [
            'name' => $name,
            'slug' => Str::slug($name.'-'.Str::random(6)),
            'type' => fake()->randomElement(['email', 'sms', 'push', 'in_app']),
            'event' => fake()->randomElement(['user_registered', 'order_created', 'password_reset']),
            'subject' => 'Subject: '.$name,
            'content' => 'Content for '.$name,
            'variables' => 'name,email',
            'is_active' => true,
        ];
    }
}
