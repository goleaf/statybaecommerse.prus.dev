<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Currency;

final class CurrencySeeder extends \Database\Seeders\BaseSeeder
{
    public function run(): void
    {
        $factories = [
            Currency::factory()->eur()->default()->active()->state(['sort_order' => 0]),
            Currency::factory()->usd()->active()->enabled()->state(['sort_order' => 1]),
            Currency::factory()->gbp()->active()->enabled()->state(['sort_order' => 2]),
            Currency::factory()->sek()->active()->enabled()->state(['sort_order' => 3]),
        ];

        foreach ($factories as $factory) {
            $attributes = $factory->raw();

            Currency::query()->updateOrCreate(
                ['code' => $attributes['code']],
                $attributes,
            );
        }
    }
}
