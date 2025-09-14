<?php declare(strict_types=1);

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class ActivityLogRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';
    protected static ?string $title = 'admin.sections.activity_log';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('log_name')
                    ->required(),
                    ->maxLength(255),
                Forms\Components\TextInput::make('description')
                    ->required(),
                    ->maxLength(255),
                Forms\Components\Textarea::make('properties')
                    ->json(),
            ]);
    }

    public function table(Table $table): Table
    {
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('log_name')
                    ->label(__('admin.fields.log_name'))
                    ->badge(),
                    ->color('info'),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('admin.fields.description'))
                    ->searchable(),
                    ->sortable(),
                Tables\Columns\TextColumn::make('event')
                    ->label(__('admin.fields.event'))
                    ->badge(),
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.fields.created_at'))
                    ->dateTime(),
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('log_name')
                    ->options([
                        'user' => 'User',
                        'order' => 'Order',
                        'product' => 'Product',
                    ]),
                Tables\Filters\SelectFilter::make('event')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort("created_at", "desc");
    }
}
    }
}
