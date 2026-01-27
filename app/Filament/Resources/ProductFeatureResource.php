<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ProductFeatureResource\Pages;
use App\Models\ProductFeature;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class ProductFeatureResource extends BaseResource
{
    protected static ?string $model = ProductFeature::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Inventory;

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('admin.product_features.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.product_features.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.product_features.model_label');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            SchemaSection::make(__('admin.product_features.basic_information'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            Select::make('product_id')
                                ->label(__('messages.product'))
                                ->relationship('product', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('feature_type')
                                ->label(__('admin.products.feature_type'))
                                ->options([
                                    'specification' => __('admin.products.feature_specification'),
                                    'benefit' => __('admin.products.feature_benefit'),
                                    'performance' => __('admin.products.feature_performance'),
                                    'other' => __('admin.products.feature_other'),
                                ])
                                ->default('specification')
                                ->required(),
                            TextInput::make('feature_key')
                                ->label(__('admin.products.feature_key'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('feature_value')
                                ->label(__('admin.products.feature_value'))
                                ->numeric()
                                ->required(),
                            TextInput::make('weight')
                                ->label(__('admin.products.weight'))
                                ->numeric()
                                ->default(1),
                            Toggle::make('is_active')
                                ->label(__('admin.products.is_active'))
                                ->default(true),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('messages.product'))
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('feature_type')
                    ->label(__('admin.products.feature_type'))
                    ->formatStateUsing(static fn (?string $state): string => ucfirst((string) $state))
                    ->colors([
                        'primary' => 'specification',
                        'success' => 'benefit',
                        'warning' => 'performance',
                        'gray' => 'other',
                    ])
                    ->sortable(),
                TextColumn::make('feature_key')
                    ->label(__('admin.products.feature_key'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('feature_value')
                    ->label(__('admin.products.feature_value'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('weight')
                    ->label(__('admin.products.weight'))
                    ->numeric()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label(__('admin.products.is_active')),
            ])
            ->filters([
                SelectFilter::make('feature_type')
                    ->label(__('admin.products.feature_type'))
                    ->options([
                        'specification' => __('admin.products.feature_specification'),
                        'benefit' => __('admin.products.feature_benefit'),
                        'performance' => __('admin.products.feature_performance'),
                        'other' => __('admin.products.feature_other'),
                    ]),
                SelectFilter::make('product_id')
                    ->label(__('messages.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('is_active')
                    ->label(__('admin.products.is_active'))
                    ->options([
                        '1' => __('admin.products.active_only'),
                        '0' => __('admin.products.inactive_only'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if ($value === null || $value === '') {
                            return $query;
                        }

                        return $query->where('is_active', (bool) (int) $value);
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('feature_key');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductFeatures::route('/'),
            'create' => Pages\CreateProductFeature::route('/create'),
            'view' => Pages\ViewProductFeature::route('/{record}'),
            'edit' => Pages\EditProductFeature::route('/{record}/edit'),
        ];
    }
}
