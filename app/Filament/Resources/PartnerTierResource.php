<?php

declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerTierResource\Pages;
use App\Models\PartnerTier;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

final class PartnerTierResource extends Resource
{
    protected static ?string $model = PartnerTier::class;

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-star';
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Marketing';
    }

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make(__('admin.partner_tiers.sections.basic_information'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('admin.partner_tiers.name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->label(__('admin.partner_tiers.code'))
                            ->maxLength(255)
                            ->unique(PartnerTier::class, 'code', ignoreRecord: true),
                        Forms\Components\Toggle::make('is_enabled')
                            ->label(__('admin.partner_tiers.is_enabled'))
                            ->default(true),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('admin.partner_tiers.sections.financial_settings'))
                    ->schema([
                        Forms\Components\TextInput::make('discount_rate')
                            ->label(__('admin.partner_tiers.discount_rate'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.0001),
                        Forms\Components\TextInput::make('commission_rate')
                            ->label(__('admin.partner_tiers.commission_rate'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.0001),
                        Forms\Components\TextInput::make('minimum_order_value')
                            ->label(__('admin.partner_tiers.minimum_order_value'))
                            ->suffix('€')
                            ->numeric()
                            ->step(0.01),
                    ])
                    ->columns(3),
                Forms\Components\Section::make(__('admin.partner_tiers.sections.benefits'))
                    ->schema([
                        Forms\Components\Repeater::make('benefits')
                            ->label(__('admin.partner_tiers.benefits'))
                            ->schema([
                                Forms\Components\TextInput::make('benefit')
                                    ->label(__('admin.partner_tiers.benefit'))
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->defaultItems(0)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['benefit'] ?? null),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.partner_tiers.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('admin.partner_tiers.code'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('admin.partner_tiers.is_enabled'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('discount_rate')
                    ->label(__('admin.partner_tiers.discount_rate'))
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('commission_rate')
                    ->label(__('admin.partner_tiers.commission_rate'))
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_enabled')
                    ->label(__('admin.partner_tiers.is_enabled')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPartnerTiers::route('/'),
            'create' => Pages\CreatePartnerTier::route('/create'),
            'view' => Pages\ViewPartnerTier::route('/{record}'),
            'edit' => Pages\EditPartnerTier::route('/{record}/edit'),
        ];
    }
}
