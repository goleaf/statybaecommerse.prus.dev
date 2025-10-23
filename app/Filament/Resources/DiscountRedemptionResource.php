<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountRedemptionResource\Pages;
use App\Filament\Resources\DiscountRedemptionResource\RelationManagers\CodeRelationManager;
use App\Filament\Resources\DiscountRedemptionResource\RelationManagers\DiscountRelationManager;
use App\Filament\Resources\DiscountRedemptionResource\RelationManagers\UserRelationManager;
use App\Models\DiscountRedemption;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use UnitEnum;

final class DiscountRedemptionResource extends Resource
{
    protected static ?string $model = DiscountRedemption::class;

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Discounts';
    }

    public static function getNavigationIcon(): BackedEnum|string|null
    {
        return 'heroicon-o-ticket';
    }

    public static function getPluralModelLabel(): string
    {
        return __('discount_redemptions.plural');
    }

    public static function getModelLabel(): string
    {
        return __('discount_redemptions.single');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('discount_redemptions.sections.associations'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('discount_id')
                                ->label(__('discount_redemptions.fields.discount'))
                                ->relationship('discount', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('code_id')
                                ->label(__('discount_redemptions.fields.code'))
                                ->relationship('code', 'code')
                                ->searchable()
                                ->preload()
                                ->required(),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Select::make('user_id')
                                ->label(__('discount_redemptions.fields.user'))
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload(),
                            Select::make('order_id')
                                ->label(__('discount_redemptions.fields.order'))
                                ->relationship('order', 'number')
                                ->searchable()
                                ->preload(),
                        ]),
                ]),
            Section::make(__('discount_redemptions.sections.redemption_details'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('amount_saved')
                                ->label(__('discount_redemptions.fields.amount_saved'))
                                ->numeric()
                                ->minValue(0)
                                ->required()
                                ->prefix('€'),
                            TextInput::make('currency_code')
                                ->label(__('discount_redemptions.fields.currency_code'))
                                ->length(3)
                                ->default('EUR')
                                ->required(),
                            Select::make('status')
                                ->label(__('discount_redemptions.fields.status'))
                                ->options([
                                    'pending'   => __('discount_redemptions.statuses.pending'),
                                    'redeemed'  => __('discount_redemptions.statuses.redeemed'),
                                    'expired'   => __('discount_redemptions.statuses.expired'),
                                    'cancelled' => __('discount_redemptions.statuses.cancelled'),
                                ])
                                ->default('pending')
                                ->required(),
                        ]),
                    DateTimePicker::make('redeemed_at')
                        ->label(__('discount_redemptions.fields.redeemed_at'))
                        ->seconds(false)
                        ->displayFormat('Y-m-d H:i')
                        ->default(now())
                        ->required(),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('ip_address')
                                ->label(__('discount_redemptions.fields.ip_address'))
                                ->ip()
                                ->nullable(),
                            TextInput::make('user_agent')
                                ->label(__('discount_redemptions.fields.user_agent'))
                                ->maxLength(255)
                                ->nullable(),
                        ]),
                ]),
            Section::make(__('discount_redemptions.sections.additional_information'))
                ->schema([
                    Textarea::make('notes')
                        ->label(__('discount_redemptions.fields.notes'))
                        ->rows(3)
                        ->nullable(),
                    KeyValue::make('metadata')
                        ->label(__('discount_redemptions.fields.metadata'))
                        ->keyLabel(__('discount_redemptions.fields.metadata_key'))
                        ->valueLabel(__('discount_redemptions.fields.metadata_value'))
                        ->columnSpanFull(),
                ])
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code.code')
                    ->label(__('discount_redemptions.fields.code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('discount.name')
                    ->label(__('discount_redemptions.fields.discount'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('discount_redemptions.fields.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order.number')
                    ->label(__('discount_redemptions.fields.order'))
                    ->formatStateUsing(fn (?string $state) => $state ? Str::upper($state) : '-')
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('amount_saved')
                    ->label(__('discount_redemptions.fields.amount_saved'))
                    ->money(fn (DiscountRedemption $record) => $record->currency_code ?? 'EUR')
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label(__('discount_redemptions.fields.status'))
                    ->colors([
                        'success' => 'redeemed',
                        'warning' => 'pending',
                        'danger'  => 'expired',
                        'gray'    => 'cancelled',
                    ])
                    ->icons([
                        'heroicon-m-check-circle'         => 'redeemed',
                        'heroicon-m-clock'                => 'pending',
                        'heroicon-m-x-mark'               => 'cancelled',
                        'heroicon-m-exclamation-triangle' => 'expired',
                    ])
                    ->sortable(),
                TextColumn::make('redeemed_at')
                    ->label(__('discount_redemptions.fields.redeemed_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('currency_code')
                    ->label(__('discount_redemptions.fields.currency_code'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('discount_redemptions.fields.status'))
                    ->options([
                        'pending'   => __('discount_redemptions.statuses.pending'),
                        'redeemed'  => __('discount_redemptions.statuses.redeemed'),
                        'expired'   => __('discount_redemptions.statuses.expired'),
                        'cancelled' => __('discount_redemptions.statuses.cancelled'),
                    ]),
                SelectFilter::make('currency_code')
                    ->label(__('discount_redemptions.fields.currency_code'))
                    ->options([
                        'EUR' => 'EUR',
                        'USD' => 'USD',
                        'GBP' => 'GBP',
                    ]),
                Filter::make('redeemed_range')
                    ->form([
                        DateTimePicker::make('from')
                            ->label(__('discount_redemptions.filters.redeemed_from')),
                        DateTimePicker::make('until')
                            ->label(__('discount_redemptions.filters.redeemed_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date): Builder => $q->where('redeemed_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date): Builder => $q->where('redeemed_at', '<=', $date));
                    }),
                TernaryFilter::make('has_order')
                    ->label(__('discount_redemptions.filters.has_order'))
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('order_id'),
                        false: fn (Builder $query): Builder => $query->whereNull('order_id'),
                    ),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('redeemed_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            DiscountRelationManager::class,
            CodeRelationManager::class,
            UserRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDiscountRedemptions::route('/'),
            'create' => Pages\CreateDiscountRedemption::route('/create'),
            'view'   => Pages\ViewDiscountRedemption::route('/{record}'),
            'edit'   => Pages\EditDiscountRedemption::route('/{record}/edit'),
        ];
    }
}
