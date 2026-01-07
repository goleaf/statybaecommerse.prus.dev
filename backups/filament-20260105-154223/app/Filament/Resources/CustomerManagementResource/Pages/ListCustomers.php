<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerManagementResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\CustomerManagementResource;
use Filament\Actions;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable as SpatieTranslatableListRecords;

final class ListCustomers extends BaseListRecords
{
    use SpatieTranslatableListRecords; // Keep language-specific filters in sync with other list pages.

    protected static string $resource = CustomerManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(), // Allow quick locale toggling before executing table actions.
            Actions\CreateAction::make(),
        ];
    }
}
