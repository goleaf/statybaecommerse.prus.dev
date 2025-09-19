<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\UserProductInteractionResource\Pages;
use App\Models\Product;
use App\Models\User;
use App\Models\UserProductInteraction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * UserProductInteractionResource
 *
 * Filament v4 resource for UserProductInteraction management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class UserProductInteractionResource extends Resource
{
    protected static ?string $model = UserProductInteraction::class;
    protected static ?int $navigationSort = 7;
    protected static ?string $recordTitleAttribute = 'interaction_type';
    protected static $navigationGroup = NavigationGroup::Analytics;

    public static function getNavigationLabel(): string
    {
        return __('admin.user_product_interactions.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.user_product_interactions.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.user_product_interactions.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.user_product_interactions.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label(__('admin.user_product_interactions.user'))
                                    ->options(User::pluck('name', 'id'))
                                    ->required()
                                    ->searchable(),
                                Select::make('product_id')
                                    ->label(__('admin.user_product_interactions.product'))
                                    ->options(Product::pluck('name', 'id'))
                                    ->required()
                                    ->searchable(),
                                Select::make('interaction_type')
                                    ->label(__('admin.user_product_interactions.interaction_type'))
                                    ->options([
                                        'view' => __('admin.user_product_interactions.interaction_types.view'),
                                        'click' => __('admin.user_product_interactions.interaction_types.click'),
                                        'add_to_cart' => __('admin.user_product_interactions.interaction_types.add_to_cart'),
                                        'purchase' => __('admin.user_product_interactions.interaction_types.purchase'),
                                        'review' => __('admin.user_product_interactions.interaction_types.review'),
                                        'share' => __('admin.user_product_interactions.interaction_types.share'),
                                    ])
                                    ->required()
                                    ->default('view'),
                                TextInput::make('rating')
                                    ->label(__('admin.user_product_interactions.rating'))
                                    ->numeric()
                                    ->step(0.1)
                                    ->minValue(0)
                                    ->maxValue(5),
                                TextInput::make('count')
                                    ->label(__('admin.user_product_interactions.count'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1),
                                DateTimePicker::make('first_interaction')
                                    ->label(__('admin.user_product_interactions.first_interaction'))
                                    ->default(now()),
                                DateTimePicker::make('last_interaction')
                                    ->label(__('admin.user_product_interactions.last_interaction'))
                                    ->default(now()),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('admin.user_product_interactions.user'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('product.name')
                    ->label(__('admin.user_product_interactions.product'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),
                TextColumn::make('interaction_type')
                    ->label(__('admin.user_product_interactions.interaction_type'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'view' => 'info',
                        'click' => 'success',
                        'add_to_cart' => 'warning',
                        'purchase' => 'danger',
                        'review' => 'primary',
                        'share' => 'secondary',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('rating')
                    ->label(__('admin.user_product_interactions.rating'))
                    ->numeric(decimalPlaces: 1)
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('count')
                    ->label(__('admin.user_product_interactions.count'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('first_interaction')
                    ->label(__('admin.user_product_interactions.first_interaction'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('last_interaction')
                    ->label(__('admin.user_product_interactions.last_interaction'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label(__('admin.user_product_interactions.user'))
                    ->options(User::pluck('name', 'id'))
                    ->searchable(),
                SelectFilter::make('product_id')
                    ->label(__('admin.user_product_interactions.product'))
                    ->options(Product::pluck('name', 'id'))
                    ->searchable(),
                SelectFilter::make('interaction_type')
                    ->label(__('admin.user_product_interactions.interaction_type'))
                    ->options([
                        'view' => __('admin.user_product_interactions.interaction_types.view'),
                        'click' => __('admin.user_product_interactions.interaction_types.click'),
                        'add_to_cart' => __('admin.user_product_interactions.interaction_types.add_to_cart'),
                        'purchase' => __('admin.user_product_interactions.interaction_types.purchase'),
                        'review' => __('admin.user_product_interactions.interaction_types.review'),
                        'share' => __('admin.user_product_interactions.interaction_types.share'),
                    ]),
                Filter::make('has_rating')
                    ->label(__('admin.user_product_interactions.has_rating'))
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('rating')),
                Filter::make('high_rating')
                    ->label(__('admin.user_product_interactions.high_rating'))
                    ->query(fn(Builder $query): Builder => $query->where('rating', '>=', 4.0)),
                Filter::make('recent_interactions')
                    ->label(__('admin.user_product_interactions.recent_interactions'))
                    ->query(fn(Builder $query): Builder => $query->where('last_interaction', '>=', now()->subDays(7))),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('increment')
                    ->label(__('admin.user_product_interactions.increment'))
                    ->icon('heroicon-o-plus')
                    ->action(function (UserProductInteraction $record): void {
                        $record->incrementInteraction();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('increment_all')
                        ->label(__('admin.user_product_interactions.increment_all'))
                        ->icon('heroicon-o-plus')
                        ->action(function (Collection $records): void {
                            $records->each->incrementInteraction();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('last_interaction', 'desc')
            ->poll('30s');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserProductInteractions::route('/'),
            'create' => Pages\CreateUserProductInteraction::route('/create'),
            'view' => Pages\ViewUserProductInteraction::route('/{record}'),
            'edit' => Pages\EditUserProductInteraction::route('/{record}/edit'),
        ];
    }
}
