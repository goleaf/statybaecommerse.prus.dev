<?php

declare(strict_types=1);

namespace App\Filament\Resources\AnalyticsEventResource\Pages;

use App\Filament\Resources\AnalyticsEventResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateAnalyticsEvent extends CreateRecord
{
    protected static string $resource = AnalyticsEventResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values
        $data['user_id'] = $data['user_id'] ?? auth()->id();
        $data['session_id'] = $data['session_id'] ?? session()->getId();
        $data['ip_address'] = $data['ip_address'] ?? request()->ip();
        $data['user_agent'] = $data['user_agent'] ?? request()->userAgent();
        $data['created_at'] = $data['created_at'] ?? now();
        
        return $data;
    }
}
