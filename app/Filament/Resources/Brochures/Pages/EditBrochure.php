<?php

declare(strict_types=1);

namespace App\Filament\Resources\Brochures\Pages;

use App\Filament\Resources\Brochures\BrochureResource;
use App\Models\BrochureFile;
use App\Support\Brochures\BrochureActivationGuard;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

final class EditBrochure extends EditRecord
{
    protected static string $resource = BrochureResource::class;

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['files'] = $this->normalizeFilesForActivationValidation($data['files'] ?? null);

        BrochureActivationGuard::ensureActiveBrochureHasActiveFile($data, 'data');

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading(__('admin.brochures.delete_heading'))
                ->modalDescription(__('admin.brochures.delete_warning')),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeFilesForActivationValidation(mixed $files): array
    {
        $existingFiles = $this->getRecord()
            ->files()
            ->get(['id', 'file_path', 'is_active'])
            ->keyBy('id');

        if (! is_array($files)) {
            return $this->filesForValidation($existingFiles);
        }

        return collect($files)
            ->filter(static fn (mixed $file): bool => is_array($file))
            ->map(static function (array $file) use ($existingFiles): array {
                $path = trim((string) ($file['file_path'] ?? ''));
                if ($path !== '') {
                    return $file;
                }

                $id = isset($file['id']) ? (int) $file['id'] : 0;
                if ($id < 1 || ! $existingFiles->has($id)) {
                    return $file;
                }

                $persisted = $existingFiles->get($id);
                if (! $persisted instanceof BrochureFile) {
                    return $file;
                }

                $file['file_path'] = (string) $persisted->file_path;
                $file['is_active'] ??= (bool) $persisted->is_active;

                return $file;
            })
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, BrochureFile>    $existingFiles
     * @return array<int, array<string, mixed>>
     */
    private function filesForValidation(Collection $existingFiles): array
    {
        return $existingFiles
            ->map(static fn (BrochureFile $file): array => [
                'file_path' => (string) ($file->file_path ?? ''),
                'is_active' => (bool) ($file->is_active ?? false),
            ])
            ->values()
            ->all();
    }
}
