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
        static $sequence = 0;

        $name = $this->faker->unique()->company() . ' Channel';

        // Build a deterministic slug that appends a compact counter and random suffix to avoid collisions.
        $slug = Str::of($name)
            ->slug('-')
            ->append('-' . $sequence)
            ->append('-' . Str::lower(Str::random(4)))
            ->value();

        // Generate a short, uppercase base for the channel code before appending a randomised suffix.
        $base = Str::of($name)
            ->snake()
            ->upper()
            ->replaceMatches('/[^A-Z0-9_]/', '')
            ->substr(0, 8)
            ->trim('_');

        if ($base->isEmpty()) {
            // Guarantee a sensible default base when the generated company name is too short.
            $base = Str::of('CHANNEL');
        }

        $code = $base
            ->append('_')
            ->append(Str::upper(Str::random(4)))
            ->append('_' . $sequence++)
            ->replaceMatches('/_{2,}/', '_')
            ->trim('_')
            ->substr(0, 16)
            ->value();

        return [
            // Identity and descriptive metadata.
            'name'        => $name,
            'slug'        => $slug,
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
