<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationBlockResource\Pages;

use App\Filament\Resources\RecommendationBlockResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateRecommendationBlock extends CreateRecord
{
    protected static string $resource = RecommendationBlockResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values
        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_featured'] = $data['is_featured'] ?? false;
        $data['max_items'] = $data['max_items'] ?? 10;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
