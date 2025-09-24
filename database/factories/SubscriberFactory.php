<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscriber>
 */
final class SubscriberFactory extends Factory
{
    protected $model = Subscriber::class;

    public function definition(): array
    {
        $firstName = fake('lt_LT')->firstName();
        $lastName = fake('lt_LT')->lastName();
        $email = strtolower($firstName.'.'.$lastName.'@'.fake()->domainName());

        return [
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => fake()->optional(0.7)->phoneNumber(),
            'company' => fake()->optional(0.6)->company(),
            'job_title' => fake()->optional(0.5)->jobTitle(),
            'interests' => fake()->randomElements([
                'products', 'news', 'promotions', 'events',
                'blog', 'technical', 'business', 'support',
            ], fake()->numberBetween(1, 4)),
            'source' => fake()->randomElement([
                'website', 'admin', 'import', 'api', 'social', 'referral', 'event', 'other',
            ]),
            'status' => fake()->randomElement(['active', 'inactive', 'unsubscribed']),
            'subscribed_at' => fake()->dateTimeBetween('-2 years', 'now'),
            'unsubscribed_at' => null,
            'last_email_sent_at' => fake()->optional(0.8)->dateTimeBetween('-6 months', 'now'),
            'email_count' => fake()->numberBetween(0, 25),
            'metadata' => [
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'referrer' => fake()->optional(0.3)->url(),
                'utm_source' => fake()->optional(0.4)->randomElement([
                    'google', 'facebook', 'linkedin', 'direct',
                ]),
                'utm_campaign' => fake()->optional(0.3)->randomElement([
                    'summer_sale', 'newsletter_signup', 'product_launch',
                ]),
            ],
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'unsubscribed_at' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
            'unsubscribed_at' => null,
        ]);
    }

    public function unsubscribed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'unsubscribed',
            'unsubscribed_at' => fake()->dateTimeBetween($attributes['subscribed_at'], 'now'),
        ]);
    }

    public function withUser(): static
    {
        return $this->state(function (array $attributes) {
            $user = User::factory()->create([
                'email' => $attributes['email'],
                'name' => $attributes['first_name'].' '.$attributes['last_name'],
            ]);

            return [
                'user_id' => $user->id,
            ];
        });
    }

    public function recent(int $days = 30): static
    {
        return $this->state(fn (array $attributes) => [
            'subscribed_at' => fake()->dateTimeBetween("-{$days} days", 'now'),
        ]);
    }

    public function withCompany(string $company): static
    {
        return $this->state(fn (array $attributes) => [
            'company' => $company,
        ]);
    }

    public function withInterests(array $interests): static
    {
        return $this->state(fn (array $attributes) => [
            'interests' => $interests,
        ]);
    }

    public function fromSource(string $source): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => $source,
        ]);
    }
}
