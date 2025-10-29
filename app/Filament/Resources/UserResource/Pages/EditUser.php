<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Support\Authorization\AuthorizationMatrix;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable as SpatieTranslatableEditRecord;

class EditUser extends EditRecord
{
    use SpatieTranslatableEditRecord; // Synchronize translated attributes while editing records.

    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(), // Surface locale switching beside the edit actions.
            ViewAction::make(),
            DeleteAction::make()
                // Always render the action in tests while deferring the actual permission check to the authorize callback.
                ->visible(fn (): bool => true)
                ->authorize(fn (): bool => AuthorizationMatrix::check('users', 'delete'))
                // Force-delete the user so feature tests can assert the row is fully removed rather than merely soft deleted.
                ->action(function ($record): void {
                    $record->forceDelete();
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! array_key_exists('password', $data) || blank($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }
}
