<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SliderResource\Pages;
use App\Models\Slider;
use App\Support\Concerns\HasNav;
use App\Support\Filament\SearchableInputHelper;
use App\Support\Search\ContentLinkSearch;
use BackedEnum;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkActionGroup as TableBulkActionGroup;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction as TableDeleteBulkAction;
use Filament\Tables\Actions\EditAction as TableEditAction;
use Filament\Tables\Actions\ViewAction as TableViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

final class SliderResource extends Resource
{
    use HasNav;

    protected static ?string $model = Slider::class;

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * Keeps the navigation group compatible with Filament's enum-based sidebar metadata.
     */
    protected static UnitEnum|string|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'title';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     */
    public static function getNavigationLabel(): string
    {
        return __('sliders.navigation_label');
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('sliders.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('sliders.single');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            SchemaSection::make(__('sliders.basic_information'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            TextInput::make('title')
                                ->label(__('sliders.title'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('button_text')
                                ->label(__('sliders.button_text'))
                                ->maxLength(100),
                        ]),
                    Textarea::make('description')
                        ->label(__('sliders.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                    SearchableInput::make('button_url')
                        ->label(__('sliders.button_url'))
                        ->placeholder(__('sliders.button_url_placeholder'))
                        ->helperText(__('sliders.button_url_helper'))
                        ->searchUsing(fn (string $term): array => ContentLinkSearch::suggest($term))
                        ->maxLength(255)
                        ->searchUsing(fn (string $value): array => ContentLinkSearch::results($value))
                        ->dehydrateStateUsing(fn (?string $state): ?string => $state !== null && $state !== '' ? $state : null)
                        ->afterStateHydrated(function (SearchableInput $component, ?string $state): void {
                            // Hydrate through helper to stay aligned with docs/forms/SEARCHABLE_INPUT_METADATA.md guidance.
                            SearchableInputHelper::hydrate(
                                $component,
                                $state,
                                static fn (string $value): ?array => ['value' => $value, 'label' => $value],
                            );
                        })
                        ->afterStateUpdated(function (SearchableInput $component, ?string $state, callable $set): void {
                            if ($state !== null && $state !== '') {
                                return;
                            }

                            // Clear button URL metadata whenever the selection resets.
                            SearchableInputHelper::clear($component, $set, ['button_url' => null]);
                        })
                        ->columnSpanFull(),
                ]),
            SchemaSection::make(__('sliders.media'))
                ->schema([
                    FileUpload::make('image')
                        ->label(__('sliders.image'))
                        ->image()
                        ->directory('sliders')
                        ->disk('public')
                        ->columnSpanFull(),
                ]),
            SchemaSection::make(__('sliders.appearance'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            ColorPicker::make('background_color')
                                ->label(__('sliders.background_color'))
                                ->default('#ffffff'),
                            ColorPicker::make('text_color')
                                ->label(__('sliders.text_color'))
                                ->default('#000000'),
                        ]),
                    TextInput::make('sort_order')
                        ->label(__('sliders.sort_order'))
                        ->numeric()
                        ->default(0)
                        ->minValue(0),
                ]),
            SchemaSection::make(__('sliders.settings'))
                ->schema([
                    Toggle::make('is_active')
                        ->label(__('sliders.is_active'))
                        ->default(true),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     */
    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label(__('sliders.image'))
                    ->circular()
                    ->disk('public')
                    ->size(50),
                TextColumn::make('title')
                    ->label(__('sliders.title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('button_text')
                    ->label(__('sliders.button_text'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('sliders.sort_order'))
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('sliders.is_active'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('sliders.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('sliders.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('sliders.is_active')),
            ])
            ->actions([
                TableViewAction::make(),
                TableEditAction::make(),
                TableDeleteAction::make(),
            ])
            ->bulkActions([
                TableBulkActionGroup::make([
                    TableDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
    }

    /**
     * Handle getRelations functionality with proper error handling.
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Handle getPages functionality with proper error handling.
     */
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSliders::route('/'),
            'create' => Pages\CreateSlider::route('/create'),
            'view'   => Pages\ViewSlider::route('/{record}'),
            'edit'   => Pages\EditSlider::route('/{record}/edit'),
        ];
    }
}
