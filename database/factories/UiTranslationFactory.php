<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\UiTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UiTranslation>
 */
final class UiTranslationFactory extends Factory
{
    protected $model = UiTranslation::class;

    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(3),
            'locale' => fake()->randomElement(['lt', 'en']),
            'value' => fake()->sentence(),
            'group' => fake()->randomElement(['admin', 'news', 'products', 'orders', 'general']),
            'metadata' => [
                'context' => fake()->word(),
                'description' => fake()->sentence(),
            ],
        ];
    }

    public function forKey(string $key): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => $key,
        ]);
    }

    public function forLocale(string $locale): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => $locale,
        ]);
    }

    public function forGroup(string $group): static
    {
        return $this->state(fn (array $attributes) => [
            'group' => $group,
        ]);
    }

    public function lithuanian(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'lt',
            'value' => fake()->randomElement([
                'Pavadinimas',
                'Aprašymas',
                'Kategorija',
                'Sukurta',
                'Atnaujinta',
                'Aktyvus',
                'Neaktyvus',
                'Taip',
                'Ne',
                'Išsaugoti',
                'Atšaukti',
                'Redaguoti',
                'Šalinti',
                'Peržiūrėti',
            ]),
        ]);
    }

    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'en',
            'value' => fake()->randomElement([
                'Title',
                'Description',
                'Category',
                'Created',
                'Updated',
                'Active',
                'Inactive',
                'Yes',
                'No',
                'Save',
                'Cancel',
                'Edit',
                'Delete',
                'View',
            ]),
        ]);
    }

    public function newsGroup(): static
    {
        return $this->state(fn (array $attributes) => [
            'group' => 'news',
            'key' => 'news.'.fake()->randomElement([
                'fields.title',
                'fields.content',
                'fields.author',
                'fields.published_at',
                'filters.category',
                'actions.publish',
                'actions.unpublish',
            ]),
        ]);
    }

    public function adminGroup(): static
    {
        return $this->state(fn (array $attributes) => [
            'group' => 'admin',
            'key' => 'admin.'.fake()->randomElement([
                'common.save',
                'common.cancel',
                'common.edit',
                'common.delete',
                'common.view',
                'navigation.dashboard',
                'navigation.products',
                'navigation.orders',
            ]),
        ]);
    }
}
