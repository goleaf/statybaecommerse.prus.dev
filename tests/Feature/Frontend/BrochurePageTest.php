<?php

declare(strict_types=1);

use App\Models\Brochure;
use App\Models\BrochureFile;
use App\Support\Storage\SecureStorage;
use Database\Seeders\BrochureSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
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

it('redirects /brochures to localized brochures page', function (): void {
    $response = $this->get('/brochures');

    $response->assertRedirect(route('localized.brochures.index', ['locale' => app()->getLocale()]));
});

it('shows only active brochure files on localized brochures page', function (): void {
    $activeBrochure = Brochure::factory()->create([
        'title'      => 'Active brochure',
        'is_active'  => true,
        'sort_order' => 1,
    ]);

    $hiddenBrochure = Brochure::factory()->create([
        'title'      => 'Hidden brochure',
        'is_active'  => false,
        'sort_order' => 2,
    ]);

    $visibleFile = BrochureFile::factory()->create([
        'brochure_id' => $activeBrochure->id,
        'name'        => 'Visible file',
        'file_path'   => 'brochures/visible.pdf',
        'is_active'   => true,
        'sort_order'  => 1,
    ]);

    BrochureFile::factory()->create([
        'brochure_id' => $activeBrochure->id,
        'name'        => 'Hidden inactive file',
        'file_path'   => 'brochures/inactive.pdf',
        'is_active'   => false,
    ]);

    BrochureFile::factory()->create([
        'brochure_id' => $hiddenBrochure->id,
        'name'        => 'Hidden brochure file',
        'file_path'   => 'brochures/hidden.pdf',
        'is_active'   => true,
    ]);

    $response = $this->get(route('localized.brochures.index', ['locale' => 'lt']));

    $response->assertOk();
    $response->assertSee('Active brochure');
    $response->assertSee('Visible file');
    $response->assertSee($visibleFile->downloadUrl());
    $response->assertDontSee('Hidden brochure');
    $response->assertDontSee('Hidden inactive file');
    $response->assertDontSee('Hidden brochure file');
});

it('shows seeded brochure files on localized brochures page', function (): void {
    Storage::fake(SecureStorage::disk());
    $this->seed(BrochureSeeder::class);

    $response = $this->get(route('localized.brochures.index', ['locale' => 'lt']));

    $response->assertOk();
    $response->assertSee('Seeded Brochure 01');
    $response->assertSee('Seeded File 01');
});
