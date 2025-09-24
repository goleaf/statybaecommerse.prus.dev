<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

final class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('coupons.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('coupons.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('coupons.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('code')
                                ->label(__('coupons.code'))
                                ->required()
                                ->maxLength(50)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash'])
                                ->helperText(__('coupons.code_help')),
                            TextInput::make('name')
                                ->label(__('coupons.name'))
                                ->maxLength(255),
                        ]),
                    Textarea::make('description')
                        ->label(__('coupons.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            Section::make(__('coupons.discount_settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('type')
                                ->label(__('coupons.type'))
                                ->options([
                                    'percentage' => __('coupons.types.percentage'),
                                    'fixed' => __('coupons.types.fixed'),
                                    'free_shipping' => __('coupons.types.free_shipping'),
                                ])
                                ->default('percentage')
                                ->live(),
                            TextInput::make('value')
                                ->label(__('coupons.value'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->helperText(__('coupons.value_help')),
                            TextInput::make('minimum_amount')
                                ->label(__('coupons.minimum_amount'))
                                ->prefix('€')
                                ->minValue(0),
                            TextInput::make('maximum_discount')
                                ->label(__('coupons.maximum_discount'))
                                ->prefix('€')
                                ->minValue(0),
                        ]),
                ]),
            Section::make(__('coupons.usage_limits'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('usage_limit')
                                ->label(__('coupons.usage_limit'))
                                ->minValue(1)
                                ->helperText(__('coupons.usage_limit_help')),
                            TextInput::make('usage_limit_per_user')
                                ->label(__('coupons.usage_limit_per_user'))
                                ->helperText(__('coupons.usage_limit_per_user_help')),
                            TextInput::make('used_count')
                                ->label(__('coupons.used_count'))
                                ->default(0)
                                ->disabled(),
                            TextInput::make('remaining_uses')
                                ->label(__('coupons.remaining_uses'))
                                ->default(0)
                                ->disabled(),
                        ]),
                ]),
            Section::make(__('coupons.validity'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            DateTimePicker::make('valid_from')
                                ->label(__('coupons.valid_from'))
                                ->default(now())
                                ->displayFormat('d/m/Y H:i'),
                            DateTimePicker::make('valid_until')
                                ->label(__('coupons.valid_until'))
                                ->displayFormat('d/m/Y H:i'),
                        ]),
                ]),
            Section::make(__('coupons.targeting'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('customer_group_id')
                                ->label(__('coupons.customer_group'))
                                ->relationship('customerGroup', 'name')
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->required()
                                        ->maxLength(255),
                                    Textarea::make('description')
                                        ->maxLength(500),
                                ]),
                            Toggle::make('is_first_time_only')
                                ->label(__('coupons.is_first_time_only'))
                                ->default(false),
                        ]),
                ]),
            Section::make(__('coupons.settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('coupons.is_active'))
                                ->default(true),
                            Toggle::make('is_public')
                                ->label(__('coupons.is_public'))
                                ->default(false),
                            Toggle::make('is_auto_apply')
                                ->label(__('coupons.is_auto_apply'))
                                ->default(false),
                            Toggle::make('is_stackable')
                                ->label(__('coupons.is_stackable'))
                                ->default(false),
                        ]),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('coupons.code'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->badge()
                    ->color('blue'),
                TextColumn::make('name')
                    ->label(__('coupons.name'))
                    ->limit(50),
                TextColumn::make('type')
                    ->label(__('coupons.type'))
                    ->formatStateUsing(fn (string $state): string => __("coupons.types.{$state}"))
                    ->color(fn (string $state): string => match ($state) {
                        'percentage' => 'green',
                        'fixed' => 'blue',
                        'free_shipping' => 'purple',
                        default => 'gray',
                    }),
                TextColumn::make('value')
                    ->label(__('coupons.value'))
                    ->formatStateUsing(function ($state, $record): string {
                        if ($record->type === 'percentage') {
                            return $state.'%';
                        } elseif ($record->type === 'free_shipping') {
                            return __('coupons.free_shipping');
                        }

                        return '€'.number_format($state, 2);
                    })
                    ->sortable(),
                TextColumn::make('usage_limit')
                    ->label(__('coupons.usage_limit'))
                    ->numeric()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('used_count')
                    ->label(__('coupons.used_count'))
                    ->color(fn ($state, $record): string => $record->usage_limit && $state >= $record->usage_limit ? 'danger' : 'success'),
                TextColumn::make('remaining_uses')
                    ->label(__('coupons.remaining_uses'))
                    ->color(fn ($state): string => $state <= 0 ? 'danger' : 'success'),
                TextColumn::make('customerGroup.name')
                    ->label(__('coupons.customer_group'))
                    ->color('gray'),
                BadgeColumn::make('is_active')
                    ->label(__('coupons.status'))
                    ->formatStateUsing(fn (bool $state): string => $state ? __('coupons.active') : __('coupons.inactive'))
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ]),
                IconColumn::make('is_public')
                    ->label(__('coupons.is_public'))
                    ->boolean(),
                IconColumn::make('is_auto_apply')
                    ->label(__('coupons.is_auto_apply'))
                    ->boolean(),
                IconColumn::make('is_stackable')
                    ->label(__('coupons.is_stackable'))
                    ->boolean(),
                TextColumn::make('valid_from')
                    ->label(__('coupons.valid_from'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('valid_until')
                    ->label(__('coupons.valid_until'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('coupons.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('coupons.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'percentage' => __('coupons.types.percentage'),
                        'fixed' => __('coupons.types.fixed'),
                        'free_shipping' => __('coupons.types.free_shipping'),
                    ]),
                SelectFilter::make('customer_group_id')
                    ->relationship('customerGroup', 'name')
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label(__('coupons.is_active'))
                    ->trueLabel(__('coupons.active_only'))
                    ->falseLabel(__('coupons.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_public')
                    ->trueLabel(__('coupons.public_only'))
                    ->falseLabel(__('coupons.private_only'))
                    ->native(false),
                TernaryFilter::make('is_auto_apply')
                    ->trueLabel(__('coupons.auto_apply_only'))
                    ->falseLabel(__('coupons.manual_apply_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn (Coupon $record): string => $record->is_active ? __('coupons.deactivate') : __('coupons.activate'))
                    ->icon(fn (Coupon $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Coupon $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Coupon $record): void {
                        $record->update(['is_active' => ! $record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? __('coupons.activated_successfully') : __('coupons.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('duplicate')
                    ->label(__('coupons.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->action(function (Coupon $record): void {
                        $newCoupon = $record->replicate();
                        $newCoupon->code = $record->code.'_copy_'.time();
                        $newCoupon->name = $record->name.' (Copy)';
                        $newCoupon->used_count = 0;
                        $newCoupon->save();

                        Notification::make()
                            ->title(__('coupons.duplicated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('coupons.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('coupons.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('coupons.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);

                            Notification::make()
                                ->title(__('coupons.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Get the relations for this resource.
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'view' => Pages\ViewCoupon::route('/{record}'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
