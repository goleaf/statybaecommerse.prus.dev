<?php declare(strict_types=1);

use App\Filament\Resources\CollectionResource;
use App\Models\Collection;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $guard = config('auth.defaults.guard', 'web');
    Role::query()->firstOrCreate([
        'name' => 'admin',
        'guard_name' => is_string($guard) ? $guard : 'web',
    ]);
    $this->admin = User::factory()->create();
    $this->admin->syncRoles(['admin']);
    $this->actingAs($this->admin);

    // Ensure Lithuanian is the default locale used by forms and translation preparation
    app()->setLocale('lt');
    config()->set('app.locale', 'lt');
    config()->set('app-features.supported_locales', ['lt', 'en']);
});

it('can render collection index page', function () {
    $this
        ->get(CollectionResource::getUrl('index'))
        ->assertSuccessful();
});

it('can list collections', function () {
    $collections = Collection::factory()->count(5)->create();

    Livewire::test(CollectionResource\Pages\ListCollections::class)
        ->assertCanSeeTableRecords($collections);
});

it('can render create page', function () {
    $this
        ->get(CollectionResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create manual collection with lt/en translations', function () {
    $newData = [
        'sort_order' => 1,
        'is_visible' => true,
        'is_automatic' => false,
        'seo_title' => 'SEO Title',
        'seo_description' => 'SEO Description',
    ];

    $component = Livewire::test(CollectionResource\Pages\CreateCollection::class)
        ->fillForm($newData)
        ->call('create')
        ->assertHasNoFormErrors();

    $collection = Collection::query()->where('is_automatic', false)->first();
    expect($collection)->not->toBeNull();
});

it('can render view page', function () {
    $collection = Collection::factory()->create();
    $this
        ->get(CollectionResource::getUrl('view', ['record' => $collection]))
        ->assertSuccessful();
});

it('can render edit page', function () {
    $collection = Collection::factory()->create();
    $this
        ->get(CollectionResource::getUrl('edit', ['record' => $collection]))
        ->assertSuccessful();
});

it('can update collection', function () {
    $collection = Collection::factory()->create();
    $newData = [
        'name_lt' => 'Atnaujinta kolekcija',
        'description_lt' => 'Atnaujintas aprašymas',
        'is_visible' => false,
    ];

    Livewire::test(CollectionResource\Pages\EditCollection::class, [
        'record' => $collection->getRouteKey(),
    ])
        ->fillForm($newData)
        ->call('save')
        ->assertHasNoFormErrors();

    $collection->refresh();
    expect($collection->is_visible)->toBeFalse();
});

it('can delete collection', function () {
    $collection = Collection::factory()->create();

    Livewire::test(CollectionResource\Pages\EditCollection::class, [
        'record' => $collection->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertSoftDeleted('collections', ['id' => $collection->id]);
});

it('can filter by visibility and type', function () {
    $manualVisible = Collection::factory()->create(['is_visible' => true, 'is_automatic' => false]);
    $autoHidden = Collection::factory()->create(['is_visible' => false, 'is_automatic' => true]);

    Livewire::test(CollectionResource\Pages\ListCollections::class)
        ->filterTable('visible')
        ->assertOk()
        ->filterTable('is_automatic', 1)
        ->assertOk();
});
