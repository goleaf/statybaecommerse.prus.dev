<?php

declare(strict_types=1);
declare(strict_types=1);
declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\VariantCombinationResource\Pages;
use App\Models\VariantCombination;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\HeaderAction;
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

/**
 * VariantCombinationResource
 *
 * Filament v4 resource for VariantCombination management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class VariantCombinationResource extends Resource
{
    protected static ?string $model = VariantCombination::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static UnitEnum|string|null $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 19;

    public static function getNavigationLabel(): string
    {
        return __('admin.variant_combinations.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.variant_combinations.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.variant_combinations.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.variant_combinations.basic_information'))
                    ->description(__('admin.variant_combinations.basic_information_description'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('product_id')
                                    ->label(__('admin.variant_combinations.product'))
                                    ->relationship('product', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $product = \App\Models\Product::find($state);
                                            if ($product && $product->attributes()->exists()) {
                                                $attributes = $product->attributes()->pluck('name', 'id')->toArray();
                                                $set('available_attributes', $attributes);
                                            }
                                        }
                                    }),
                                Toggle::make('is_available')
                                    ->label(__('admin.variant_combinations.is_available'))
                                    ->default(true)
                                    ->helperText(__('admin.variant_combinations.is_available_help')),
                            ]),
                    ]),
                Section::make(__('admin.variant_combinations.attribute_combinations'))
                    ->description(__('admin.variant_combinations.attribute_combinations_description'))
                    ->schema([
                        KeyValue::make('attribute_combinations')
                            ->label(__('admin.variant_combinations.attribute_combinations'))
                            ->keyLabel(__('admin.variant_combinations.attribute'))
                            ->valueLabel(__('admin.variant_combinations.value'))
                            ->columnSpanFull()
                            ->helperText(__('admin.variant_combinations.attribute_combinations_help'))
                            ->addActionLabel(__('admin.variant_combinations.add_attribute'))
                            ->deleteActionLabel(__('admin.variant_combinations.remove_attribute'))
                            ->reorderable()
                            ->collapsible(),
                    ]),
                Section::make(__('admin.variant_combinations.additional_information'))
                    ->description(__('admin.variant_combinations.additional_information_description'))
                    ->schema([
                        Placeholder::make('combination_hash')
                            ->label(__('admin.variant_combinations.combination_hash'))
                            ->content(fn ($record) => $record?->combination_hash ?? __('admin.variant_combinations.will_be_generated')),
                        Placeholder::make('formatted_combinations')
                            ->label(__('admin.variant_combinations.formatted_combinations'))
                            ->content(fn ($record) => $record?->formatted_combinations ?? __('admin.variant_combinations.no_combinations')),
                        Placeholder::make('is_valid_combination')
                            ->label(__('admin.variant_combinations.is_valid_combination'))
                            ->content(fn ($record) => $record?->is_valid_combination
                                ? __('admin.variant_combinations.valid_combination')
                                : __('admin.variant_combinations.invalid_combination')),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('admin.variant_combinations.id'))
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('product.name')
                    ->label(__('admin.variant_combinations.product'))
                    ->sortable()
                    ->searchable()
                    ->url(fn ($record) => route('filament.admin.resources.products.view', $record->product_id))
                    ->color('primary'),
                TextColumn::make('attribute_combinations')
                    ->label(__('admin.variant_combinations.attribute_combinations'))
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            return collect($state)->map(function ($value, $key) {
                                return $key.': '.$value;
                            })->join(', ');
                        }

                        return $state;
                    })
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    })
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('is_available')
                    ->label(__('admin.variant_combinations.is_available'))
                    ->formatStateUsing(fn ($state) => $state ? __('admin.variant_combinations.available') : __('admin.variant_combinations.unavailable'))
                    ->colors([
                        'success' => fn ($state) => $state,
                        'danger' => fn ($state) => ! $state,
                    ])
                    ->sortable(),
                TextColumn::make('combination_hash')
                    ->label(__('admin.variant_combinations.combination_hash'))
                    ->limit(20)
                    ->tooltip(fn ($record) => $record->combination_hash)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('formatted_combinations')
                    ->label(__('admin.variant_combinations.formatted_combinations'))
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->formatted_combinations)
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_valid_combination')
                    ->label(__('admin.variant_combinations.is_valid_combination'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('admin.variant_combinations.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.variant_combinations.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label(__('admin.variant_combinations.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_available')
                    ->label(__('admin.variant_combinations.is_available'))
                    ->placeholder(__('admin.variant_combinations.all_combinations'))
                    ->trueLabel(__('admin.variant_combinations.available_only'))
                    ->falseLabel(__('admin.variant_combinations.unavailable_only')),
                Filter::make('valid_combinations')
                    ->label(__('admin.variant_combinations.valid_combinations_only'))
                    ->query(fn (Builder $query): Builder => $query->whereHas('product', function (Builder $query) {
                        $query->whereHas('attributes');
                    }))
                    ->toggle(),
                Filter::make('recent_combinations')
                    ->label(__('admin.variant_combinations.recent_combinations'))
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(7)))
                    ->toggle(),
                Filter::make('has_attributes')
                    ->label(__('admin.variant_combinations.has_attributes'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('attribute_combinations'))
                    ->toggle(),
            ])
            ->headerActions([
                HeaderAction::make('generate_combinations')
                    ->label(__('admin.variant_combinations.generate_combinations'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('primary')
                    ->action(function () {
                        Notification::make()
                            ->title(__('admin.variant_combinations.combinations_generation_started'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.variant_combinations.generate_combinations'))
                    ->modalDescription(__('admin.variant_combinations.generate_combinations_description'))
                    ->modalSubmitActionLabel(__('admin.variant_combinations.generate')),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_availability')
                    ->label(fn (VariantCombination $record): string => $record->is_available ? __('admin.variant_combinations.make_unavailable') : __('admin.variant_combinations.make_available'))
                    ->icon(fn (VariantCombination $record): string => $record->is_available ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (VariantCombination $record): string => $record->is_available ? 'warning' : 'success')
                    ->action(function (VariantCombination $record): void {
                        $record->update(['is_available' => ! $record->is_available]);
                        Notification::make()
                            ->title($record->is_available ? __('admin.variant_combinations.made_available_successfully') : __('admin.variant_combinations.made_unavailable_successfully'))
                            ->success()
                            ->send();
                    }),
                Action::make('duplicate')
                    ->label(__('admin.variant_combinations.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->action(function (VariantCombination $record): void {
                        $newRecord = $record->replicate();
                        $newRecord->save();

                        Notification::make()
                            ->title(__('admin.variant_combinations.duplicated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('validate_combination')
                    ->label(__('admin.variant_combinations.validate_combination'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (VariantCombination $record): void {
                        $isValid = $record->is_valid_combination;
                        Notification::make()
                            ->title($isValid ? __('admin.variant_combinations.combination_is_valid') : __('admin.variant_combinations.combination_is_invalid'))
                            ->color($isValid ? 'success' : 'danger')
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('make_available')
                        ->label(__('admin.variant_combinations.make_available'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_available' => true]);
                            Notification::make()
                                ->title(__('admin.variant_combinations.made_available_successfully'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.variant_combinations.make_available'))
                        ->modalDescription(__('admin.variant_combinations.make_available_description'))
                        ->modalSubmitActionLabel(__('admin.variant_combinations.make_available')),
                    BulkAction::make('make_unavailable')
                        ->label(__('admin.variant_combinations.make_unavailable'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_available' => false]);
                            Notification::make()
                                ->title(__('admin.variant_combinations.made_unavailable_successfully'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.variant_combinations.make_unavailable'))
                        ->modalDescription(__('admin.variant_combinations.make_unavailable_description'))
                        ->modalSubmitActionLabel(__('admin.variant_combinations.make_unavailable')),
                    BulkAction::make('duplicate_selected')
                        ->label(__('admin.variant_combinations.duplicate_selected'))
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            $records->each(function (VariantCombination $record) {
                                $newRecord = $record->replicate();
                                $newRecord->save();
                            });

                            Notification::make()
                                ->title(__('admin.variant_combinations.duplicated_successfully'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.variant_combinations.duplicate_selected'))
                        ->modalDescription(__('admin.variant_combinations.duplicate_selected_description'))
                        ->modalSubmitActionLabel(__('admin.variant_combinations.duplicate')),
                    BulkAction::make('validate_selected')
                        ->label(__('admin.variant_combinations.validate_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('primary')
                        ->action(function (Collection $records): void {
                            $validCount = $records->filter(fn ($record) => $record->is_valid_combination)->count();
                            $totalCount = $records->count();

                            Notification::make()
                                ->title(__('admin.variant_combinations.validation_completed', [
                                    'valid' => $validCount,
                                    'total' => $totalCount,
                                ]))
                                ->color($validCount === $totalCount ? 'success' : 'warning')
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListVariantCombinations::route('/'),
            'create' => Pages\CreateVariantCombination::route('/create'),
            'view' => Pages\ViewVariantCombination::route('/{record}'),
            'edit' => Pages\EditVariantCombination::route('/{record}/edit'),
        ];
    }
}
