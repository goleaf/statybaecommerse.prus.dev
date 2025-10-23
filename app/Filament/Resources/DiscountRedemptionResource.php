<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountRedemptionResource\Pages;
use App\Filament\Resources\DiscountRedemptionResource\RelationManagers\CodeRelationManager;
use App\Filament\Resources\DiscountRedemptionResource\RelationManagers\DiscountRelationManager;
use App\Filament\Resources\DiscountRedemptionResource\RelationManagers\UserRelationManager;
use App\Models\DiscountRedemption;
use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use UnitEnum;

final class DiscountRedemptionResource extends Resource
{
    protected static ?string $model = DiscountRedemption::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Discounts';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?int $navigationSort = null;

    public static function getNavigationBadgeColor(): string|array|null
    {
        return null;
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.discount_redemptions.plural');
    }

    public static function getModelLabel(): string
    {
        return __('admin.discount_redemptions.single');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            SchemaSection::make(__('discount_redemptions.sections.associations'))
                ->schema([
                    SchemaGrid::make(2)
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
                    SchemaGrid::make(2)
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
                    SchemaGrid::make(2)
                        ->schema([
                            Select::make('status')
                                ->label(__('admin.discount_redemptions.form.fields.status'))
                                ->options([
                                    'pending'   => __('frontend.discount_redemptions.status.pending'),
                                    'redeemed'  => __('frontend.discount_redemptions.status.redeemed'),
                                    'expired'   => __('frontend.discount_redemptions.status.expired'),
                                    'cancelled' => __('frontend.discount_redemptions.status.cancelled'),
                                    'refunded'  => __('frontend.discount_redemptions.status.refunded'),
                                ])
                                ->default('pending')
                                ->required(),
                            SupportFlatpickr::makeDateTime('redeemed_at')
                                ->label(__('admin.discount_redemptions.form.fields.redeemed_at'))
                                ->seconds(false)
                                ->displayFormat('Y-m-d H:i')
                                ->default(now())
                                ->required(),
                        ]),
                    SchemaGrid::make(2)
                        ->schema([
                            TextInput::make('ip_address')
                                ->label(__('admin.discount_redemptions.form.fields.ip_address'))
                                ->maxLength(45),
                            TextInput::make('user_agent')
                                ->label(__('admin.discount_redemptions.form.fields.user_agent')),
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
                    ->formatStateUsing(fn (?int $state): string => $state !== null ? '#'.$state : '-')
                    ->sortable(),
                TextColumn::make('amount_saved')
                    ->label(__('admin.discount_redemptions.table.discount_amount'))
                    ->formatStateUsing(
                        fn (DiscountRedemption $record): string => number_format((float) $record->amount_saved, 2).' '.($record->currency_code ?? 'EUR')
                    )
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('discount_redemptions.fields.status'))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'redeemed'  => 'success',
                        'pending'   => 'warning',
                        'expired'   => 'danger',
                        'cancelled' => 'gray',
                        default     => 'primary',
                    })
                    ->icon(fn (?string $state): ?string => match ($state) {
                        'redeemed'  => 'heroicon-m-check-circle',
                        'pending'   => 'heroicon-m-clock',
                        'cancelled' => 'heroicon-m-x-mark',
                        'expired'   => 'heroicon-m-exclamation-triangle',
                        default     => null,
                    })
                    ->sortable(),
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
                        SupportFlatpickr::makeDateTime('from')
                            ->label(__('discount_redemptions.filters.redeemed_from')),
                        SupportFlatpickr::makeDateTime('until')
                            ->label(__('discount_redemptions.filters.redeemed_until')),
                    ])
                    ->query(static function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                static fn (Builder $builder, string $date): Builder => $builder->where('redeemed_at', '>=', $date)
                            )
                            ->when(
                                $data['until'] ?? null,
                                static fn (Builder $builder, string $date): Builder => $builder->where('redeemed_at', '<=', $date)
                            );
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
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('refund')
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
                    BulkAction::make('bulk_refund')
                        ->label(__('admin.discount_redemptions.actions.bulk_refund'))
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
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
