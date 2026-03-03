<?php

declare(strict_types=1);

use App\Filament\Resources\Brochures\BrochureResource;
use App\Filament\Resources\Brochures\Pages\CreateBrochure;
use App\Filament\Resources\Brochures\Pages\EditBrochure;
use App\Filament\Resources\Brochures\Pages\ListBrochures;
use App\Models\AdminUser;
use App\Models\Brochure;
use App\Models\BrochureFile;
use App\Support\Storage\SecureStorage;
use Database\Seeders\BrochureSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Support\PdfFixture;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = AdminUser::factory()->create();
    $this->actingAs($this->admin, 'admin');

    if (! Schema::hasTable('brochures')) {
        Schema::create('brochures', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('brochure_files')) {
        Schema::create('brochure_files', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('brochure_id')->constrained('brochures')->cascadeOnDelete();
            $table->string('name');
            $table->string('file_path');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }
});

it('can render brochure resource index page', function (): void {
    $response = $this->get(BrochureResource::getUrl('index'));

    $response->assertOk();
});

it('can list brochures in table', function (): void {
    $brochures = Brochure::factory()->count(3)->create();

    Livewire::test(ListBrochures::class)
        ->assertCanSeeTableRecords($brochures);
});

it('can list seeded brochures in admin table', function (): void {
    Storage::fake(SecureStorage::disk());
    $this->seed(BrochureSeeder::class);

    $seededBrochures = Brochure::query()
        ->orderBy('sort_order')
        ->limit(3)
        ->get();

    Livewire::test(ListBrochures::class)
        ->assertCanSeeTableRecords($seededBrochures);
});

it('filters brochures table by active status', function (): void {
    $active = Brochure::factory()->create([
        'title'     => 'Active brochure',
        'is_active' => true,
    ]);

    $inactive = Brochure::factory()->create([
        'title'     => 'Inactive brochure',
        'is_active' => false,
    ]);

    Livewire::test(ListBrochures::class)
        ->filterTable('is_active', true)
        ->assertCanSeeTableRecords([$active])
        ->assertCanNotSeeTableRecords([$inactive]);
});

it('filters brochures table by active files availability', function (): void {
    $withActiveFiles = Brochure::factory()->create([
        'title'     => 'Brochure with active files',
        'is_active' => true,
    ]);

    $withoutActiveFiles = Brochure::factory()->create([
        'title'     => 'Brochure without active files',
        'is_active' => true,
    ]);

    BrochureFile::factory()->create([
        'brochure_id' => $withActiveFiles->id,
        'name'        => 'Active file',
        'is_active'   => true,
    ]);

    BrochureFile::factory()->create([
        'brochure_id' => $withoutActiveFiles->id,
        'name'        => 'Inactive file',
        'is_active'   => false,
    ]);

    Livewire::test(ListBrochures::class)
        ->filterTable('has_active_files', 'yes')
        ->assertCanSeeTableRecords([$withActiveFiles])
        ->assertCanNotSeeTableRecords([$withoutActiveFiles]);

    Livewire::test(ListBrochures::class)
        ->filterTable('has_active_files', 'no')
        ->assertCanSeeTableRecords([$withoutActiveFiles])
        ->assertCanNotSeeTableRecords([$withActiveFiles]);
});

it('blocks creating active brochure without active files', function (): void {
    Livewire::test(CreateBrochure::class)
        ->fillForm([
            'title'     => 'Missing Files',
            'is_active' => true,
            'files'     => [],
        ])
        ->call('create')
        ->assertHasFormErrors(['files']);

    $this->assertDatabaseCount('brochures', 0);
});

it('can create inactive brochure without files', function (): void {
    Livewire::test(CreateBrochure::class)
        ->fillForm([
            'title'     => 'Draft brochure',
            'is_active' => false,
            'files'     => [],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('brochures', [
        'title'     => 'Draft brochure',
        'is_active' => false,
    ]);
});

it('can edit brochure fields', function (): void {
    $disk = config('media-security.disk', 'secure-media');
    Storage::fake($disk);

    $brochure = Brochure::factory()->create([
        'title' => 'Original brochure',
    ]);

    Storage::disk($disk)->put('brochures/current-file.pdf', PdfFixture::binary('Brochure edit test'));

    BrochureFile::factory()->create([
        'brochure_id' => $brochure->id,
        'name'        => 'Current file',
        'file_path'   => 'brochures/current-file.pdf',
        'is_active'   => true,
    ]);

    Livewire::test(EditBrochure::class, ['record' => $brochure->getRouteKey()])
        ->fillForm([
            'title'     => 'Updated brochure',
            'is_active' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('brochures', [
        'id'        => $brochure->id,
        'title'     => 'Updated brochure',
        'is_active' => true,
    ]);
});

it('deletes brochure and uploaded files', function (): void {
    $disk = config('media-security.disk', 'secure-media');
    Storage::fake($disk);

    $brochure = Brochure::factory()->create();
    $filePath = 'brochures/sample.pdf';

    Storage::disk($disk)->put($filePath, PdfFixture::binary('Brochure delete test'));

    BrochureFile::factory()->create([
        'brochure_id' => $brochure->id,
        'file_path'   => $filePath,
    ]);

    Livewire::test(ListBrochures::class)
        ->callTableAction('delete', $brochure)
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseMissing('brochures', ['id' => $brochure->id]);
    $this->assertDatabaseMissing('brochure_files', ['brochure_id' => $brochure->id]);
    Storage::disk($disk)->assertMissing($filePath);
});
