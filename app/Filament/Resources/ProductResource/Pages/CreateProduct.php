<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function afterCreate(): void
    {
        /** @var \Filament\Schemas\Schema $form */
        $form = $this->form;

        if (method_exists($form, 'saveRelationships')) {
            $form->saveRelationships();
        }
    }
}
