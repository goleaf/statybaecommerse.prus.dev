<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use App\Models\Scopes\ActiveScope;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Filament resource responsible for managing coupon CRUD, filters and table actions.
 */
final class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    /**
     * Provide a translated singular label for navigation and actions.
     */
    public static function getModelLabel(): string
    {
        return __('coupons.single');
    }

    /**
     * Provide a translated plural label for navigation and tables.
     */
    public static function getPluralModelLabel(): string
    {
        return __('coupons.plural');
    }

    /**
     * Define the infolist schema so the view page can surface the coupon attributes tested.
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('code')
                    ->label(__('coupons.code')),
                TextEntry::make('name')
                    ->label(__('coupons.name')),
                TextEntry::make('type')
                    ->label(__('coupons.type')),
            ]);
    }

    /**
     * Define the form schema used in create and edit pages.
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Basic details for identifying the coupon in the admin panel.
                Section::make(__('coupons.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('code')
                                    ->label(__('coupons.code'))
                                    ->required()
                                    ->maxLength(50)
                                    ->unique(ignoreRecord: true)
                                    ->rule('alpha_dash')
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
                // Discount configuration specifying how the coupon behaves.
                Section::make(__('coupons.discount_settings'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('type')
                                    ->label(__('coupons.type'))
                                    ->options([
                                        'percentage'    => __('coupons.types.percentage'),
                                        'fixed'         => __('coupons.types.fixed'),
                                        'free_shipping' => __('coupons.types.free_shipping'),
                                    ])
                                    ->default('percentage')
                                    ->required(),
                                TextInput::make('value')
                                    ->label(__('coupons.value'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->required(fn (Get $get): bool => $get('type') !== 'free_shipping')
                                    ->helperText(__('coupons.value_help')),
                                TextInput::make('minimum_amount')
                                    ->label(__('coupons.minimum_amount'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->nullable(),
                                TextInput::make('maximum_discount')
                                    ->label(__('coupons.maximum_discount'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->nullable(),
                            ]),
                    ]),
                // Usage limit configuration for controlling how many times a coupon can be applied.
                Section::make(__('coupons.usage_limits'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('usage_limit')
                                    ->label(__('coupons.usage_limit'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->nullable(),
                                TextInput::make('usage_limit_per_user')
                                    ->label(__('coupons.usage_limit_per_user'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->nullable(),
                                TextInput::make('used_count')
                                    ->label(__('coupons.used_count'))
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),
                    ]),
                // Validity window fields allow scoping a coupon to specific time ranges.
                Section::make(__('coupons.validity'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('starts_at')
                                    ->label(__('coupons.valid_from'))
                                    ->seconds(false)
                                    ->nullable(),
                                DateTimePicker::make('expires_at')
                                    ->label(__('coupons.valid_until'))
                                    ->seconds(false)
                                    ->nullable(),
                            ]),
                    ]),
                // Toggle switches expose behaviour flags such as public visibility.
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
     * Configure the table columns, filters, and available actions for index pages.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Show the coupon code prominently as it is the primary identifier.
                TextColumn::make('code')
                    ->label(__('coupons.code'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label(__('coupons.name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('coupons.type'))
                    ->formatStateUsing(static fn (?string $state): string => $state ? __('coupons.types.' . $state) : '—'),
                TextColumn::make('value')
                    ->label(__('coupons.value'))
                    ->formatStateUsing(static function ($state, Coupon $record): string {
                        if ($record->type === 'percentage') {
                            return is_numeric($state) ? sprintf('%s%%', (float) $state) : '—';
                        }

                        if ($record->type === 'free_shipping') {
                            return __('coupons.free_shipping');
                        }

                        if (! is_numeric($state)) {
                            return '—';
                        }

                        return sprintf('€%0.2f', (float) $state);
                    }),
                IconColumn::make('is_active')
                    ->label(__('coupons.is_active'))
                    ->boolean(),
                IconColumn::make('is_public')
                    ->label(__('coupons.is_public'))
                    ->boolean(),
                IconColumn::make('is_auto_apply')
                    ->label(__('coupons.is_auto_apply'))
                    ->boolean(),
                IconColumn::make('is_stackable')
                    ->label(__('coupons.is_stackable'))
                    ->boolean(),
                TextColumn::make('starts_at')
                    ->label(__('coupons.valid_from'))
                    ->dateTime(),
                TextColumn::make('expires_at')
                    ->label(__('coupons.valid_until'))
                    ->dateTime(),
            ])
            ->filters([
                // Allow filtering by coupon type as required by the feature tests.
                SelectFilter::make('type')
                    ->options([
                        'percentage'    => __('coupons.types.percentage'),
                        'fixed'         => __('coupons.types.fixed'),
                        'free_shipping' => __('coupons.types.free_shipping'),
                    ]),
                // Boolean filters expose quick toggles for coupon flags used in tests.
                TernaryFilter::make('is_active')
                    ->label(__('coupons.is_active')),
                TernaryFilter::make('is_public')
                    ->label(__('coupons.is_public')),
                TernaryFilter::make('is_auto_apply')
                    ->label(__('coupons.is_auto_apply')),
            ])
            ->actions([
                // Core CRUD actions come from Filament to provide standard behaviour.
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                // Custom action toggles the active state inline from the list view.
                Action::make('toggle_active')
                    ->label(fn (Coupon $record): string => $record->is_active ? __('coupons.deactivate') : __('coupons.activate'))
                    ->icon(fn (Coupon $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Coupon $record): string => $record->is_active ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(function (Coupon $record): void {
                        // Flip the state without honouring the ActiveScope global scope so the update always applies.
                        $nextState = ! $record->is_active;
                        Coupon::withoutGlobalScopes([ActiveScope::class])
                            ->whereKey($record->getKey())
                            ->update(['is_active' => $nextState]);

                        $record->forceFill(['is_active' => $nextState]);

                        Notification::make()
                            ->title($nextState ? __('coupons.activated_successfully') : __('coupons.deactivated_successfully'))
                            ->success()
                            ->send();
                    }),
                // Duplicate action quickly clones the record for the scenario covered in the tests.
                Action::make('duplicate')
                    ->label(__('coupons.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->requiresConfirmation()
                    ->action(function (Coupon $record): void {
                        $timestamp = time();

                        $copy = $record->replicate();
                        $copy->code = $record->code . '_copy_' . $timestamp;
                        $copy->name = $record->name . ' (Copy)';
                        $copy->used_count = 0;
                        $copy->save();

                        Notification::make()
                            ->title(__('coupons.duplicated_successfully'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                // Default delete bulk action plus custom activation toggles.
                DeleteBulkAction::make(),
                BulkAction::make('activate')
                    ->label(__('coupons.activate_selected'))
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records): void {
                        $ids = $records->pluck('id')->all();

                        Coupon::withoutGlobalScopes([ActiveScope::class])
                            ->whereIn('id', $ids)
                            ->update(['is_active' => true]);
                    }),
                BulkAction::make('deactivate')
                    ->label(__('coupons.deactivate_selected'))
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Collection $records): void {
                        $ids = $records->pluck('id')->all();

                        Coupon::withoutGlobalScopes([ActiveScope::class])
                            ->whereIn('id', $ids)
                            ->update(['is_active' => false]);
                    }),
            ]);
    }

    /**
     * Remove the ActiveScope global scope so the resource can show inactive records.
     *
     * @return Builder<Coupon>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([ActiveScope::class]);
    }

    /**
     * Define relation managers (none required for these tests).
     */
    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Register the Filament pages that back this resource.
     */
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'view'   => Pages\ViewCoupon::route('/{record}'),
            'edit'   => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
