<?php declare(strict_types=1);

namespace App\Filament\Resources\CartItemResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

final class UserRelationManager extends RelationManager
{
    protected static string $relationship = 'user';

    protected static ?string $title = 'admin.cart_items.relations.user';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('admin.users.fields.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label(__('admin.users.fields.email'))
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label(__('admin.users.fields.phone'))
                    ->tel()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.users.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('admin.users.fields.email'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage(__('admin.common.copied')),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('admin.users.fields.phone'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label(__('admin.users.fields.email_verified'))
                    ->boolean()
                    ->getStateUsing(fn($record) => !is_null($record->email_verified_at)),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('admin.users.fields.is_active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.users.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label(__('admin.users.filters.email_verified')),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('admin.users.filters.active_only')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
