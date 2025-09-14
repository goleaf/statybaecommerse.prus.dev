<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\VariantPricingRuleResource\Pages;
use App\Models\VariantPricingRule;
use App\Models\Product;
use App\Enums\NavigationGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use BackedEnum;
use UnitEnum;

/**
 * VariantPricingRuleResource
 * 
 * Filament v4 resource for VariantPricingRule management in the admin panel.
 */
final class VariantPricingRuleResource extends Resource
{
    protected static ?string $model = VariantPricingRule::class;
    
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = NavigationGroup::Products;
    
    protected static ?int $navigationSort = 4;
    protected static ?string $recordTitleAttribute = 'rule_name';

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
        return NavigationGroup::Products->label();
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
    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make(__('variant_pricing_rules.tabs.main'))
                ->tabs([
                    Tab::make(__('variant_pricing_rules.tabs.basic_information'))
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Section::make(__('variant_pricing_rules.sections.basic_information'))
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Select::make('product_id')
                                                ->label(__('variant_pricing_rules.fields.product'))
                                                ->relationship('product', 'name')
                                                ->required()
                                                ->searchable()
                                                ->preload()
                                                ->columnSpan(1),

                                            TextInput::make('rule_name')
                                                ->label(__('variant_pricing_rules.fields.rule_name'))
                                                ->required()
                                                ->maxLength(255)
                                                ->columnSpan(1),
                                        ]),

                                    Grid::make(2)
                                        ->schema([
                                            Select::make('rule_type')
                                                ->label(__('variant_pricing_rules.fields.rule_type'))
                                                ->options([
                                                    'size_based' => __('variant_pricing_rules.rule_types.size_based'),
                                                    'quantity_based' => __('variant_pricing_rules.rule_types.quantity_based'),
                                                    'customer_group_based' => __('variant_pricing_rules.rule_types.customer_group_based'),
                                                    'time_based' => __('variant_pricing_rules.rule_types.time_based'),
                                                ])
                                                ->default('size_based')
                                                ->required()
                                                ->columnSpan(1),

                                            TextInput::make('priority')
                                                ->label(__('variant_pricing_rules.fields.priority'))
                                                ->numeric()
                                                ->default(0)
                                                ->columnSpan(1),
                                        ]),

                                    Toggle::make('is_active')
                                        ->label(__('variant_pricing_rules.fields.is_active'))
                                        ->default(true),
                                ])
                                ->columns(1),
                        ]),

                    Tab::make(__('variant_pricing_rules.tabs.conditions'))
                        ->icon('heroicon-o-funnel')
                        ->schema([
                            Section::make(__('variant_pricing_rules.sections.conditions'))
                                ->schema([
                                    Repeater::make('conditions')
                                        ->label(__('variant_pricing_rules.fields.conditions'))
                                        ->schema([
                                            Select::make('attribute')
                                                ->label(__('variant_pricing_rules.fields.attribute'))
                                                ->options([
                                                    'size' => __('variant_pricing_rules.attributes.size'),
                                                    'variant_type' => __('variant_pricing_rules.attributes.variant_type'),
                                                    'price' => __('variant_pricing_rules.attributes.price'),
                                                    'weight' => __('variant_pricing_rules.attributes.weight'),
                                                ])
                                                ->required(),

                                            Select::make('operator')
                                                ->label(__('variant_pricing_rules.fields.operator'))
                                                ->options([
                                                    'equals' => __('variant_pricing_rules.operators.equals'),
                                                    'not_equals' => __('variant_pricing_rules.operators.not_equals'),
                                                    'greater_than' => __('variant_pricing_rules.operators.greater_than'),
                                                    'less_than' => __('variant_pricing_rules.operators.less_than'),
                                                    'contains' => __('variant_pricing_rules.operators.contains'),
                                                    'not_contains' => __('variant_pricing_rules.operators.not_contains'),
                                                ])
                                                ->required(),

                                            TextInput::make('value')
                                                ->label(__('variant_pricing_rules.fields.value'))
                                                ->required(),
                                        ])
                                        ->columns(3)
                                        ->addActionLabel(__('variant_pricing_rules.actions.add_condition'))
                                        ->collapsible(),
                                ])
                                ->columns(1),
                        ]),

                    Tab::make(__('variant_pricing_rules.tabs.pricing_modifiers'))
                        ->icon('heroicon-o-currency-euro')
                        ->schema([
                            Section::make(__('variant_pricing_rules.sections.pricing_modifiers'))
                                ->schema([
                                    Repeater::make('pricing_modifiers')
                                        ->label(__('variant_pricing_rules.fields.pricing_modifiers'))
                                        ->schema([
                                            Select::make('type')
                                                ->label(__('variant_pricing_rules.fields.modifier_type'))
                                                ->options([
                                                    'percentage' => __('variant_pricing_rules.modifier_types.percentage'),
                                                    'fixed_amount' => __('variant_pricing_rules.modifier_types.fixed_amount'),
                                                    'multiplier' => __('variant_pricing_rules.modifier_types.multiplier'),
                                                ])
                                                ->required(),

                                            TextInput::make('value')
                                                ->label(__('variant_pricing_rules.fields.modifier_value'))
                                                ->numeric()
                                                ->step(0.01)
                                                ->required(),

                                            Repeater::make('conditions')
                                                ->label(__('variant_pricing_rules.fields.modifier_conditions'))
                                                ->schema([
                                                    Select::make('attribute')
                                                        ->label(__('variant_pricing_rules.fields.attribute'))
                                                        ->options([
                                                            'size' => __('variant_pricing_rules.attributes.size'),
                                                            'variant_type' => __('variant_pricing_rules.attributes.variant_type'),
                                                            'price' => __('variant_pricing_rules.attributes.price'),
                                                            'weight' => __('variant_pricing_rules.attributes.weight'),
                                                        ])
                                                        ->required(),

                                                    Select::make('operator')
                                                        ->label(__('variant_pricing_rules.fields.operator'))
                                                        ->options([
                                                            'equals' => __('variant_pricing_rules.operators.equals'),
                                                            'not_equals' => __('variant_pricing_rules.operators.not_equals'),
                                                            'greater_than' => __('variant_pricing_rules.operators.greater_than'),
                                                            'less_than' => __('variant_pricing_rules.operators.less_than'),
                                                            'contains' => __('variant_pricing_rules.operators.contains'),
                                                            'not_contains' => __('variant_pricing_rules.operators.not_contains'),
                                                        ])
                                                        ->required(),

                                                    TextInput::make('value')
                                                        ->label(__('variant_pricing_rules.fields.value'))
                                                        ->required(),
                                                ])
                                                ->columns(3)
                                                ->addActionLabel(__('variant_pricing_rules.actions.add_modifier_condition'))
                                                ->collapsible(),
                                        ])
                                        ->columns(2)
                                        ->addActionLabel(__('variant_pricing_rules.actions.add_modifier'))
                                        ->collapsible(),
                                ])
                                ->columns(1),
                        ]),

                    Tab::make(__('variant_pricing_rules.tabs.schedule'))
                        ->icon('heroicon-o-clock')
                        ->schema([
                            Section::make(__('variant_pricing_rules.sections.schedule'))
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            DateTimePicker::make('starts_at')
                                                ->label(__('variant_pricing_rules.fields.starts_at'))
                                                ->columnSpan(1),

                                            DateTimePicker::make('ends_at')
                                                ->label(__('variant_pricing_rules.fields.ends_at'))
                                                ->columnSpan(1),
                                        ]),
                                ])
                                ->columns(1),
                        ]),
                ])
                ->columnSpanFull(),
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
                TextColumn::make('product.name')
                    ->label(__('variant_pricing_rules.fields.product'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('rule_name')
                    ->label(__('variant_pricing_rules.fields.rule_name'))
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('rule_type')
                    ->label(__('variant_pricing_rules.fields.rule_type'))
                    ->colors([
                        'primary' => 'size_based',
                        'success' => 'quantity_based',
                        'warning' => 'customer_group_based',
                        'info' => 'time_based',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'size_based' => __('variant_pricing_rules.rule_types.size_based'),
                        'quantity_based' => __('variant_pricing_rules.rule_types.quantity_based'),
                        'customer_group_based' => __('variant_pricing_rules.rule_types.customer_group_based'),
                        'time_based' => __('variant_pricing_rules.rule_types.time_based'),
                        default => $state,
                    }),

                TextColumn::make('priority')
                    ->label(__('variant_pricing_rules.fields.priority'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('formatted_conditions')
                    ->label(__('variant_pricing_rules.fields.conditions'))
                    ->limit(50),

                TextColumn::make('formatted_modifiers')
                    ->label(__('variant_pricing_rules.fields.pricing_modifiers'))
                    ->limit(50),

                IconColumn::make('is_currently_active')
                    ->label(__('variant_pricing_rules.fields.is_active'))
                    ->boolean(),

                TextColumn::make('starts_at')
                    ->label(__('variant_pricing_rules.fields.starts_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ends_at')
                    ->label(__('variant_pricing_rules.fields.ends_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('variant_pricing_rules.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label(__('variant_pricing_rules.fields.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('rule_type')
                    ->label(__('variant_pricing_rules.fields.rule_type'))
                    ->options([
                        'size_based' => __('variant_pricing_rules.rule_types.size_based'),
                        'quantity_based' => __('variant_pricing_rules.rule_types.quantity_based'),
                        'customer_group_based' => __('variant_pricing_rules.rule_types.customer_group_based'),
                        'time_based' => __('variant_pricing_rules.rule_types.time_based'),
                    ]),

                TernaryFilter::make('is_active')
                    ->label(__('variant_pricing_rules.fields.is_active')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label(__('variant_pricing_rules.actions.activate'))
                        ->icon('heroicon-o-check-circle')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('variant_pricing_rules.messages.bulk_activate_success'))
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('deactivate')
                        ->label(__('variant_pricing_rules.actions.deactivate'))
                        ->icon('heroicon-o-x-circle')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('variant_pricing_rules.messages.bulk_deactivate_success'))
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('priority', 'desc');
    }

    /**
     * Get the resource pages.
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVariantPricingRules::route('/'),
            'create' => Pages\CreateVariantPricingRule::route('/create'),
            'edit' => Pages\EditVariantPricingRule::route('/{record}/edit'),
        ];
    }
}
