<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountCodeResource\Pages;

use App\Filament\Resources\DiscountCodeResource;
use App\Models\DiscountCode;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

final class EditDiscountCode extends EditRecord
{
    protected static string $resource = DiscountCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Persist updates while bypassing storefront-focused global scopes.
     *
     * @param array<string, mixed> $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if (! $record instanceof DiscountCode) {
            return $record;
        }

        $record->fill($data);

        $dirty = $record->getDirty();

        if ($dirty === []) {
            return $record;
        }

        // Delegate to the resource helper so scope-aware actions stay consistent.
        DiscountCodeResource::updateRecordWithoutScopes($record, $dirty);

        return $record;
    }
}
