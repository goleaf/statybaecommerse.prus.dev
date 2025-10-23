<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Channel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Channel>
 */
class ChannelFactory extends Factory
{
    protected $model = Channel::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company() . ' Channel';
        $baseCode = Str::of($name)
            ->snake()
            ->upper()
            ->replaceMatches('/[^A-Z0-9_]/', '')
            ->trim('_')
            ->substr(0, 8)
            ->value();

        // Append a random suffix so truncated company names cannot collide with the channel unique constraint.
        $suffix = Str::upper(Str::random(4));
        $code = Str::of(($baseCode !== '' ? $baseCode : 'CHANNEL') . '_' . $suffix)
            ->replaceMatches('/_{2,}/', '_')
            ->trim('_')
            ->substr(0, 12)
            ->value();

        return [
            // Identity and descriptive metadata.
            'name'        => $name,
            'slug'        => Str::slug($name),
            'code'        => $code,
            'type'        => $this->faker->randomElement(['web', 'mobile', 'api', 'pos']),
            'description' => $this->faker->boolean(50) ? $this->faker->sentence(10) : null,

            // Routing and storefront preferences.
            'timezone'          => $this->faker->timezone(),
            'url'               => $this->faker->url(),
            'domain'            => $this->faker->domainName(),
            'currency_code'     => 'EUR',
            'currency_symbol'   => '€',
            'currency_position' => 'after',

            // Operational flags surfaced in Filament filters.
            'is_enabled'        => true,
            'is_default'        => false,
            'is_active'         => true,
            'ssl_enabled'       => $this->faker->boolean(70),
            'analytics_enabled' => $this->faker->boolean(40),
            'sort_order'        => $this->faker->numberBetween(0, 50),

            // Provide deterministic defaults for nullable JSON columns.
            'metadata'      => [],
            'configuration' => [],
        ];
    }

    public function web(): static
    {
        return $this->state(fn (array $attributes) => [
            'name'        => 'B2C Web',
            'slug'        => 'b2c-web',
            'description' => 'Numatytasis viešas prekybos kanalas.',
            'timezone'    => 'Europe/Vilnius',
            'url'         => 'https://demo.statyba.test',
            'is_default'  => true,
            'is_enabled'  => true,
        ]);
    }
}
