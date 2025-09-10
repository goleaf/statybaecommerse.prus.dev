<?php declare(strict_types=1);

use App\Filament\Resources\SeoDataResource\Pages\CreateSeoData as CreateSeoDataPage;
use App\Filament\Resources\SeoDataResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SeoData;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->admin = User::factory()->create(['email' => 'admin@example.com']);
    Role::findOrCreate('admin');
    $this->admin->assignRole('admin');
});

it('can render SeoData index and create pages', function (): void {
    actingAs($this->admin);

    Livewire::test(\App\Filament\Resources\SeoDataResource\Pages\ListSeoData::class)
        ->assertOk();

    Livewire::test(CreateSeoDataPage::class)
        ->assertOk();
});

it('can create seo data for a product', function (): void {
    actingAs($this->admin);

    $product = Product::factory()->create();

    Livewire::test(CreateSeoDataPage::class)
        ->fillForm([
            'locale' => 'lt',
            'title' => 'Testo SEO pavadinimas',
            'description' => 'Trumpas SEO aprašymas',
            'keywords' => 'raktazodis1, raktazodis2',
            'canonical_url' => 'https://example.test/lt/products/' . $product->slug,
            'no_index' => false,
            'no_follow' => false,
            'seoable_type' => Product::class,
            'seoable_id' => $product->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(SeoData::query()->where([
        'seoable_type' => Product::class,
        'seoable_id' => $product->id,
        'locale' => 'lt',
    ])->exists())->toBeTrue();
});
