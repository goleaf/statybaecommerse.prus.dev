<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Organizations\OrganizationResource;
use App\Models\Organization;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class OrganizationsRelationManager extends RelationManager
{
    protected static string $relationship = 'organizations'; // Need to define in model.

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.navigation.organizations');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (Organization $record): string => OrganizationResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('pivot.role')
                    ->label(__('messages.role'))
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('view')
                    ->url(fn (Organization $record): string => OrganizationResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
