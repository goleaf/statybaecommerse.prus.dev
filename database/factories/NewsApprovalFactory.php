<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\News;
use App\Models\NewsApproval;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<\App\Models\NewsApproval>
 */
final class NewsApprovalFactory extends Factory
{
    /**
     * {@inheritDoc}
     */
    protected $model = NewsApproval::class;

    public function definition(): array
    {
        // Use deterministic timestamps so scope tests can assert inclusive ranges.
        $decidedAt = Carbon::now()->subMinutes(fake()->numberBetween(1, 120));

        return [
            // Automatically create related records unless the caller overrides them.
            'news_id'  => News::factory(),
            'user_id'  => User::factory(),
            'decision' => fake()->randomElement([
                NewsApproval::DECISION_APPROVED,
                NewsApproval::DECISION_RETURNED,
            ]),
            'notes'      => fake()->optional()->sentence(),
            'decided_at' => $decidedAt,
        ];
    }
}
