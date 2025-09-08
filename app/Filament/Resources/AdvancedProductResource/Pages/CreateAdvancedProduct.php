<?php declare(strict_types=1);

namespace App\Filament\Resources\AdvancedProductResource\Pages;

use App\Filament\Resources\AdvancedProductResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateAdvancedProduct extends CreateRecord
{
    protected static string $resource = AdvancedProductResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('Product created successfully');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-generate slug if not provided
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        }

        // Set published_at if status is published
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Log activity
        activity()
            ->performedOn($this->record)
            ->withProperties(['admin_created' => true])
            ->log('Product created via admin panel');
    }
}



