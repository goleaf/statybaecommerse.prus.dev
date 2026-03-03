<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brochure;
use App\Models\BrochureFile;
use App\Support\Pdf\LoremPdfGenerator;
use App\Support\Storage\SecureStorage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

final class BrochureSeeder extends BaseSeeder
{
    public function run(): void
    {
        if (! Schema::hasTable('brochures') || ! Schema::hasTable('brochure_files')) {
            $this->command?->warn('BrochureSeeder: brochures tables are missing, skipping.');

            return;
        }

        $brochureCount = max(1, (int) config('seeds.brochures.count', 12));
        $filesPerBrochure = max(1, (int) config('seeds.brochures.files_per_brochure', 4));
        $inactiveBrochures = max(0, min($brochureCount, (int) config('seeds.brochures.inactive_brochure_count', 3)));
        $inactiveFilesPerBrochure = max(0, min($filesPerBrochure, (int) config('seeds.brochures.inactive_files_per_brochure', 1)));

        $activeBrochureThreshold = $brochureCount - $inactiveBrochures;
        $activeFileThreshold = $filesPerBrochure - $inactiveFilesPerBrochure;
        $disk = SecureStorage::disk();

        for ($brochureIndex = 1; $brochureIndex <= $brochureCount; $brochureIndex++) {
            $title = sprintf('Seeded Brochure %02d', $brochureIndex);
            $brochure = Brochure::query()->updateOrCreate(
                ['title' => $title],
                [
                    'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                    'is_active'   => $brochureIndex <= $activeBrochureThreshold,
                    'sort_order'  => $brochureIndex,
                ]
            );

            for ($fileIndex = 1; $fileIndex <= $filesPerBrochure; $fileIndex++) {
                $fileName = sprintf('Seeded File %02d', $fileIndex);
                $path = sprintf(
                    'brochures/seeded/brochure-%02d/file-%02d.pdf',
                    $brochureIndex,
                    $fileIndex
                );

                $binary = LoremPdfGenerator::brochureBinary(
                    $title,
                    $fileName,
                    sprintf(
                        'Lorem ipsum brochure seed file for brochure %02d file %02d. Ut enim ad minim veniam quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
                        $brochureIndex,
                        $fileIndex
                    )
                );

                Storage::disk($disk)->put($path, $binary);

                BrochureFile::query()->updateOrCreate(
                    [
                        'brochure_id' => $brochure->getKey(),
                        'file_path'   => $path,
                    ],
                    [
                        'name'       => $fileName,
                        'is_active'  => $fileIndex <= $activeFileThreshold,
                        'sort_order' => $fileIndex,
                    ]
                );
            }
        }

        $this->command?->info(sprintf(
            'BrochureSeeder: seeded %d brochures with %d PDFs each.',
            $brochureCount,
            $filesPerBrochure
        ));
    }
}
