<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EmailCampaign;
use App\Models\NotificationTemplate;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\EmailCampaign>
 */
final class EmailCampaignFactory extends Factory
{
    protected $model = EmailCampaign::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->sentence(3);
        $status = $this->faker->randomElement(['draft', 'scheduled', 'sending', 'sent', 'paused', 'cancelled']);

        $totalRecipients = $this->faker->numberBetween(0, 5000);
        $sentCount = $this->faker->numberBetween(0, $totalRecipients);
        $deliveredCount = $this->faker->numberBetween(0, $sentCount);
        $openedCount = $this->faker->numberBetween(0, $deliveredCount);
        $clickedCount = $this->faker->numberBetween(0, $openedCount);

        return [
            'name'            => $name,
            'description'     => $this->faker->paragraph(),
            'subject'         => $this->faker->sentence(6),
            'content'         => $this->faker->paragraphs(3, true),
            'html_content'    => sprintf('<p>%s</p>', implode('</p><p>', $this->faker->paragraphs(2))),
            'from_email'      => $this->faker->unique()->safeEmail(),
            'from_name'       => $this->faker->name(),
            'reply_to'        => $this->faker->safeEmail(),
            'is_active'       => $status !== 'cancelled' ? $this->faker->boolean(70) : false,
            'status'          => $status,
            'target_audience' => [
                'segments' => $this->faker->randomElements([
                    'all_customers',
                    'new_customers',
                    'vip_customers',
                    'inactive_customers',
                    'subscribers',
                ], $this->faker->numberBetween(1, 3)),
                'filters' => [
                    'last_purchase_days' => $this->faker->numberBetween(7, 90),
                    'min_orders'         => $this->faker->numberBetween(0, 5),
                ],
            ],
            'total_recipients'   => $totalRecipients,
            'sent_count'         => $sentCount,
            'delivered_count'    => $deliveredCount,
            'opened_count'       => $openedCount,
            'clicked_count'      => $clickedCount,
            'unsubscribed_count' => $this->faker->numberBetween(0, $sentCount),
            'scheduled_at'       => fn (array $attributes) => $this->scheduledAt($attributes['status']),
            'sent_at'            => fn (array $attributes) => $this->sentAt($attributes['status']),
            'completed_at'       => fn (array $attributes) => $this->completedAt($attributes['status']),
            'settings'           => [
                'track_opens'            => true,
                'track_clicks'           => $this->faker->boolean(),
                'send_time_optimization' => $this->faker->boolean(),
                'utm_campaign'           => Str::slug($name),
            ],
            'metadata' => [
                'source'         => $this->faker->randomElement(['manual', 'automation', 'import']),
                'notes'          => $this->faker->sentence(),
                'last_synced_at' => $this->faker->optional()->dateTimeBetween('-1 week', 'now'),
            ],
            'template_id' => null,
            'created_by'  => null,
        ];
    }

    public function withCreator(?User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user?->id ?? User::factory(),
        ]);
    }

    public function withTemplate(?NotificationTemplate $template = null): static
    {
        return $this->state(fn (array $attributes) => [
            'template_id' => $template?->id ?? NotificationTemplate::factory(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => 'draft',
            'scheduled_at' => null,
            'sent_at'      => null,
            'completed_at' => null,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => 'scheduled',
            'scheduled_at' => $this->faker->dateTimeBetween('+1 hour', '+3 days'),
            'sent_at'      => null,
            'completed_at' => null,
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => 'sent',
            'scheduled_at' => $this->faker->dateTimeBetween('-2 days', '-1 hour'),
            'sent_at'      => $this->faker->dateTimeBetween('-1 hour', 'now'),
            'completed_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
        ]);
    }

    private function scheduledAt(string $status): ?DateTimeInterface
    {
        return match ($status) {
            'scheduled', 'sending' => $this->faker->dateTimeBetween('+1 hour', '+3 days'),
            'sent', 'paused', 'cancelled' => $this->faker->dateTimeBetween('-3 days', 'now'),
            default => null,
        };
    }

    private function sentAt(string $status): ?DateTimeInterface
    {
        return match ($status) {
            'sending', 'sent' => $this->faker->dateTimeBetween('-2 days', 'now'),
            default => null,
        };
    }

    private function completedAt(string $status): ?DateTimeInterface
    {
        return match ($status) {
            'sent', 'cancelled' => $this->faker->dateTimeBetween('-1 day', 'now'),
            default => null,
        };
    }
}
