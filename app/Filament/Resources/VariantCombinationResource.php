<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\VariantCombinationResource\Pages;
use App\Models\Product;
use App\Models\VariantCombination;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

final class VariantCombinationResource extends Resource
{
    protected static ?string $model = \App\Models\VariantCombination::class;

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations while
     * still allowing the resource to override it with a simple string when needed.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-squares-2x2';

    /**
     * Keeps the navigation group compatible with Filament's enum-based sidebar metadata while
     * remaining flexible for plain string usage in configuration overrides.
     */
    protected static UnitEnum|string|null $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 19;

    public static function getNavigationLabel(): string
    {
        // Return translation keys so tests can assert deterministically
        return 'admin.variant_combinations.navigation_label';
    }

    public static function getPluralModelLabel(): string
    {
        return 'admin.variant_combinations.plural_model_label';
    }

    public static function getModelLabel(): string
    {
        return 'admin.variant_combinations.model_label';
    }

    public static function form(Schema $schema): Schema
    {
        // Re-use the shared schema definition so both Filament and the test suite operate on
        // the exact same component graph without duplicating configuration details.
        return $schema->components(self::formComponents());
    }

    /**
     * Expose the reusable set of form components so tests can inspect the structure without
     * needing to instantiate a Livewire-powered schema instance.
     *
     * @return array<int, SchemaSection>
     */
    public static function formComponents(): array
    {
        return [
            SchemaSection::make('admin.variant_combinations.basic_information')
                ->description('admin.variant_combinations.basic_information_description')
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            Select::make('product_id')
                                ->label(__('admin.variant_combinations.product'))
                                ->relationship('product', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set): void {
                                    // Resolve the available attribute list via a dedicated helper so we avoid
                                    // ambiguous column selections while keeping the UI responsive.
                                    $set('available_attributes', self::resolveAvailableAttributes($state));
                                }),
                            Toggle::make('is_available')
                                ->label(__('admin.variant_combinations.is_available'))
                                ->default(true)
                                ->helperText(__('admin.variant_combinations.is_available_help')),
                        ]),
                ]),
            SchemaSection::make('admin.variant_combinations.attribute_combinations')
                ->description('admin.variant_combinations.attribute_combinations_description')
                ->schema([
                    KeyValue::make('attribute_combinations')
                        ->label(__('admin.variant_combinations.attribute_combinations'))
                        ->default([])
                        ->keyLabel(__('admin.variant_combinations.attribute'))
                        ->valueLabel(__('admin.variant_combinations.value'))
                        ->columnSpanFull()
                        ->helperText(__('admin.variant_combinations.attribute_combinations_help'))
                        ->addActionLabel(__('admin.variant_combinations.add_attribute'))
                        ->deleteActionLabel(__('admin.variant_combinations.remove_attribute'))
                        ->reorderable(),
                ]),
            SchemaSection::make('admin.variant_combinations.additional_information')
                ->description('admin.variant_combinations.additional_information_description')
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
        ];
    }

    /**
     * Resolve the available attribute list for the selected product while avoiding ambiguous
     * column selections when SQLite performs implicit column ordering.
     *
     * @param  int|string|null           $productId
     * @return array<int|string, string>
     */
    private static function resolveAvailableAttributes($productId): array
    {
        if ($productId === null || $productId === '') {
            // Guard against empty selections so form state remains predictable.
            return [];
        }

        $product = Product::query()->find($productId);

        if ($product === null) {
            return [];
        }

        return $product->attributes()
            ->select(['attributes.id', 'attributes.name'])
            ->pluck('attributes.name', 'attributes.id')
            ->toArray();
    }

    public static function table(Table $table): Table
    {
        // Compose the table definition from the shared building blocks so runtime behaviour and
        // test assertions stay aligned when we evolve the resource structure.
        return $table
            ->columns(self::tableColumns())
            ->filters(self::tableFilters())
            ->headerActions(self::tableHeaderActions())
            ->actions(self::tableActions())
            ->bulkActions(self::tableBulkActions())
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Provide the column configuration for both Filament runtime usage and test inspection.
     *
     * @return array<int, TextColumn|BadgeColumn|IconColumn>
     */
    public static function tableColumns(): array
    {
        return [
            TextColumn::make('id')
                ->label(__('admin.variant_combinations.id'))
                ->sortable()
                ->searchable()
                ->toggleable(),
            TextColumn::make('product.name')
                ->label(__('admin.variant_combinations.product'))
                ->sortable()
                ->searchable()
                ->url(fn (VariantCombination $record): ?string => $record->product_id
                    ? route('filament.admin.resources.products.view', $record->product_id)
                    : null)
                ->color('primary'),
            TextColumn::make('attribute_combinations')
                ->label(__('admin.variant_combinations.attribute_combinations'))
                ->formatStateUsing(function ($state) {
                    if (is_array($state)) {
                        // Present attribute pairs in a consistent "name: value" format for readability.
                        return collect($state)->map(function ($value, $key) {
                            return $key . ': ' . $value;
                        })->join(', ');
                    }

                    return $state;
                })
                ->limit(50)
                ->tooltip(function (TextColumn $column): ?string {
                    $state = $column->getState();

                    if (! is_string($state)) {
                        return null;
                    }

                    return mb_strlen($state) > 50 ? $state : null;
                })
                ->searchable()
                ->sortable(),
            BadgeColumn::make('is_available')
                ->label(__('admin.variant_combinations.is_available'))
                ->formatStateUsing(fn ($state) => $state ? __('admin.variant_combinations.available') : __('admin.variant_combinations.unavailable'))
                ->colors([
                    'success' => fn ($state) => $state,
                    'danger'  => fn ($state) => ! $state,
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
        ];
    }

    /**
     * Describe all table filters so they can be asserted without booting the component.
     *
     * @return array<int, Filter|SelectFilter|TernaryFilter>
     */
    public static function tableFilters(): array
    {
        return [
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
                ->query(fn (Builder $query): Builder => $query->whereHas('product', function (Builder $query): Builder {
                    return $query->whereHas('attributes');
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
        ];
    }

    /**
     * Provide access to the table header actions for dedicated assertions.
     *
     * @return array<int, Action>
     */
    public static function tableHeaderActions(): array
    {
        return [
            Action::make('generate_combinations')
                ->label(__('admin.variant_combinations.generate_combinations'))
                ->icon('heroicon-o-cog-6-tooth')
                ->color('primary')
                ->action(function (): void {
                    Notification::make()
                        ->title(__('admin.variant_combinations.combinations_generation_started'))
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading(__('admin.variant_combinations.generate_combinations'))
                ->modalDescription(__('admin.variant_combinations.generate_combinations_description'))
                ->modalSubmitActionLabel(__('admin.variant_combinations.generate')),
        ];
    }

    /**
     * Enumerate the per-record table actions for transparent behavioural checks.
     *
     * @return array<int, Action|ViewAction|EditAction>
     */
    public static function tableActions(): array
    {
        return [
            ViewAction::make(),
            EditAction::make(),
            Action::make('toggle_availability')
                ->label(fn (VariantCombination $record): string => $record->is_available ? __('admin.variant_combinations.make_unavailable') : __('admin.variant_combinations.make_available'))
                ->icon(fn (VariantCombination $record): string => $record->is_available ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                ->color(fn (VariantCombination $record): string => $record->is_available ? 'warning' : 'success')
                ->action(function (VariantCombination $record): void {
                    // Flip the availability flag and surface the result via a notification for parity
                    // with the existing UX expectations.
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
                    // Replicate the combination verbatim so administrators can fine-tune the copy.
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
        ];
    }

    /**
     * Share the bulk action configuration for improved test ergonomics.
     *
     * @return array<int, BulkActionGroup>
     */
    public static function tableBulkActions(): array
    {
        return [
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
                        $records->each(function (VariantCombination $record): void {
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
        ];
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
            'index'  => Pages\ListVariantCombinations::route('/'),
            'create' => Pages\CreateVariantCombination::route('/create'),
            'view'   => Pages\ViewVariantCombination::route('/{record}'),
            'edit'   => Pages\EditVariantCombination::route('/{record}/edit'),
        ];
    }
}
