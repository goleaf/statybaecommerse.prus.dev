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
        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_default'] = $data['is_default'] ?? false;
        $data['max_products'] = $data['max_products'] ?? 10;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['show_title'] = $data['show_title'] ?? true;
        $data['show_description'] = $data['show_description'] ?? false;
        $data['config_ids'] = $data['config_ids'] ?? [];

        return $data;
    }
}
