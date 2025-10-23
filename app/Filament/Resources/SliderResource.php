<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\Concerns\HasNav;

use App\Filament\Resources\SliderResource\Pages;
use App\Models\Slider;
use App\Support\Filament\SearchableComponentHelper;
use App\Support\Search\ContentLinkSearch;
use App\Support\Search\SearchResultPayload;
use BackedEnum;

use function collect;

use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
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

/**
 * SliderResource
 *
 * Filament v4 resource for Slider management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class SliderResource extends Resource
{
    use HasNav;

    protected static ?string $model = Slider::class;

    /**
     * Navigation icon for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * Navigation group for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationGroup = 'Content';

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

    public static function form(Form $form): Form
    {
        // Filament 4 expects returning the Form builder instance.
        return $form->schema([
            Section::make(__('sliders.basic_information'))
                ->schema([
                    Grid::make(2)
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
                        ->afterStateHydrated(function (SearchableInput $component, ?string $state, Set $set): void {
                            // Default the payload cache before the helper runs its hydration flow.
                            $set('button_url_payload', []);

                            SearchableComponentHelper::hydrate(
                                $component,
                                $state,
                                static function (?string $url): ?array {
                                    if (! is_string($url) || trim($url) === '') {
                                        return null;
                                    }

                                    $result = collect(ContentLinkSearch::results($url))
                                        ->first(static fn ($candidate): bool => $candidate->value() === $url);

                                    if ($result !== null) {
                                        $normalised = SearchResultPayload::hydrate($result);

                                        return [
                                            'value'   => $normalised['id'],
                                            'label'   => $normalised['label'],
                                            'payload' => $normalised['payload'],
                                        ];
                                    }

                                    return [
                                        'value'   => $url,
                                        'label'   => $url,
                                        'payload' => [
                                            'id'    => $url,
                                            'label' => $url,
                                            'type'  => 'custom',
                                        ],
                                    ];
                                },
                                static function (array $record) use ($set): array {
                                    $payload = $record['payload'] ?? [];

                                    $set('button_url_payload', $payload);

                                    return [
                                        'value'   => $record['value'] ?? null,
                                        'label'   => $record['label'] ?? null,
                                        'payload' => $payload,
                                    ];
                                },
                            );

                            // See docs/filament/searchable-inputs.md for helper expectations.
                        })
                        ->afterStateUpdated(function (SearchableInput $component, ?string $state, Set $set): void {
                            if (is_string($state) && trim($state) !== '') {
                                return;
                            }

                            SearchableComponentHelper::clear(
                                $component,
                                static function () use ($set): void {
                                    $set('button_url_payload', []);
                                },
                            );
                        })
                        ->columnSpanFull(),
                ]),
            Section::make(__('sliders.media'))
                ->schema([
                    FileUpload::make('image')
                        ->label(__('sliders.image'))
                        ->image()
                        ->directory('sliders')
                        ->disk('public')
                        ->columnSpanFull(),
                ]),
            Section::make(__('sliders.appearance'))
                ->schema([
                    Grid::make(2)
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
            Section::make(__('sliders.settings'))
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
        // Filament 4 expects returning the Table builder instance.
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
