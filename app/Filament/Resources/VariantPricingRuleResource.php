<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\VariantPricingRuleResource\Pages;
use App\Models\CustomerGroup;
use App\Models\ProductVariant;
use App\Models\VariantPricingRule;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Grid;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * VariantPricingRuleResource
 *
 * Filament v4 resource for VariantPricingRule management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class VariantPricingRuleResource extends Resource
{
    protected static ?string $model = VariantPricingRule::class;

    protected static string|UnitEnum|null $navigationGroup = 'Products';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('variant_pricing_rules.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return "Products";
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('variant_pricing_rules.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('variant_pricing_rules.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('variant_pricing_rules.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('variant_pricing_rules.name'))
                                ->required()
                                ->maxLength(255),
                            Select::make('type')
                                ->label(__('variant_pricing_rules.type'))
                                ->options([
                                    'percentage' => __('variant_pricing_rules.types.percentage'),
                                    'fixed' => __('variant_pricing_rules.types.fixed'),
                                    'tier' => __('variant_pricing_rules.types.tier'),
                                    'bulk' => __('variant_pricing_rules.types.bulk'),
                                ])
                                ->required()
                                ->default('percentage')
                                ->live(),
                        ]),
                    Textarea::make('description')
                        ->label(__('variant_pricing_rules.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            Section::make(__('variant_pricing_rules.targeting'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('product_variant_id')
                                ->label(__('variant_pricing_rules.product_variant'))
                                ->relationship('productVariant', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('customer_group_id')
                                ->label(__('variant_pricing_rules.customer_group'))
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
                        ]),
                ]),
            Section::make(__('variant_pricing_rules.pricing'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('value')
                                ->label(__('variant_pricing_rules.value'))
                                ->numeric()
                                ->required()
                                ->step(0.01)
                                ->minValue(0)
                                ->helperText(__('variant_pricing_rules.value_help')),
                            TextInput::make('min_quantity')
                                ->label(__('variant_pricing_rules.min_quantity'))
                                ->numeric()
                                ->minValue(1)
                                ->default(1),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('max_quantity')
                                ->label(__('variant_pricing_rules.max_quantity'))
                                ->numeric()
                                ->minValue(1),
                            TextInput::make('priority')
                                ->label(__('variant_pricing_rules.priority'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->helperText(__('variant_pricing_rules.priority_help')),
                        ]),
                ]),
            Section::make(__('variant_pricing_rules.conditions'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            DateTimePicker::make('valid_from')
                                ->label(__('variant_pricing_rules.valid_from'))
                                ->default(now())
                                ->displayFormat('d/m/Y H:i'),
                            DateTimePicker::make('valid_until')
                                ->label(__('variant_pricing_rules.valid_until'))
                                ->displayFormat('d/m/Y H:i'),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('variant_pricing_rules.is_active'))
                                ->default(true),
                            Toggle::make('is_cumulative')
                                ->label(__('variant_pricing_rules.is_cumulative'))
                                ->default(false),
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
                    ->label(__('variant_pricing_rules.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('type')
                    ->label(__('variant_pricing_rules.type'))
                    ->formatStateUsing(fn(string $state): string => __("variant_pricing_rules.types.{$state}"))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'percentage' => 'blue',
                        'fixed' => 'green',
                        'tier' => 'purple',
                        'bulk' => 'orange',
                        default => 'gray',
                    }),
                TextColumn::make('productVariant.name')
                    ->label(__('variant_pricing_rules.product_variant'))
                    ->sortable()
                    ->limit(50),
                TextColumn::make('customerGroup.name')
                    ->label(__('variant_pricing_rules.customer_group'))
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('value')
                    ->label(__('variant_pricing_rules.value'))
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(function ($state, $record): string {
                        if ($record->type === 'percentage') {
                            return $state . '%';
                        }
                        return '€' . number_format($state, 2);
                    }),
                TextColumn::make('min_quantity')
                    ->label(__('variant_pricing_rules.min_quantity'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('max_quantity')
                    ->label(__('variant_pricing_rules.max_quantity'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('priority')
                    ->label(__('variant_pricing_rules.priority'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('variant_pricing_rules.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_cumulative')
                    ->label(__('variant_pricing_rules.is_cumulative'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('valid_from')
                    ->label(__('variant_pricing_rules.valid_from'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('valid_until')
                    ->label(__('variant_pricing_rules.valid_until'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('variant_pricing_rules.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('variant_pricing_rules.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('variant_pricing_rules.type'))
                    ->options([
                        'percentage' => __('variant_pricing_rules.types.percentage'),
                        'fixed' => __('variant_pricing_rules.types.fixed'),
                        'tier' => __('variant_pricing_rules.types.tier'),
                        'bulk' => __('variant_pricing_rules.types.bulk'),
                    ]),
                SelectFilter::make('product_variant_id')
                    ->label(__('variant_pricing_rules.product_variant'))
                    ->relationship('productVariant', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('customer_group_id')
                    ->label(__('variant_pricing_rules.customer_group'))
                    ->relationship('customerGroup', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label(__('variant_pricing_rules.is_active'))
                    ->boolean()
                    ->trueLabel(__('variant_pricing_rules.active_only'))
                    ->falseLabel(__('variant_pricing_rules.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_cumulative')
                    ->label(__('variant_pricing_rules.is_cumulative'))
                    ->boolean()
                    ->trueLabel(__('variant_pricing_rules.cumulative_only'))
                    ->falseLabel(__('variant_pricing_rules.non_cumulative_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn(VariantPricingRule $record): string => $record->is_active ? __('variant_pricing_rules.deactivate') : __('variant_pricing_rules.activate'))
                    ->icon(fn(VariantPricingRule $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn(VariantPricingRule $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (VariantPricingRule $record): void {
                        $record->update(['is_active' => !$record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? __('variant_pricing_rules.activated_successfully') : __('variant_pricing_rules.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('variant_pricing_rules.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);

                            Notification::make()
                                ->title(__('variant_pricing_rules.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('variant_pricing_rules.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);

                            Notification::make()
                                ->title(__('variant_pricing_rules.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('priority', 'desc');
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
            'index' => Pages\ListVariantPricingRules::route('/'),
            'create' => Pages\CreateVariantPricingRule::route('/create'),
            'view' => Pages\ViewVariantPricingRule::route('/{record}'),
            'edit' => Pages\EditVariantPricingRule::route('/{record}/edit'),
        ];
    }
}
