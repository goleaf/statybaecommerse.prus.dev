<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerTierResource\Pages;
use App\Models\PartnerTier;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Actions as Actions;
use Filament\Forms;
use Filament\Tables;
final class PartnerTierResource extends Resource
{
    protected static ?string $model = PartnerTier::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';


    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return __('admin.partner_tier.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.partner_tier.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.partner_tier.form.basic_info'))
                    ->components([
                        Forms\Components\TextInput::make('name')
                            ->label(__('admin.partner_tier.form.name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->label(__('admin.partner_tier.form.code'))
                            ->required()
                            ->unique(PartnerTier::class, 'code', ignoreRecord: true)
                            ->maxLength(100),
                        Forms\Components\TextInput::make('discount_rate')
                            ->label(__('admin.partner_tier.form.discount_rate'))
                            ->numeric()
                            ->step(0.0001)
                            ->minValue(0)
                            ->maxValue(1)
                            ->helperText(__('admin.partner_tier.form.discount_rate_help')),
                        Forms\Components\TextInput::make('commission_rate')
                            ->label(__('admin.partner_tier.form.commission_rate'))
                            ->numeric()
                            ->step(0.0001)
                            ->minValue(0)
                            ->maxValue(1)
                            ->helperText(__('admin.partner_tier.form.commission_rate_help')),
                        Forms\Components\TextInput::make('minimum_order_value')
                            ->label(__('admin.partner_tier.form.minimum_order_value'))
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€'),
                        Forms\Components\Toggle::make('is_enabled')
                            ->label(__('admin.partner_tier.form.active'))
                            ->default(true),
                    ])
                    ->columns(2),
                Section::make(__('admin.partner_tier.form.benefits'))
                    ->components([
                        Forms\Components\KeyValue::make('benefits')
                            ->label(__('admin.partner_tier.form.benefits'))
                            ->keyLabel(__('admin.partner_tier.form.benefit_key'))
                            ->valueLabel(__('admin.partner_tier.form.benefit_value'))
                            ->addButtonLabel(__('admin.partner_tier.form.add_benefit'))
                            ->reorderable(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.partner_tier.table.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('admin.partner_tier.table.code'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('discount_rate')
                    ->label(__('admin.partner_tier.table.discount_rate'))
                    ->formatStateUsing(fn($state) => $state === null ? null : number_format((float) $state * 100, 2) . '%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('commission_rate')
                    ->label(__('admin.partner_tier.table.commission_rate'))
                    ->formatStateUsing(fn($state) => $state === null ? null : number_format((float) $state * 100, 2) . '%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('minimum_order_value')
                    ->label(__('admin.partner_tier.table.minimum_order_value'))
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('partners_count')
                    ->label(__('admin.partner_tier.table.partners_count'))
                    ->counts('partners')
                    ->badge()
                    ->color('info'),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('admin.partner_tier.table.active'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.partner_tier.table.created_at'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_enabled')
                    ->label(__('admin.partner_tier.filters.active')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
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
