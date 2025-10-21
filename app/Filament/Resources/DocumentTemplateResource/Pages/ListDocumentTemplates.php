<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentTemplateResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\DocumentTemplateResource;
use Filament\Actions;

class ListDocumentTemplates extends BaseListRecords
{
    protected static string $resource = DocumentTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
