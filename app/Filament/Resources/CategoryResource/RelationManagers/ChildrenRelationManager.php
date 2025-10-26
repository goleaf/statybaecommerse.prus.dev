<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use App\Filament\RelationManagers\Support\BaseRelationManager;
// Bring in Closure so inline annotations about schema arrays remain precise for tooling.
use Closure;
use Filament\Actions\Action;
// Import the repeater component explicitly so the quick-edit modal schema renders without runtime errors.
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
// Include action types to keep schema annotations compatible with Filament's action components.
use Filament\Forms\Components\Toggle;
// Support grouped actions within quick-edit schemas for completeness.
use Filament\Forms\Set;
// Reference the base component type for quick-edit schema hints.
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
// Allow schema annotations to include pre-rendered HTML segments when needed.
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
// Bring in the custom repeater action to keep inline editing for child categories functional.
use Zvizvi\RelationManagerRepeater\Tables\RelationManagerRepeaterAction;

final class ChildrenRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'children';

    protected static ?string $title = 'Subcategories';

    protected static ?string $modelLabel = 'Subcategory';

    protected static ?string $pluralModelLabel = 'Subcategories';

    public function form(Schema $schema): Schema
    {
        // Delegate to a reusable builder so modal quick-edit schemas stay in sync with the full form.
        return $schema->components($this->getFormComponents());
    }

    /**
     * Expose the components so both the primary form and the quick-edit modal share the exact same layout.
     *
     * @return array<int, SchemaSection>
     */
    private function getFormComponents(): array
    {
        return [
            SchemaSection::make(__('categories.basic_information'))
                ->components([
                    SchemaGrid::make(2)
                        ->components([
                            TextInput::make('name')
                                ->label(__('categories.name'))
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (string $operation, mixed $state, Set $set): void {
                                    // Guard the slug helper against null or array states while still normalising the preview slug.
                                    if ($operation !== 'create' || ! is_string($state)) {
                                        return;
                                    }

                                    $set('slug', Str::slug($state));
                                }),
                            TextInput::make('slug')
                                ->label(__('categories.slug'))
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    Textarea::make('description')
                        ->label(__('categories.description'))
                        ->rows(3)
                        ->columnSpanFull(),
                    Textarea::make('short_description')
                        ->label(__('categories.short_description'))
                        ->rows(2)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            SchemaSection::make(__('categories.appearance'))
                ->components([
                    SchemaGrid::make(3)
                        ->components([
                            ColorPicker::make('color')
                                ->label(__('categories.color'))
                                ->hex(),
                            TextInput::make('sort_order')
                                ->label(__('categories.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                            TextInput::make('product_limit')
                                ->label(__('categories.product_limit'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->helperText(__('categories.product_limit_help')),
                        ]),
                ]),
            SchemaSection::make(__('categories.settings'))
                ->components([
                    SchemaGrid::make(3)
                        ->components([
                            Toggle::make('is_active')
                                ->label(__('categories.is_active'))
                                ->default(true),
                            Toggle::make('is_visible')
                                ->label(__('categories.is_visible'))
                                ->default(true),
                            Toggle::make('is_enabled')
                                ->label(__('categories.is_enabled'))
                                ->default(true),
                        ]),
                    SchemaGrid::make(2)
                        ->components([
                            Toggle::make('is_featured')
                                ->label(__('categories.is_featured')),
                            Toggle::make('show_in_menu')
                                ->label(__('categories.show_in_menu'))
                                ->default(true),
                        ]),
                ]),
        ];
    }

    public function table(Table $table): Table
    {
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('categories.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('slug')
                    ->label(__('categories.slug'))
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ColorColumn::make('color')
                    ->label(__('categories.color'))
                    ->toggleable(),
                TextColumn::make('products_count')
                    ->label(__('categories.products_count'))
                    ->counts('products')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                TextColumn::make('children_count')
                    ->label(__('categories.subcategories_count'))
                    ->counts('children')
                    ->badge()
                    ->color('info'),
                TextColumn::make('sort_order')
                    ->label(__('categories.sort_order'))
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                IconColumn::make('is_active')
                    ->label(__('categories.is_active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('is_visible')
                    ->label(__('categories.is_visible'))
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('is_enabled')
                    ->label(__('categories.is_enabled'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('is_featured')
                    ->label(__('categories.is_featured'))
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                TextColumn::make('created_at')
                    ->label(__('categories.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->trueLabel(__('categories.active_only'))
                    ->falseLabel(__('categories.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_visible')
                    ->trueLabel(__('categories.visible_only'))
                    ->falseLabel(__('categories.hidden_only'))
                    ->native(false),
                TernaryFilter::make('is_enabled')
                    ->trueLabel(__('categories.enabled_only'))
                    ->falseLabel(__('categories.disabled_only'))
                    ->native(false),
                TernaryFilter::make('is_featured')
                    ->trueLabel(__('categories.featured_only'))
                    ->falseLabel(__('categories.not_featured'))
                    ->native(false),
            ])
            ->headerActions([
                RelationManagerRepeaterAction::make()
                    ->label('Quick edit ' . $this->getPluralModelLabel())
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Edit ' . $this->getPluralModelLabel())
                    ->modalWidth('5xl')
                    ->configureRepeater(function (Repeater $repeater): Repeater {
                        // Provide a quick-edit modal for managing records inline.
                        /** @var array<int, Component|Action|ActionGroup|Htmlable|string|Closure> $quickEditSchema */
                        $quickEditSchema = $this->getQuickEditSchema();

                        /** @phpstan-ignore-next-line The repeater package consumes full component arrays despite the helper signature. */
                        return $repeater->schema($quickEditSchema);
                    }),
                CreateAction::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                // Add bulk actions if needed
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }

    /**
     * Keep the quick-edit modal consistent with the primary form layout.
     *
     * @return array<int, Component|Action|ActionGroup|Closure|Htmlable|string>
     */
    protected function getQuickEditSchema(): array
    {
        // Share the same component list so administrators see identical validation and helper text in both contexts.
        return $this->getFormComponents();
    }
}
