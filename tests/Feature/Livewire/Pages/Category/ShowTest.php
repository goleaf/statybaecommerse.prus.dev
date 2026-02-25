<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Pages\Category;

use App\Livewire\Pages\Category\Show;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

final class ShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_with_rating_sort_without_missing_column_errors(): void
    {
        $category = Category::factory()->create([
            'is_visible' => true,
        ]);

        $productA = Product::factory()->published()->create([
            'is_enabled'   => true,
            'status'       => 'published',
            'published_at' => now()->subDay(),
        ]);
        $productB = Product::factory()->published()->create([
            'is_enabled'   => true,
            'status'       => 'published',
            'published_at' => now()->subDay(),
        ]);

        $category->products()->attach([$productA->getKey(), $productB->getKey()]);

        if (
            Schema::hasTable('reviews')
            && Schema::hasColumn('reviews', 'product_id')
            && Schema::hasColumn('reviews', 'rating')
        ) {
            DB::table('reviews')->insert([
                [
                    'product_id'           => $productA->getKey(),
                    'user_id'              => null,
                    'reviewer_name'        => 'Demo Reviewer A',
                    'reviewer_email'       => 'reviewer-a@example.com',
                    'rating'               => 5,
                    'title'                => 'Great',
                    'content'              => 'Great product',
                    'is_approved'          => true,
                    'locale'               => 'lt',
                    'is_verified_purchase' => false,
                    'helpful_count'        => 0,
                    'reported_count'       => 0,
                    'is_featured'          => false,
                    'metadata'             => null,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ],
                [
                    'product_id'           => $productB->getKey(),
                    'user_id'              => null,
                    'reviewer_name'        => 'Demo Reviewer B',
                    'reviewer_email'       => 'reviewer-b@example.com',
                    'rating'               => 2,
                    'title'                => 'Okay',
                    'content'              => 'Average product',
                    'is_approved'          => true,
                    'locale'               => 'lt',
                    'is_verified_purchase' => false,
                    'helpful_count'        => 0,
                    'reported_count'       => 0,
                    'is_featured'          => false,
                    'metadata'             => null,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ],
            ]);
        }

        Livewire::test(Show::class, ['category' => $category])
            ->set('sortBy', 'rating')
            ->set('sortDirection', 'desc')
            ->assertStatus(200);
    }
}
