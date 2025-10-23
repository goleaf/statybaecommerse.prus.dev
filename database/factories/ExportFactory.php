<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ExportStatus;
use App\Models\Export;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Export>
 */
final class ExportFactory extends Factory
{
    protected $model = Export::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => 'Test Export',
            'format' => 'csv',
            'status' => ExportStatus::Queued,
            'exportable_type' => 'TestExportable',
            'columns' => ['id', 'name'],
            'exportable_options' => ['ids' => [1, 2, 3]],
            'artifact_disk' => config('export.disk', 'public'),
            'artifact_path' => null,
            'artifact_filename' => null,
            'requested_at' => now(),
            'requested_by' => User::factory(),
        ];
    }
}
