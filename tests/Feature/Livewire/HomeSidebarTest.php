<?php declare(strict_types=1);

use App\Models\Category;

use function Pest\Laravel\get;

it('shows home sidebar categories accordion', function (): void {
    $parent = Category::factory()->create(['name' => 'Elektriniai įrankiai', 'slug' => 'elektriniai-irankiai', 'is_visible' => true]);
    Category::factory()->create(['name' => 'Gręžtuvai', 'slug' => 'greztuvai', 'is_visible' => true, 'parent_id' => $parent->id]);

    $response = get('/lt');
    $response->assertOk();
    $response->assertSee('Elektriniai įrankiai', false);
});
