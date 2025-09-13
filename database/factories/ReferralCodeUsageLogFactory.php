<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ReferralCode;
use App\Models\ReferralCodeUsageLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class ReferralCodeUsageLogFactory extends Factory
{
    protected $model = ReferralCodeUsageLog::class;

    public function definition(): array
    {
        return [
            'referral_code_id' => ReferralCode::factory(),
            'user_id' => $this->faker->optional(0.8)->randomElement(User::pluck('id')->toArray()),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'referrer' => $this->faker->optional(0.6)->url(),
            'metadata' => $this->faker->optional(0.3)->randomElements([
                'device_type' => $this->faker->randomElement(['desktop', 'mobile', 'tablet']),
                'browser' => $this->faker->randomElement(['chrome', 'firefox', 'safari', 'edge']),
                'os' => $this->faker->randomElement(['windows', 'macos', 'linux', 'android', 'ios']),
                'country' => $this->faker->countryCode(),
                'city' => $this->faker->city(),
            ]),
        ];
    }

    public function withUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function withReferralCode(ReferralCode $referralCode): static
    {
        return $this->state(fn (array $attributes) => [
            'referral_code_id' => $referralCode->id,
        ]);
    }

    public function fromIp(string $ipAddress): static
    {
        return $this->state(fn (array $attributes) => [
            'ip_address' => $ipAddress,
        ]);
    }

    public function withUserAgent(string $userAgent): static
    {
        return $this->state(fn (array $attributes) => [
            'user_agent' => $userAgent,
        ]);
    }

    public function withReferrer(string $referrer): static
    {
        return $this->state(fn (array $attributes) => [
            'referrer' => $referrer,
        ]);
    }
}
