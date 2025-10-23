<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountRedemptionResource\Pages;
use App\Filament\Resources\DiscountRedemptionResource\RelationManagers\CodeRelationManager;
use App\Filament\Resources\DiscountRedemptionResource\RelationManagers\DiscountRelationManager;
use App\Filament\Resources\DiscountRedemptionResource\RelationManagers\UserRelationManager;
use App\Models\DiscountRedemption;
use BackedEnum;
use Filament\Actions\Action as TableAction;
use Filament\Actions\BulkAction as TableBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use UnitEnum;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;

final class DiscountRedemptionResource extends Resource
{
    protected static ?string $model = DiscountRedemption::class;

    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-receipt-percent';
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Discounts';
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.discount_redemptions.plural');
    }

    public static function getModelLabel(): string
    {
        return __('admin.discount_redemptions.single');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('admin.discount_redemptions.form.sections.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('discount_id')
                                ->label(__('admin.discount_redemptions.form.fields.discount'))
                                ->relationship('discount', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('code_id')
                                ->label(__('admin.discount_redemptions.form.fields.discount_code'))
                                ->relationship('code', 'code')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('user_id')
                                ->label(__('admin.discount_redemptions.form.fields.user'))
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('order_id')
                                ->label(__('admin.discount_redemptions.form.fields.order'))
                                ->relationship('order', 'id')
                                ->searchable()
                                ->preload()
                                ->nullable(),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('amount_saved')
                                ->label(__('admin.discount_redemptions.form.fields.discount_amount'))
                                ->numeric()
                                ->minValue(0)
                                ->prefix('€')
                                ->required(),
                            TextInput::make('currency_code')
                                ->label(__('admin.discount_redemptions.form.fields.currency_code'))
                                ->maxLength(3)
                                ->default('EUR')
                                ->required(),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Select::make('status')
                                ->label(__('admin.discount_redemptions.form.fields.status'))
                                ->options([
                                    'pending' => __('frontend.discount_redemptions.status.pending'),
                                    'redeemed' => __('frontend.discount_redemptions.status.redeemed'),
                                    'expired' => __('frontend.discount_redemptions.status.expired'),
                                    'cancelled' => __('frontend.discount_redemptions.status.cancelled'),
                                    'refunded' => __('frontend.discount_redemptions.status.refunded'),
                                ])
                                ->default('pending')
                                ->required(),
                            DateTimePicker::make('redeemed_at')
                                ->label(__('admin.discount_redemptions.form.fields.redeemed_at'))
                                ->default(now())
                                ->required(),
                        ]),
                    Flatpickr::make('redeemed_at')
                        ->time(true)
                        ->time24hr(true)
                        ->seconds(false)
                        ->format('Y-m-d H:i')
                        ->label(__('discount_redemptions.fields.redeemed_at'))
                        ->seconds(false)
                        ->displayFormat('Y-m-d H:i')
                        ->default(now())
                        ->required(),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('ip_address')
                                ->label(__('admin.discount_redemptions.form.fields.ip_address'))
                                ->maxLength(45)
                                ->columnSpan(1),
                            TextInput::make('user_agent')
                                ->label(__('admin.discount_redemptions.form.fields.user_agent'))
                                ->columnSpan(1),
                        ]),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('redeemed_at', 'desc')
            ->columns([
                TextColumn::make('code.code')
                    ->label(__('admin.discount_redemptions.table.discount_code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('discount.name')
                    ->label(__('admin.discount_redemptions.table.discount'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('admin.discount_redemptions.table.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order.id')
                    ->label(__('admin.discount_redemptions.table.order'))
                    ->formatStateUsing(fn (?int $state): string => $state ? '#'.$state : '-')
                    ->sortable(),
                TextColumn::make('amount_saved')
                    ->label(__('admin.discount_redemptions.table.discount_amount'))
                    ->formatStateUsing(fn (DiscountRedemption $record): string => number_format((float) $record->amount_saved, 2).' '.($record->currency_code ?? 'EUR'))
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('admin.discount_redemptions.table.status'))
                    ->badge()
                    ->colors([
                        'success' => static fn (string $state): bool => $state === 'redeemed',
                        'warning' => static fn (string $state): bool => $state === 'pending',
                        'gray' => static fn (string $state): bool => $state === 'expired',
                        'danger' => static fn (string $state): bool => $state === 'cancelled',
                        'info' => static fn (string $state): bool => $state === 'refunded',
                    ]),
                TextColumn::make('redeemed_at')
                    ->label(__('admin.discount_redemptions.table.redeemed_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.discount_redemptions.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('deleted_at')
                    ->label(__('admin.discount_redemptions.table.deleted'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('code_id')
                    ->label(__('admin.discount_redemptions.filters.discount_code'))
                    ->relationship('code', 'code')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('user_id')
                    ->label(__('admin.discount_redemptions.filters.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('redeemed_at')
                    ->label(__('admin.discount_redemptions.filters.redeemed_at'))
                    ->form([
                        Flatpickr::make('from')
                            ->time(true)
                            ->time24hr(true)
                            ->seconds(false)
                            ->format('Y-m-d H:i')
                            ->rangePicker()
                            ->label(__('discount_redemptions.filters.redeemed_from')),
                        Flatpickr::make('until')
                            ->time(true)
                            ->time24hr(true)
                            ->seconds(false)
                            ->format('Y-m-d H:i')
                            ->rangePicker()
                            ->label(__('discount_redemptions.filters.redeemed_until')),
                    ])
                    ->query(static function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, static fn (Builder $builder, string $date): Builder => $builder->where('redeemed_at', '>=', $date))
                            ->when($data['until'] ?? null, static fn (Builder $builder, string $date): Builder => $builder->where('redeemed_at', '<=', $date));
                    }),
                TernaryFilter::make('recent')
                    ->label(__('admin.discount_redemptions.filters.recent'))
                    ->nullable()
                    ->queries(
                        true: static fn (Builder $query): Builder => $query->where('redeemed_at', '>=', Carbon::now()->subDays(7)),
                        false: static fn (Builder $query): Builder => $query->where('redeemed_at', '<', Carbon::now()->subDays(7)),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                TableAction::make('refund')
                    ->label(__('admin.discount_redemptions.actions.refund'))
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->requiresConfirmation()
                    ->visible(fn (DiscountRedemption $record): bool => $record->status !== 'refunded')
                    ->action(function (DiscountRedemption $record): void {
                        $record->update(['status' => 'refunded']);
                    })
                    ->successNotificationTitle(__('admin.discount_redemptions.refund_successful')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    TableBulkAction::make('bulk_refund')
                        ->label(__('admin.discount_redemptions.actions.bulk_refund'))
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->requiresConfirmation()
                        ->action(function (EloquentCollection $records): void {
                            $records->each->update(['status' => 'refunded']);
                        })
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle(__('admin.discount_redemptions.bulk_refund_successful')),
                ]),
            ]);
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
            'index' => Pages\ListDiscountRedemptions::route('/'),
            'create' => Pages\CreateDiscountRedemption::route('/create'),
            'view' => Pages\ViewDiscountRedemption::route('/{record}'),
            'edit' => Pages\EditDiscountRedemption::route('/{record}/edit'),
        ];
    }
}
