<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\CustomerGroupResource\Pages;
use App\Models\CustomerGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * CustomerGroupResource
 *
 * Filament v4 resource for CustomerGroup management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class CustomerGroupResource extends Resource
{
    protected static ?string $model = CustomerGroup::class;

    protected static string | UnitEnum | null $navigationGroup = "Products";

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('customer_groups.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return "Customers";
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('customer_groups.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('customer_groups.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('customer_groups.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('customer_groups.name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('code')
                                ->label(__('customer_groups.code'))
                                ->required()
                                ->maxLength(50)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    Textarea::make('description')
                        ->label(__('customer_groups.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            Section::make(__('customer_groups.pricing_settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('discount_percentage')
                                ->label(__('customer_groups.discount_percentage'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->maxValue(100)
                                ->suffix('%')
                                ->helperText(__('customer_groups.discount_percentage_help')),
                            TextInput::make('discount_fixed')
                                ->label(__('customer_groups.discount_fixed'))
                                ->numeric()
                                ->prefix('€')
                                ->step(0.01)
                                ->minValue(0)
                                ->helperText(__('customer_groups.discount_fixed_help')),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Toggle::make('has_special_pricing')
                                ->label(__('customer_groups.has_special_pricing'))
                                ->default(false),
                            Toggle::make('has_volume_discounts')
                                ->label(__('customer_groups.has_volume_discounts'))
                                ->default(false),
                        ]),
                ]),
            Section::make(__('customer_groups.permissions'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('can_view_prices')
                                ->label(__('customer_groups.can_view_prices'))
                                ->default(true),
                            Toggle::make('can_place_orders')
                                ->label(__('customer_groups.can_place_orders'))
                                ->default(true),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Toggle::make('can_view_catalog')
                                ->label(__('customer_groups.can_view_catalog'))
                                ->default(true),
                            Toggle::make('can_use_coupons')
                                ->label(__('customer_groups.can_use_coupons'))
                                ->default(true),
                        ]),
                ]),
            Section::make(__('customer_groups.settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('customer_groups.is_active'))
                                ->default(true),
                            Toggle::make('is_default')
                                ->label(__('customer_groups.is_default')),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('sort_order')
                                ->label(__('customer_groups.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                            Select::make('type')
                                ->label(__('customer_groups.type'))
                                ->options([
                                    'regular' => __('customer_groups.types.regular'),
                                    'vip' => __('customer_groups.types.vip'),
                                    'wholesale' => __('customer_groups.types.wholesale'),
                                    'retail' => __('customer_groups.types.retail'),
                                    'corporate' => __('customer_groups.types.corporate'),
                                ])
                                ->default('regular'),
                        ]),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('customer_groups.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('code')
                    ->label(__('customer_groups.code'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('type')
                    ->label(__('customer_groups.type'))
                    ->formatStateUsing(fn(string $state): string => __("customer_groups.types.{$state}"))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'regular' => 'blue',
                        'vip' => 'gold',
                        'wholesale' => 'green',
                        'retail' => 'purple',
                        'corporate' => 'orange',
                        default => 'gray',
                    }),
                TextColumn::make('discount_percentage')
                    ->label(__('customer_groups.discount_percentage'))
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn($state): string => $state ? $state . '%' : '-')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('discount_fixed')
                    ->label(__('customer_groups.discount_fixed'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('customers_count')
                    ->label(__('customer_groups.customers_count'))
                    ->counts('customers')
                    ->sortable()
                    ->alignCenter(),
                IconColumn::make('has_special_pricing')
                    ->label(__('customer_groups.has_special_pricing'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('has_volume_discounts')
                    ->label(__('customer_groups.has_volume_discounts'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('can_view_prices')
                    ->label(__('customer_groups.can_view_prices'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('can_place_orders')
                    ->label(__('customer_groups.can_place_orders'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('customer_groups.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_default')
                    ->label(__('customer_groups.is_default'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('customer_groups.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('customer_groups.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('customer_groups.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('customer_groups.type'))
                    ->options([
                        'regular' => __('customer_groups.types.regular'),
                        'vip' => __('customer_groups.types.vip'),
                        'wholesale' => __('customer_groups.types.wholesale'),
                        'retail' => __('customer_groups.types.retail'),
                        'corporate' => __('customer_groups.types.corporate'),
                    ]),
                TernaryFilter::make('is_active')
                    ->label(__('customer_groups.is_active'))
                    ->boolean()
                    ->trueLabel(__('customer_groups.active_only'))
                    ->falseLabel(__('customer_groups.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_default')
                    ->label(__('customer_groups.is_default'))
                    ->boolean()
                    ->trueLabel(__('customer_groups.default_only'))
                    ->falseLabel(__('customer_groups.non_default_only'))
                    ->native(false),
                TernaryFilter::make('has_special_pricing')
                    ->label(__('customer_groups.has_special_pricing'))
                    ->boolean()
                    ->trueLabel(__('customer_groups.special_pricing_only'))
                    ->falseLabel(__('customer_groups.no_special_pricing'))
                    ->native(false),
                TernaryFilter::make('has_volume_discounts')
                    ->label(__('customer_groups.has_volume_discounts'))
                    ->boolean()
                    ->trueLabel(__('customer_groups.volume_discounts_only'))
                    ->falseLabel(__('customer_groups.no_volume_discounts'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn(CustomerGroup $record): string => $record->is_active ? __('customer_groups.deactivate') : __('customer_groups.activate'))
                    ->icon(fn(CustomerGroup $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn(CustomerGroup $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (CustomerGroup $record): void {
                        $record->update(['is_active' => !$record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? __('customer_groups.activated_successfully') : __('customer_groups.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('set_default')
                    ->label(__('customer_groups.set_default'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn(CustomerGroup $record): bool => !$record->is_default)
                    ->action(function (CustomerGroup $record): void {
                        // Remove default from other customer groups
                        CustomerGroup::where('is_default', true)->update(['is_default' => false]);

                        // Set this customer group as default
                        $record->update(['is_default' => true]);

                        Notification::make()
                            ->title(__('customer_groups.set_as_default_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('customer_groups.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);

                            Notification::make()
                                ->title(__('customer_groups.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('customer_groups.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);

                            Notification::make()
                                ->title(__('customer_groups.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    /**
     * Get the relations for this resource.
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomerGroups::route('/'),
            'create' => Pages\CreateCustomerGroup::route('/create'),
            'view' => Pages\ViewCustomerGroup::route('/{record}'),
            'edit' => Pages\EditCustomerGroup::route('/{record}/edit'),
        ];
    }
}
