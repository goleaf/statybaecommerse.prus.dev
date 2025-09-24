<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\UserProductInteractionResource\Pages;
use App\Models\UserProductInteraction;
use Filament\Actions\Action as TableAction;
use Filament\Actions\BulkAction as TableBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

final class UserProductInteractionResource extends Resource
{
    protected static UnitEnum|string|null $navigationGroup = 'Users';

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
            ->schema([
                SchemaSection::make(__('admin.user_product_interactions.basic_information'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label(__('admin.user_product_interactions.user'))
                                    ->relationship('user', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label(__('admin.users.name'))
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('email')
                                            ->label(__('admin.users.email'))
                                            ->email()
                                            ->required()
                                            ->maxLength(255),
                                    ]),
                                Select::make('product_id')
                                    ->label(__('admin.user_product_interactions.product'))
                                    ->relationship('product', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label(__('admin.products.name'))
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('sku')
                                            ->label(__('admin.products.sku'))
                                            ->maxLength(100),
                                    ]),
                                Select::make('interaction_type')
                                    ->label(__('admin.user_product_interactions.interaction_type'))
                                    ->options([
                                        'view' => __('admin.user_product_interactions.interaction_types.view'),
                                        'click' => __('admin.user_product_interactions.interaction_types.click'),
                                        'add_to_cart' => __('admin.user_product_interactions.interaction_types.add_to_cart'),
                                        'purchase' => __('admin.user_product_interactions.interaction_types.purchase'),
                                        'review' => __('admin.user_product_interactions.interaction_types.review'),
                                        'share' => __('admin.user_product_interactions.interaction_types.share'),
                                        'favorite' => __('admin.user_product_interactions.interaction_types.favorite'),
                                        'compare' => __('admin.user_product_interactions.interaction_types.compare'),
                                    ])
                                    ->required()
                                    ->default('view')
                                    ->searchable(),
                                TextInput::make('rating')
                                    ->label(__('admin.user_product_interactions.rating'))
                                    ->numeric()
                                    ->step(0.1)
                                    ->minValue(0)
                                    ->maxValue(5)
                                    ->suffix('/5')
                                    ->helperText(__('admin.user_product_interactions.rating_help')),
                                TextInput::make('count')
                                    ->label(__('admin.user_product_interactions.count'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->helperText(__('admin.user_product_interactions.count_help')),
                                DateTimePicker::make('first_interaction')
                                    ->label(__('admin.user_product_interactions.first_interaction'))
                                    ->default(now())
                                    ->displayFormat('d/m/Y H:i')
                                    ->seconds(false),
                                DateTimePicker::make('last_interaction')
                                    ->label(__('admin.user_product_interactions.last_interaction'))
                                    ->default(now())
                                    ->displayFormat('d/m/Y H:i')
                                    ->seconds(false),
                            ]),
                    ])
                    ->collapsible(),
                SchemaSection::make(__('admin.user_product_interactions.additional_information'))
                    ->schema([
                        Textarea::make('notes')
                            ->label(__('admin.user_product_interactions.notes'))
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText(__('admin.user_product_interactions.notes_help')),
                        Toggle::make('is_anonymous')
                            ->label(__('admin.user_product_interactions.is_anonymous'))
                            ->default(false)
                            ->helperText(__('admin.user_product_interactions.is_anonymous_help')),
                        Hidden::make('ip_address')
                            ->default(request()->ip()),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading(false)
            ->columns([
                TextColumn::make('id')
                    ->label(__('admin.common.id'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')
                    ->label(__('admin.user_product_interactions.user'))
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->copyable()
                    ->copyMessage(__('admin.user_product_interactions.user_copied'))
                    ->url(fn($record) => UserResource::getUrl('view', ['record' => $record->user_id]))
                    ->openUrlInNewTab(),
                TextColumn::make('product.name')
                    ->label(__('admin.user_product_interactions.product'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    })
                    ->copyable()
                    ->copyMessage(__('admin.user_product_interactions.product_copied')),
                TextColumn::make('product.sku')
                    ->label(__('admin.products.sku'))
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->copyable(),
                BadgeColumn::make('interaction_type')
                    ->label(__('admin.user_product_interactions.interaction_type'))
                    ->colors([
                        'info' => 'view',
                        'success' => 'click',
                        'warning' => 'add_to_cart',
                        'danger' => 'purchase',
                        'primary' => 'review',
                        'secondary' => 'share',
                        'gray' => 'favorite',
                        'slate' => 'compare',
                    ])
                    ->sortable()
                    ->searchable(),
                TextColumn::make('rating')
                    ->label(__('admin.user_product_interactions.rating'))
                    ->numeric(decimalPlaces: 1)
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state >= 4.5 => 'success',
                        $state >= 3.5 => 'warning',
                        $state >= 2.5 => 'info',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn($state) => $state ? $state . '/5' : __('admin.user_product_interactions.no_rating')),
                TextColumn::make('count')
                    ->label(__('admin.user_product_interactions.count'))
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state >= 10 => 'success',
                        $state >= 5 => 'warning',
                        $state >= 2 => 'info',
                        default => 'gray',
                    }),
                IconColumn::make('is_anonymous')
                    ->label(__('admin.user_product_interactions.is_anonymous'))
                    ->boolean()
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('first_interaction')
                    ->label(__('admin.user_product_interactions.first_interaction'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->since(),
                TextColumn::make('last_interaction')
                    ->label(__('admin.user_product_interactions.last_interaction'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->since(),
                TextColumn::make('ip_address')
                    ->label(__('admin.user_product_interactions.ip_address'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),
                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since(),
                TextColumn::make('updated_at')
                    ->label(__('admin.common.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label(__('admin.user_product_interactions.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                SelectFilter::make('product_id')
                    ->label(__('admin.user_product_interactions.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                SelectFilter::make('interaction_type')
                    ->label(__('admin.user_product_interactions.interaction_type'))
                    ->options([
                        'view' => __('admin.user_product_interactions.interaction_types.view'),
                        'click' => __('admin.user_product_interactions.interaction_types.click'),
                        'add_to_cart' => __('admin.user_product_interactions.interaction_types.add_to_cart'),
                        'purchase' => __('admin.user_product_interactions.interaction_types.purchase'),
                        'review' => __('admin.user_product_interactions.interaction_types.review'),
                        'share' => __('admin.user_product_interactions.interaction_types.share'),
                        'favorite' => __('admin.user_product_interactions.interaction_types.favorite'),
                        'compare' => __('admin.user_product_interactions.interaction_types.compare'),
                    ])
                    ->multiple(),
                TernaryFilter::make('is_anonymous')
                    ->label(__('admin.user_product_interactions.is_anonymous'))
                    ->placeholder(__('admin.user_product_interactions.all_records'))
                    ->trueLabel(__('admin.user_product_interactions.anonymous_only'))
                    ->falseLabel(__('admin.user_product_interactions.non_anonymous_only')),
                Filter::make('has_rating')
                    ->label(__('admin.user_product_interactions.has_rating'))
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('rating')),
                Filter::make('high_rating')
                    ->label(__('admin.user_product_interactions.high_rating'))
                    ->query(fn(Builder $query): Builder => $query->where('rating', '>=', 4.0)),
                Filter::make('low_rating')
                    ->label(__('admin.user_product_interactions.low_rating'))
                    ->query(fn(Builder $query): Builder => $query->where('rating', '<', 3.0)),
                Filter::make('recent_interactions')
                    ->label(__('admin.user_product_interactions.recent_interactions'))
                    ->query(fn(Builder $query): Builder => $query->where('last_interaction', '>=', now()->subDays(7))),
                Filter::make('this_month')
                    ->label(__('admin.user_product_interactions.this_month'))
                    ->query(fn(Builder $query): Builder => $query->where('last_interaction', '>=', now()->startOfMonth())),
                Filter::make('this_week')
                    ->label(__('admin.user_product_interactions.this_week'))
                    ->query(fn(Builder $query): Builder => $query->where('last_interaction', '>=', now()->startOfWeek())),
                Filter::make('today')
                    ->label(__('admin.user_product_interactions.today'))
                    ->query(fn(Builder $query): Builder => $query->where('last_interaction', '>=', now()->startOfDay())),
                Filter::make('high_count')
                    ->label(__('admin.user_product_interactions.high_count'))
                    ->query(fn(Builder $query): Builder => $query->where('count', '>=', 5)),
                Filter::make('single_interaction')
                    ->label(__('admin.user_product_interactions.single_interaction'))
                    ->query(fn(Builder $query): Builder => $query->where('count', '=', 1)),
            ])
            ->actions([
                ViewAction::make()
                    ->label(__('admin.common.view'))
                    ->icon('heroicon-o-eye'),
                EditAction::make()
                    ->label(__('admin.common.edit'))
                    ->icon('heroicon-o-pencil'),
                TableAction::make('increment')
                    ->label(__('admin.user_product_interactions.increment'))
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->action(function (UserProductInteraction $record): void {
                        $record->incrementInteraction();
                        Notification::make()
                            ->title(__('admin.user_product_interactions.incremented_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.user_product_interactions.increment_heading'))
                    ->modalDescription(__('admin.user_product_interactions.increment_description')),
                TableAction::make('reset_count')
                    ->label(__('admin.user_product_interactions.reset_count'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (UserProductInteraction $record): void {
                        $record->update(['count' => 1, 'last_interaction' => now()]);
                        Notification::make()
                            ->title(__('admin.user_product_interactions.count_reset_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.user_product_interactions.reset_count_heading'))
                    ->modalDescription(__('admin.user_product_interactions.reset_count_description')),
                TableAction::make('duplicate')
                    ->label(__('admin.user_product_interactions.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->action(function (UserProductInteraction $record): void {
                        $newRecord = $record->replicate();
                        $newRecord->count = 1;
                        $newRecord->first_interaction = now();
                        $newRecord->last_interaction = now();
                        $newRecord->save();

                        Notification::make()
                            ->title(__('admin.user_product_interactions.duplicated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.user_product_interactions.duplicate_heading'))
                    ->modalDescription(__('admin.user_product_interactions.duplicate_description')),
                TableAction::make('view_user')
                    ->label(__('admin.user_product_interactions.view_user'))
                    ->icon('heroicon-o-user')
                    ->color('gray')
                    ->url(fn($record) => UserResource::getUrl('view', ['record' => $record->user_id]))
                    ->openUrlInNewTab(),
                TableAction::make('view_product')
                    ->label(__('admin.user_product_interactions.view_product'))
                    ->icon('heroicon-o-cube')
                    ->color('gray')
                    ->url(fn($record) => ProductResource::getUrl('view', ['record' => $record->product_id]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('admin.common.delete_selected'))
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                    TableBulkAction::make('increment_all')
                        ->label(__('admin.user_product_interactions.increment_all'))
                        ->icon('heroicon-o-plus')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each(function (UserProductInteraction $record): void {
                                $record->incrementInteraction();
                            });
                            Notification::make()
                                ->title(__('admin.user_product_interactions.all_incremented_successfully'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.user_product_interactions.increment_all_heading'))
                        ->modalDescription(__('admin.user_product_interactions.increment_all_description')),
                    TableBulkAction::make('reset_all_counts')
                        ->label(__('admin.user_product_interactions.reset_all_counts'))
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each(function (UserProductInteraction $record): void {
                                $record->update(['count' => 1, 'last_interaction' => now()]);
                            });
                            Notification::make()
                                ->title(__('admin.user_product_interactions.all_counts_reset_successfully'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.user_product_interactions.reset_all_counts_heading'))
                        ->modalDescription(__('admin.user_product_interactions.reset_all_counts_description')),
                    TableBulkAction::make('mark_anonymous')
                        ->label(__('admin.user_product_interactions.mark_anonymous'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('gray')
                        ->action(function (Collection $records): void {
                            $records->each(function (UserProductInteraction $record): void {
                                $record->update(['is_anonymous' => true]);
                            });
                            Notification::make()
                                ->title(__('admin.user_product_interactions.marked_anonymous_successfully'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    TableBulkAction::make('mark_non_anonymous')
                        ->label(__('admin.user_product_interactions.mark_non_anonymous'))
                        ->icon('heroicon-o-eye')
                        ->color('gray')
                        ->action(function (Collection $records): void {
                            $records->each(function (UserProductInteraction $record): void {
                                $record->update(['is_anonymous' => false]);
                            });
                            Notification::make()
                                ->title(__('admin.user_product_interactions.marked_non_anonymous_successfully'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    TableBulkAction::make('export_selected')
                        ->label(__('admin.user_product_interactions.export_selected'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            // Export logic would go here
                            Notification::make()
                                ->title(__('admin.user_product_interactions.export_started'))
                                ->info()
                                ->send();
                        }),
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
