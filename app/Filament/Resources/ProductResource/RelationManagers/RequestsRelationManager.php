<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\ProductRequest;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RequestsRelationManager extends RelationManager
{
    protected static string $relationship = 'requests';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('messages.requests');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('user_id')
                    ->label(__('messages.user'))
                    ->relationship('user', 'name')
                    ->searchable(),
                TextInput::make('name')
                    ->label(__('messages.name'))
                    ->maxLength(255),
                TextInput::make('email')
                    ->label(__('messages.email'))
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label(__('messages.phone'))
                    ->tel()
                    ->maxLength(255),
                Textarea::make('message')
                    ->label(__('messages.message'))
                    ->columnSpanFull(),
                TextInput::make('requested_quantity')
                    ->label(__('messages.requested_quantity'))
                    ->numeric(),
                Select::make('status')
                    ->label(__('messages.status'))
                    ->options([
                        ProductRequest::STATUS_PENDING     => __('translations.status_pending'),
                        ProductRequest::STATUS_IN_PROGRESS => __('translations.status_in_progress'),
                        ProductRequest::STATUS_COMPLETED   => __('translations.status_completed'),
                        ProductRequest::STATUS_CANCELLED   => __('translations.status_cancelled'),
                    ])
                    ->required(),
                Textarea::make('admin_notes')
                    ->label(__('messages.admin_notes'))
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('messages.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->sortable()
                    ->label(__('messages.name'))
                    ->searchable(),
                TextColumn::make('email')
                    ->sortable()
                    ->label(__('messages.email'))
                    ->searchable(),
                TextColumn::make('status')
                    ->sortable()
                    ->label(__('messages.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        ProductRequest::STATUS_PENDING     => 'warning',
                        ProductRequest::STATUS_IN_PROGRESS => 'info',
                        ProductRequest::STATUS_COMPLETED   => 'success',
                        ProductRequest::STATUS_CANCELLED   => 'danger',
                        default                            => 'secondary',
                    }),
                TextColumn::make('created_at')
                    ->label(__('messages.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
