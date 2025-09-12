<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\ReferralCodeStatistics;
use App\Models\ReferralCode;
use Illuminate\Database\Eloquent\Factories\Factory;

final class ReferralCodeStatisticsFactory extends Factory
{
    protected $model = ReferralCodeStatistics::class;

    public function definition(): array
    {
        $views = $this->faker->numberBetween(100, 10000);
        $clicks = $this->faker->numberBetween(10, $views);
        $signups = $this->faker->numberBetween(1, $clicks);
        $conversions = $this->faker->numberBetween(0, $signups);

        return [
            'referral_code_id' => ReferralCode::factory(),
            'date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'total_views' => $views,
            'total_clicks' => $clicks,
            'total_signups' => $signups,
            'total_conversions' => $conversions,
            'total_revenue' => $this->faker->randomFloat(2, 0, $conversions * 100),
            'metadata' => $this->faker->optional(0.2)->randomElements([
                'avg_session_duration' => $this->faker->numberBetween(30, 600),
                'bounce_rate' => $this->faker->randomFloat(2, 0, 1),
                'top_countries' => $this->faker->randomElements(['LT', 'LV', 'EE', 'PL', 'DE'], 3),
                'top_devices' => $this->faker->randomElements(['desktop', 'mobile', 'tablet'], 2),
            ]),
        ];
    }

    public function withReferralCode(ReferralCode $referralCode): static
    {
        return $this->state(fn (array $attributes) => [
            'referral_code_id' => $referralCode->id,
        ]);
    }

    public function forDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
        ]);
    }

    public function withHighConversion(): static
    {
        return $this->state(function (array $attributes) {
            $views = $this->faker->numberBetween(1000, 5000);
            $clicks = $this->faker->numberBetween(500, $views);
            $signups = $this->faker->numberBetween(200, $clicks);
            $conversions = $this->faker->numberBetween(100, $signups);

            return [
                'total_views' => $views,
                'total_clicks' => $clicks,
                'total_signups' => $signups,
                'total_conversions' => $conversions,
                'total_revenue' => $this->faker->randomFloat(2, $conversions * 50, $conversions * 200),
            ];
        });
    }

    public function withLowConversion(): static
    {
        return $this->state(function (array $attributes) {
            $views = $this->faker->numberBetween(1000, 5000);
            $clicks = $this->faker->numberBetween(50, 200);
            $signups = $this->faker->numberBetween(10, $clicks);
            $conversions = $this->faker->numberBetween(1, $signups);

            return [
                'total_views' => $views,
                'total_clicks' => $clicks,
                'total_signups' => $signups,
                'total_conversions' => $conversions,
                'total_revenue' => $this->faker->randomFloat(2, 0, $conversions * 50),
            ];
        });
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
        ]);
    }
}
