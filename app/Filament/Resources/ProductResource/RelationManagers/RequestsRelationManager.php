<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\ProductRequest;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class RequestsRelationManager extends RelationManager
{
    protected static string $relationship = 'requests';

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->withoutGlobalScopes();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->label(__('messages.user'))
                ->relationship('user', 'name')
                ->searchable()
                ->preload(),
            TextInput::make('name')
                ->label(__('messages.name'))
                ->required()
                ->maxLength(255),
            TextInput::make('email')
                ->label(__('messages.email'))
                ->email()
                ->required()
                ->maxLength(255),
            TextInput::make('phone')
                ->label(__('messages.phone'))
                ->maxLength(50),
            TextInput::make('requested_quantity')
                ->label(__('admin.products.requested_quantity'))
                ->numeric()
                ->integer()
                ->default(1),
            Select::make('status')
                ->label(__('admin.products.status'))
                ->options([
                    ProductRequest::STATUS_PENDING => __('admin.products.status_pending'),
                    ProductRequest::STATUS_IN_PROGRESS => __('admin.products.status_in_progress'),
                    ProductRequest::STATUS_COMPLETED => __('admin.products.status_completed'),
                    ProductRequest::STATUS_CANCELLED => __('admin.products.status_cancelled'),
                ])
                ->default(ProductRequest::STATUS_PENDING)
                ->required(),
            Textarea::make('message')
                ->label(__('messages.message'))
                ->rows(3)
                ->columnSpanFull(),
            Textarea::make('admin_notes')
                ->label(__('admin.products.admin_notes'))
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('messages.email'))
                    ->searchable(),
                TextColumn::make('status')
                    ->label(__('admin.products.status'))
                    ->sortable(),
                TextColumn::make('requested_quantity')
                    ->label(__('admin.products.requested_quantity'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('responded_at')
                    ->label(__('admin.products.responded_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
