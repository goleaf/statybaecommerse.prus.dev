<?php declare(strict_types=1);

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Support\Str;

it('instantiates Collection model', function (): void {
    expect(new Collection())->toBeInstanceOf(Collection::class);
});

it('uses slug as route key', function (): void {
    $collection = Collection::factory()->create(['slug' => 'summer-tools']);
    expect($collection->getRouteKeyName())
        ->toBe('slug')
        ->and($collection->getRouteKey())
        ->toBe('summer-tools');
});

it('applies scopes: visible, manual, automatic, ordered', function (): void {
    $c1 = Collection::factory()->create(['is_visible' => true, 'is_automatic' => false, 'sort_order' => 2]);
    $c2 = Collection::factory()->create(['is_visible' => false, 'is_automatic' => true, 'sort_order' => 1]);
    $c3 = Collection::factory()->create(['is_visible' => true, 'is_automatic' => true, 'sort_order' => 3]);

    $visible = Collection::query()->visible()->pluck('id')->all();
    expect($visible)->toContain($c1->id, $c3->id)->not()->toContain($c2->id);

    $manual = Collection::query()->manual()->pluck('id')->all();
    expect($manual)->toContain($c1->id)->not()->toContain($c2->id, $c3->id);

    $automatic = Collection::query()->automatic()->pluck('id')->all();
    expect($automatic)->toContain($c2->id, $c3->id)->not()->toContain($c1->id);

    $ordered = Collection::query()->ordered()->pluck('sort_order')->all();
    expect($ordered)->toBe([1, 2, 3]);
});

it('helpers isManual and isAutomatic reflect flag', function (): void {
    $manual = Collection::factory()->create(['is_automatic' => false]);
    $auto = Collection::factory()->create(['is_automatic' => true]);

    expect($manual->isManual())
        ->toBeTrue()
        ->and($manual->isAutomatic())
        ->toBeFalse()
        ->and($auto->isManual())
        ->toBeFalse()
        ->and($auto->isAutomatic())
        ->toBeTrue();
});

it('products relationship attaches and products_count counts only published', function (): void {
    $collection = Collection::factory()->create();

    // Published product
    $p1 = Product::factory()->create([
        'is_visible' => true,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    // Draft product (not counted)
    $p2 = Product::factory()->create([
        'is_visible' => true,
        'status' => 'draft',
        'published_at' => null,
    ]);

    $collection->products()->attach([$p1->id, $p2->id]);

    expect($collection->products()->count())
        ->toBe(2)
        ->and($collection->products_count)
        ->toBe(1);
});

it('image and banner accessors return null without media', function (): void {
    $collection = Collection::factory()->create();
    expect($collection->image)
        ->toBeNull()
        ->and($collection->getImageUrl())
        ->toBeNull()
        ->and($collection->getImageUrl('md'))
        ->toBe('')
        ->and($collection->getBannerUrl())
        ->toBeNull()
        ->and($collection->getBannerUrl('md'))
        ->toBeNull();
});

it('flushCaches executes without exception for configured locales', function (): void {
    config()->set('app.supported_locales', 'lt,en');
    Collection::flushCaches();
    expect(true)->toBeTrue();
});

it('trans returns translated field for locale or falls back to base', function (): void {
    app()->setLocale('lt');
    config()->set('app.locale', 'lt');

    $collection = Collection::factory()->create([
        'name' => 'Base Name',
        'slug' => 'base-slug',
        'description' => 'Base Desc',
    ]);

    $collection->translations()->create([
        'locale' => 'lt',
        'name' => 'LT Pavadinimas',
        'slug' => 'lt-slugas',
        'description' => 'LT aprašymas',
    ]);

    expect($collection->fresh()->trans('name'))->toBe('LT Pavadinimas');

    app()->setLocale('en');
    config()->set('app.locale', 'en');
    expect($collection->fresh()->trans('name'))->toBe('Base Name');
});
