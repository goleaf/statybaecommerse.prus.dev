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
    ensureBrochureSeederTables();
    Storage::fake(SecureStorage::disk());
});

it('registers brochure seeder in the standard seeder profile', function (): void {
    $standardSeeders = config('seeds.standard_seeders', []);

    expect($standardSeeders)->toContain(BrochureSeeder::class);
});

it('seeds brochure records and valid lorem pdf files', function (): void {
    $this->seed(BrochureSeeder::class);

    $brochureCount = max(1, (int) config('seeds.brochures.count', 12));
    $filesPerBrochure = max(1, (int) config('seeds.brochures.files_per_brochure', 4));
    $inactiveBrochureCount = max(0, min($brochureCount, (int) config('seeds.brochures.inactive_brochure_count', 3)));
    $inactiveFilesPerBrochure = max(0, min($filesPerBrochure, (int) config('seeds.brochures.inactive_files_per_brochure', 1)));

    $expectedBrochureTotal = $brochureCount;
    $expectedFileTotal = $brochureCount * $filesPerBrochure;
    $expectedActiveBrochureTotal = $brochureCount - $inactiveBrochureCount;
    $expectedActiveFileTotal = $brochureCount * ($filesPerBrochure - $inactiveFilesPerBrochure);

    expect(Brochure::query()->count())->toBe($expectedBrochureTotal);
    expect(BrochureFile::query()->count())->toBe($expectedFileTotal);
    expect(Brochure::query()->where('is_active', true)->count())->toBe($expectedActiveBrochureTotal);
    expect(BrochureFile::query()->where('is_active', true)->count())->toBe($expectedActiveFileTotal);

    $firstFile = BrochureFile::query()
        ->orderBy('brochure_id')
        ->orderBy('sort_order')
        ->firstOrFail();

    Storage::disk(SecureStorage::disk())->assertExists((string) $firstFile->file_path);
    $binary = (string) Storage::disk(SecureStorage::disk())->get((string) $firstFile->file_path);

    expect($binary)->toStartWith('%PDF-');
    expect($binary)->toContain('Lorem ipsum');
});

it('is idempotent when run multiple times', function (): void {
    $this->seed(BrochureSeeder::class);
    $this->seed(BrochureSeeder::class);

    $brochureCount = max(1, (int) config('seeds.brochures.count', 12));
    $filesPerBrochure = max(1, (int) config('seeds.brochures.files_per_brochure', 4));

    expect(Brochure::query()->count())->toBe($brochureCount);
    expect(BrochureFile::query()->count())->toBe($brochureCount * $filesPerBrochure);
});

it('honors brochure count and file count config overrides', function (): void {
    config()->set('seeds.brochures.count', 3);
    config()->set('seeds.brochures.files_per_brochure', 2);
    config()->set('seeds.brochures.inactive_brochure_count', 1);
    config()->set('seeds.brochures.inactive_files_per_brochure', 1);

    $this->seed(BrochureSeeder::class);

    expect(Brochure::query()->count())->toBe(3);
    expect(BrochureFile::query()->count())->toBe(6);
    expect(Brochure::query()->where('is_active', true)->count())->toBe(2);
    expect(BrochureFile::query()->where('is_active', true)->count())->toBe(3);
});

function ensureBrochureSeederTables(): void
{
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
}
