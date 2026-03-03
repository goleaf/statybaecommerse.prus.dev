<?php

declare(strict_types=1);

namespace App\Filament\Resources\Brochures\Pages;

use App\Filament\Resources\Brochures\BrochureResource;
use App\Support\Brochures\BrochureActivationGuard;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

final class CreateBrochure extends CreateRecord
{
    protected static string $resource = BrochureResource::class;

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        BrochureActivationGuard::ensureActiveBrochureHasActiveFile($data, 'data');

        return $data;
    }
}
