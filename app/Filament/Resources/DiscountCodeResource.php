<?php

declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountCodeResource\Pages;
use App\Models\DiscountCode;
use BackedEnum;
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
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

final class DiscountCodeResource extends Resource
{
    protected static ?string $model = DiscountCode::class;

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Marketing';
    }

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-tag';
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('discount_codes.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('discount_codes.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('discount_codes.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('code')
                                ->label(__('discount_codes.code'))
                                ->required()
                                ->maxLength(50)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash'])
                                ->helperText(__('discount_codes.code_help')),
                            TextInput::make('name')
                                ->label(__('discount_codes.name'))
                                ->maxLength(255),
                        ]),
                    Textarea::make('description')
                        ->label(__('discount_codes.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            Section::make(__('discount_codes.discount_settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('type')
                                ->label(__('discount_codes.type'))
                                ->options([
                                    'percentage' => __('discount_codes.types.percentage'),
                                    'fixed' => __('discount_codes.types.fixed'),
                                    'free_shipping' => __('discount_codes.types.free_shipping'),
                                    'buy_x_get_y' => __('discount_codes.types.buy_x_get_y'),
                                ])
                                ->default('percentage')
                                ->live(),
                            TextInput::make('value')
                                ->label(__('discount_codes.value'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->helperText(__('discount_codes.value_help')),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('minimum_amount')
                                ->label(__('discount_codes.minimum_amount'))
                                ->prefix('€')
                                ->minValue(0),
                            TextInput::make('maximum_discount')
                                ->label(__('discount_codes.maximum_discount'))
                                ->prefix('€')
                                ->minValue(0),
                        ]),
                ]),
            Section::make(__('discount_codes.usage_limits'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('usage_limit')
                                ->label(__('discount_codes.usage_limit'))
                                ->numeric()
                                ->minValue(1)
                                ->helperText(__('discount_codes.usage_limit_help')),
                            TextInput::make('usage_limit_per_user')
                                ->label(__('discount_codes.usage_limit_per_user'))
                                ->numeric()
                                ->minValue(1)
                                ->helperText(__('discount_codes.usage_limit_per_user_help')),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('used_count')
                                ->label(__('discount_codes.used_count'))
                                ->numeric()
                                ->default(0)
                                ->disabled(),
                            TextInput::make('remaining_uses')
                                ->label(__('discount_codes.remaining_uses'))
                                ->numeric()
                                ->disabled(),
                        ]),
                ]),
            Section::make(__('discount_codes.validity'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            DateTimePicker::make('valid_from')
                                ->label(__('discount_codes.valid_from'))
                                ->default(now())
                                ->displayFormat('d/m/Y H:i'),
                            DateTimePicker::make('valid_until')
                                ->label(__('discount_codes.valid_until'))
                                ->displayFormat('d/m/Y H:i'),
                        ]),
                ]),
            Section::make(__('discount_codes.targeting'))
                ->schema([
                    Select::make('customer_group_id')
                        ->label(__('discount_codes.customer_group'))
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
                        ->label(__('discount_codes.is_first_time_only'))
                        ->default(false),
                ]),
            Section::make(__('discount_codes.settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('discount_codes.is_active'))
                                ->default(true),
                            Toggle::make('is_public')
                                ->label(__('discount_codes.is_public'))
                                ->default(false),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_auto_apply')
                                ->label(__('discount_codes.is_auto_apply'))
                                ->default(false),
                            Toggle::make('is_stackable')
                                ->label(__('discount_codes.is_stackable'))
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
                    ->label(__('discount_codes.code'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->badge()
                    ->color('blue'),
                TextColumn::make('name')
                    ->label(__('discount_codes.name'))
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('discount_codes.type'))
                    ->formatStateUsing(fn (string $state): string => __("discount_codes.types.{$state}"))
                    ->color(fn (string $state): string => match ($state) {
                        'percentage' => 'green',
                        'fixed' => 'blue',
                        'free_shipping' => 'purple',
                        'buy_x_get_y' => 'orange',
                        default => 'gray',
                    }),
                TextColumn::make('value')
                    ->label(__('discount_codes.value'))
                    ->formatStateUsing(function ($state, $record): string {
                        if ($record->type === 'percentage') {
                            return $state.'%';
                        } elseif ($record->type === 'free_shipping') {
                            return __('discount_codes.free_shipping');
                        }

                        return '€'.number_format($state, 2);
                    })
                    ->sortable(),
                TextColumn::make('usage_limit')
                    ->label(__('discount_codes.usage_limit'))
                    ->numeric()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('used_count')
                    ->label(__('discount_codes.used_count'))
                    ->numeric()
                    ->color(fn ($state, $record): string => $record->usage_limit && $state >= $record->usage_limit ? 'danger' : 'success'),
                TextColumn::make('remaining_uses')
                    ->label(__('discount_codes.remaining_uses'))
                    ->numeric()
                    ->color(fn ($state): string => $state <= 0 ? 'danger' : 'success'),
                TextColumn::make('customerGroup.name')
                    ->label(__('discount_codes.customer_group'))
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('is_active')
                    ->label(__('discount_codes.status'))
                    ->formatStateUsing(fn (bool $state): string => $state ? __('discount_codes.active') : __('discount_codes.inactive'))
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                IconColumn::make('is_public')
                    ->label(__('discount_codes.is_public'))
                    ->boolean(),
                IconColumn::make('is_auto_apply')
                    ->label(__('discount_codes.is_auto_apply'))
                    ->boolean(),
                IconColumn::make('is_stackable')
                    ->label(__('discount_codes.is_stackable'))
                    ->boolean(),
                TextColumn::make('valid_from')
                    ->label(__('discount_codes.valid_from'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('valid_until')
                    ->label(__('discount_codes.valid_until'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('discount_codes.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('discount_codes.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'percentage' => __('discount_codes.types.percentage'),
                        'fixed' => __('discount_codes.types.fixed'),
                        'free_shipping' => __('discount_codes.types.free_shipping'),
                        'buy_x_get_y' => __('discount_codes.types.buy_x_get_y'),
                    ]),
                SelectFilter::make('customer_group_id')
                    ->relationship('customerGroup', 'name')
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label(__('discount_codes.is_active'))
                    ->trueLabel(__('discount_codes.active_only'))
                    ->falseLabel(__('discount_codes.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_public')
                    ->label(__('discount_codes.is_public'))
                    ->trueLabel(__('discount_codes.public_only'))
                    ->falseLabel(__('discount_codes.private_only'))
                    ->native(false),
                TernaryFilter::make('is_auto_apply')
                    ->label(__('discount_codes.is_auto_apply'))
                    ->trueLabel(__('discount_codes.auto_apply_only'))
                    ->falseLabel(__('discount_codes.manual_apply_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn (DiscountCode $record): string => $record->is_active ? __('discount_codes.deactivate') : __('discount_codes.activate'))
                    ->icon(fn (DiscountCode $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (DiscountCode $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (DiscountCode $record): void {
                        $record->update(['is_active' => ! $record->is_active]);
                        Notification::make()
                            ->title($record->is_active ? __('discount_codes.activated_successfully') : __('discount_codes.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('duplicate')
                    ->label(__('discount_codes.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->action(function (DiscountCode $record): void {
                        $newDiscountCode = $record->replicate();
                        $newDiscountCode->code = $record->code.'_copy_'.time();
                        $newDiscountCode->name = $record->name.' (Copy)';
                        $newDiscountCode->used_count = 0;
                        $newDiscountCode->save();

                        Notification::make()
                            ->title(__('discount_codes.duplicated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('discount_codes.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('discount_codes.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('discount_codes.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('discount_codes.bulk_deactivated_success'))
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
            'index' => Pages\ListDiscountCodes::route('/'),
            'create' => Pages\CreateDiscountCode::route('/create'),
            'view' => Pages\ViewDiscountCode::route('/{record}'),
            'edit' => Pages\EditDiscountCode::route('/{record}/edit'),
        ];
    }
}
