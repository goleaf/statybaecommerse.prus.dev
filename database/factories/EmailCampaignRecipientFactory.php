<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\EmailCampaignRecipient>
 */
final class EmailCampaignRecipientFactory extends Factory
{
    protected $model = EmailCampaignRecipient::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement([
            'pending',
            'scheduled',
            'sent',
            'delivered',
            'opened',
            'clicked',
            'bounced',
            'failed',
        ]);

        $scheduledAt = $this->faker->optional(0.7)->dateTimeBetween('-2 days', '+2 days');
        $sentAt = in_array($status, ['sent', 'delivered', 'opened', 'clicked', 'bounced', 'failed'], true)
            ? $this->faker->dateTimeBetween('-2 days', 'now')
            : null;
        $deliveredAt = in_array($status, ['delivered', 'opened', 'clicked'], true)
            ? $this->faker->dateTimeBetween('-36 hours', 'now')
            : null;
        $openedAt = in_array($status, ['opened', 'clicked'], true)
            ? $this->faker->dateTimeBetween('-24 hours', 'now')
            : null;
        $clickedAt = $status === 'clicked'
            ? $this->faker->dateTimeBetween('-12 hours', 'now')
            : null;
        $bouncedAt = $status === 'bounced'
            ? $this->faker->dateTimeBetween('-12 hours', 'now')
            : null;
        $unsubscribedAt = $this->faker->optional(0.1)->dateTimeBetween('-1 day', 'now');

        $openCount = $openedAt ? $this->faker->numberBetween(1, 5) : 0;
        $clickCount = $clickedAt ? $this->faker->numberBetween(1, 3) : 0;
        $deliveryAttempts = $sentAt ? $this->faker->numberBetween(1, 3) : 0;

        return [
            'email_campaign_id' => EmailCampaign::factory(),
            'email'             => $this->faker->unique()->safeEmail(),
            'name'              => $this->faker->optional()->name(),
            'status'            => $status,
            'metadata'          => [
                'locale'   => $this->faker->randomElement(['lt_LT', 'en_GB']),
                'segments' => $this->faker->randomElements([
                    'vip-customers',
                    'newsletter-subscribers',
                    'recent-buyers',
                    'inactive-users',
                ], $this->faker->numberBetween(1, 3)),
                'utm_source' => $this->faker->randomElement(['newsletter', 'promotion', 'holiday-campaign']),
            ],
            'meta'            => [],
            'scheduled_at'    => $scheduledAt,
            'sent_at'         => $sentAt,
            'delivered_at'    => $deliveredAt,
            'opened_at'       => $openedAt,
            'clicked_at'      => $clickedAt,
            'bounced_at'      => $bouncedAt,
            'unsubscribed_at' => $unsubscribedAt,
            'bounce_reason'   => $bouncedAt ? $this->faker->randomElement([
                'mailbox-full',
                'user-unknown',
                'domain-error',
            ]) : null,
            'error_message' => $status === 'failed'
                ? $this->faker->randomElement([
                    'SMTP rejected the message',
                    'Connection timeout',
                    'Template rendering error',
                ])
                : null,
            'open_count'        => $openCount,
            'click_count'       => $clickCount,
            'delivery_attempts' => $deliveryAttempts,
            'is_delivered'      => $deliveredAt !== null,
            'is_opened'         => $openedAt !== null,
            'is_clicked'        => $clickedAt !== null,
            'is_bounced'        => $bouncedAt !== null,
            'is_unsubscribed'   => $unsubscribedAt !== null,
        ];
    }
}
