<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Support\Html\HtmlSanitizer;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

final class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('products.messages.created_successfully'))
            ->body(__('products.messages.created_successfully_description'));
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        /** @var HtmlSanitizer $sanitizer */
        $sanitizer = app(HtmlSanitizer::class);

        if (array_key_exists('description', $data)) {
            $data['description'] = $sanitizer->sanitize($data['description']);
        }

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Set published date if not provided and product is visible
        if (! isset($data['published_at']) && ($data['is_visible'] ?? false)) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
