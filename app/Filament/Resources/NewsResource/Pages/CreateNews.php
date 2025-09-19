<?php declare(strict_types=1);

namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\NewsResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateNews extends CreateRecord
{
    protected static string $resource = NewsResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values
        $data['is_published'] = $data['is_published'] ?? false;
        $data['is_featured'] = $data['is_featured'] ?? false;
        $data['is_breaking'] = $data['is_breaking'] ?? false;
        $data['published_at'] = $data['published_at'] ?? ($data['is_published'] ? now() : null);

        return $data;
    }
}
