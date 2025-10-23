<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Channel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Channel>
 */
class ChannelFactory extends Factory
{
    protected $model = Channel::class;

    /**
     * @var array<string, true>
     */
    private static array $generatedCodes = [];

    public function definition(): array
    {
        $name = $this->faker->unique()->company() . ' Channel';
        $baseCode = Str::of($name)
            ->snake()
            ->replaceMatches('/[^a-z0-9_]/', '')
            ->replaceMatches('/_{2,}/', '_')
            ->trim('_')
            ->upper()
            ->limit(12, '')
            ->value();

        // Guarantee the code respects the alpha_dash rule even when company names contain punctuation.
        $baseCode = $baseCode !== '' ? $baseCode : Str::upper(Str::random(8));

        $code = $this->generateUniqueCode($baseCode);

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

    /**
     * Generate a unique channel code while avoiding collisions within the current factory run
     * and the persisted database records when the table exists.
     */
    private function generateUniqueCode(string $baseCode): string
    {
        $table = (new Channel())->getTable();
        $tableExists = Schema::hasTable($table);

        // Ensure we always have a valid uppercase base to work with.
        $normalisedBase = trim($baseCode, '_');
        $normalisedBase = $normalisedBase !== '' ? $normalisedBase : Str::upper(Str::random(8));

        $attempts = 0;

        while ($attempts < 100) {
            $attempts++;

            $candidate = Str::limit(
                $normalisedBase . '_' . Str::upper(Str::random(6)),
                20,
                ''
            );

            if ($candidate === '') {
                continue;
            }

            if (isset(self::$generatedCodes[$candidate])) {
                continue;
            }

            if ($tableExists && Channel::where('code', $candidate)->exists()) {
                continue;
            }

            self::$generatedCodes[$candidate] = true;

            return $candidate;
        }

        // Fallback to a full random code if we exhausted attempts (extremely unlikely).
        $fallback = Str::upper(Str::random(20));
        self::$generatedCodes[$fallback] = true;

        return $fallback;
    }
}
