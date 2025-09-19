<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceListResource\RelationManagers;

use App\Models\Company;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;

final class PartnersRelationManager extends RelationManager
{
    protected static string $relationship = 'partners';

    protected static ?string $title = 'Partners';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('company_id')
                    ->label(__('price_lists.partner'))
                    ->relationship('company', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(500),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('website')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ]),

                Forms\Components\TextInput::make('commission_percentage')
                    ->label(__('price_lists.commission_percentage'))
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->step(0.01)
                    ->suffix('%'),

                Forms\Components\TextInput::make('priority')
                    ->label(__('price_lists.priority'))
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(100),

                Forms\Components\DateTimePicker::make('valid_from')
                    ->label(__('price_lists.valid_from'))
                    ->default(now()),

                Forms\Components\DateTimePicker::make('valid_until')
                    ->label(__('price_lists.valid_until'))
                    ->after('valid_from'),

                Forms\Components\Toggle::make('is_active')
                    ->label(__('price_lists.is_active'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('company.name')
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->label(__('price_lists.partner'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('company.email')
                    ->label(__('price_lists.email'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('company.phone')
                    ->label(__('price_lists.phone'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('commission_percentage')
                    ->label(__('price_lists.commission_percentage'))
                    ->formatStateUsing(fn (?float $state): string => $state ? "{$state}%" : '-')
                    ->sortable()
                    ->badge()
                    ->color(fn (?float $state): string => match (true) {
                        $state >= 20 => 'danger',
                        $state >= 15 => 'warning',
                        $state >= 10 => 'info',
                        $state >= 5 => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('priority')
                    ->label(__('price_lists.priority'))
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 80 => 'danger',
                        $state >= 60 => 'warning',
                        $state >= 40 => 'info',
                        $state >= 20 => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('valid_from')
                    ->label(__('price_lists.valid_from'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('valid_until')
                    ->label(__('price_lists.valid_until'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('price_lists.is_active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('price_lists.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('company')
                    ->label(__('price_lists.partner'))
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('price_lists.is_active'))
                    ->boolean()
                    ->trueLabel(__('price_lists.active_only'))
                    ->falseLabel(__('price_lists.inactive_only'))
                    ->native(false),

                Tables\Filters\Filter::make('valid_now')
                    ->label(__('price_lists.valid_now'))
                    ->query(fn (Builder $query): Builder => $query->where(function (Builder $query) {
                        $query->where('valid_from', '<=', now())
                            ->where(function (Builder $query) {
                                $query->whereNull('valid_until')
                                    ->orWhere('valid_until', '>=', now());
                            });
                    })),

                Tables\Filters\Filter::make('expired')
                    ->label(__('price_lists.expired'))
                    ->query(fn (Builder $query): Builder => $query->where('valid_until', '<', now())),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make(),
            ])
            ->actions([
                EditAction::make(),
                Tables\Actions\DetachAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('priority', 'desc');
    }
}
