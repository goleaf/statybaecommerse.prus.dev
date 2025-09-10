<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!isset($data['parent_id']) && request()->filled('parent_id')) {
            $data['parent_id'] = (int) request()->get('parent_id');
        }

        if (!array_key_exists('sort_order', $data) || $data['sort_order'] === null) {
            $data['sort_order'] = 0;
        }
        if (!array_key_exists('is_enabled', $data) || $data['is_enabled'] === null) {
            $data['is_enabled'] = true;
        }
        if (!array_key_exists('is_visible', $data) || $data['is_visible'] === null) {
            $data['is_visible'] = true;
        }

        return $data;
    }

    public function mount(?int $parent_id = null): void
    {
        parent::mount();

        if ($parent_id) {
            $this->form->fill([
                'parent_id' => $parent_id,
            ]);
        }
    }
}
