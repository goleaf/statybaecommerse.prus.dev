<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CategoryShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_children_are_paginated_when_exceeding_default_limit(): void
    {
        // Create a root category that will host more children than the default page size.
        $category = Category::factory()->create();

        // Seed more than ten children so the controller has to paginate the relationship.
        Category::factory()->count(15)->create([
            'parent_id' => $category->getKey(),
        ]);

        // Attach a few published products to ensure the product listing executes successfully.
        Product::factory()
            ->count(3)
            ->published()
            ->hasAttached($category, [], 'categories')
            ->create();

        $response = $this->getJson(route('api.categories.show', $category));

        $response->assertOk();
        $response->assertJsonCount(10, 'data.children');
        $response->assertJsonPath('data.children_pagination.total', 15);
        $response->assertJsonPath('meta.children.per_page', 10);
    }

    public function test_hidden_category_results_in_not_found_response(): void
    {
        // Create a category flagged as hidden to ensure the route binding fails gracefully.
        $category = Category::factory()->create([
            'is_visible' => false,
        ]);

        $this->getJson(route('api.categories.show', $category))->assertNotFound();
    }

    public function test_invalid_sort_parameter_triggers_validation_error(): void
    {
        // Prepare a visible category that can be requested while using an invalid sort option.
        $category = Category::factory()->create();

        $this->getJson(route('api.categories.show', [$category, 'sort' => 'invalid']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['sort']);
    }
}
