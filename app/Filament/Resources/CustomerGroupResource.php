<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerGroupResource\Pages;
use BackedEnum;
use App\Filament\Resources\CustomerGroupResource\RelationManagers;
use App\Filament\Widgets\CustomerGroupStatsWidget;
use App\Models\CustomerGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\NavigationGroup;
use UnitEnum;

final /**
 * CustomerGroupResource
 * 
 * Filament resource for admin panel management.
 */
class CustomerGroupResource extends Resource
{
    protected static ?string $model = CustomerGroup::class;

    /** @var BackedEnum|string|null */
    protected static $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::Users->label();
    }

    public static function getNavigationLabel(): string
    {
        return __('customer_groups.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('customer_groups.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('customer_groups.navigation_label');
    }

    public static function form(Schema $schema): Schema {
        return $schema->schema([
                Section::make(__('customer_groups.navigation_label'))
                    ->description(__('customer_groups.description'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('customer_groups.name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, callable $set) => $operation === 'create' ? $set('slug', \Str::slug($state)) : null
                                    ),
                                TextInput::make('slug')
                                    ->label(__('customer_groups.slug'))
                                    ->required()
                                    ->maxLength(50)
                                    ->unique(ignoreRecord: true)
                                    ->rules(['alpha_dash']),
                            ]),
                        Textarea::make('description')
                            ->label(__('customer_groups.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make(__('customer_groups.discount_percentage'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('discount_percentage')
                                    ->label(__('customer_groups.discount_percentage'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText(__('customer_groups.validation_discount_percentage_max')),
                                Toggle::make('is_enabled')
                                    ->label(__('customer_groups.is_enabled'))
                                    ->default(true)
                                    ->helperText(__('customer_groups.is_enabled')),
                            ]),
                    ]),

                Section::make(__('customer_groups.conditions'))
                    ->schema([
                        KeyValue::make('conditions')
                            ->label(__('customer_groups.conditions'))
                            ->keyLabel(__('customer_groups.conditions'))
                            ->valueLabel(__('customer_groups.conditions'))
                            ->helperText(__('customer_groups.conditions'))
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('customer_groups.table_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label(__('customer_groups.table_slug'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('customer_groups.table_description'))
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }

                        return $state;
                    }),

                Tables\Columns\TextColumn::make('discount_percentage')
                    ->label(__('customer_groups.table_discount_percentage'))
                    ->numeric(decimalPlaces: 2)
                    ->suffix('%')
                    ->sortable()
                    ->color(fn ($state): string => $state > 0 ? 'success' : 'gray')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state > 0 ? $state.'%' : __('customer_groups.no_discount')),

                Tables\Columns\TextColumn::make('users_count')
                    ->label(__('customer_groups.table_users_count'))
                    ->counts('users')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('customer_groups.table_is_enabled'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('customer_groups.table_created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('customer_groups.table_updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_enabled')
                    ->label(__('customer_groups.filter_enabled'))
                    ->placeholder(__('customer_groups.all_groups'))
                    ->trueLabel(__('customer_groups.enabled_only'))
                    ->falseLabel(__('customer_groups.disabled_only')),

                Filter::make('with_discount')
                    ->label(__('customer_groups.filter_with_discount'))
                    ->query(fn (Builder $query): Builder => $query->where('discount_percentage', '>', 0)),

                Filter::make('discount_range')
                    ->form([
                        TextInput::make('discount_from')
                            ->label(__('customer_groups.discount_from'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        TextInput::make('discount_to')
                            ->label(__('customer_groups.discount_to'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['discount_from'],
                                fn (Builder $query, $discount): Builder => $query->where('discount_percentage', '>=', $discount),
                            )
                            ->when(
                                $data['discount_to'],
                                fn (Builder $query, $discount): Builder => $query->where('discount_percentage', '<=', $discount),
                            );
                    }),

                Filter::make('users_count_range')
                    ->form([
                        TextInput::make('users_from')
                            ->label(__('customer_groups.users_from'))
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('users_to')
                            ->label(__('customer_groups.users_to'))
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['users_from'],
                                fn (Builder $query, $users): Builder => $query->has('users', '>=', $users),
                            )
                            ->when(
                                $data['users_to'],
                                fn (Builder $query, $users): Builder => $query->has('users', '<=', $users),
                            );
                    }),

                DateFilter::make('created_at')
                    ->label(__('customer_groups.filter_created_date'))
                    ->displayFormat('d/m/Y'),
            ])
            ->actions([
                ViewAction::make()
                    ->label(__('customer_groups.action_view')),
                EditAction::make()
                    ->label(__('customer_groups.action_edit')),
                DeleteAction::make()
                    ->label(__('customer_groups.action_delete')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('customer_groups.action_delete')),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UsersRelationManager::class,
            RelationManagers\DiscountsRelationManager::class,
            RelationManagers\PriceListsRelationManager::class,
            RelationManagers\CampaignsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            CustomerGroupStatsWidget::class,
        ];
    }

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
