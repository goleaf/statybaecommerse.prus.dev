<?php declare(strict_types=1);

use App\Filament\Resources\ReportResource;
use App\Models\Report;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Livewire\Livewire;

beforeEach(function (): void {
    $admin = User::factory()->create();
    // Ensure 'admin' role exists for the current guard before assigning
    $guard = config('auth.defaults.guard', 'web');
    \Spatie\Permission\Models\Role::findOrCreate('admin', $guard);
    $admin->assignRole('admin');
    actingAs($admin);
});

it('renders report index', function (): void {
    get(ReportResource::getUrl('index'))->assertSuccessful();
});

it('lists reports', function (): void {
    $records = Report::factory()->count(3)->create();
    Livewire::test(ReportResource\Pages\ListReports::class)
        ->assertCanSeeTableRecords($records);
});

it('creates report', function (): void {
    $data = Report::factory()->make()->toArray();
    Livewire::test(ReportResource\Pages\CreateReport::class)
        ->fillForm($data)
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas('reports', [
        'name' => $data['name'],
        'type' => $data['type'],
    ]);
});

it('updates report', function (): void {
    $record = Report::factory()->create();
    $data = Report::factory()->make()->toArray();
    Livewire::test(ReportResource\Pages\EditReport::class, [
        'record' => $record->getRouteKey(),
    ])
        ->fillForm($data)
        ->call('save')
        ->assertHasNoFormErrors();

    expect($record->refresh()->name)->toBe($data['name']);
});

it('deletes report', function (): void {
    $record = Report::factory()->create();
    Livewire::test(ReportResource\Pages\EditReport::class, [
        'record' => $record->getRouteKey(),
    ])->callAction(DeleteAction::class);

    assertModelMissing($record);
});
