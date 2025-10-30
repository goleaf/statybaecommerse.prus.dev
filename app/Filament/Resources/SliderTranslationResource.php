<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SliderTranslationResource\Pages;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

final class SliderTranslationResource extends Resource
{
    protected static ?string $model = \App\Models\SliderTranslation::class;

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'title';

    /**
     * Keeps the navigation group compatible with Filament's enum-based sidebar metadata.
     */
    protected static UnitEnum|string|null $navigationGroup = 'Content Management';

    public static function getNavigationLabel(): string
    {
        return __('admin.slider_translations.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.slider_translations.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.slider_translations.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            SchemaSection::make(__('admin.slider_translations.basic_information'))
                ->components([
                    SchemaGrid::make(2)
                        ->components([
                            Select::make('slider_id')
                                ->label(__('admin.slider_translations.slider'))
                                // Target the slider title column so dropdowns expose the human-readable label.
                                ->relationship('slider', 'title')
                                ->required()
                                ->preload()
                                ->searchable(),
                            Select::make('locale')
                                ->label(__('admin.slider_translations.locale'))
                                ->options([
                                    'en' => 'English',
                                    'lt' => 'Lithuanian',
                                    'de' => 'German',
                                    'fr' => 'French',
                                    'es' => 'Spanish',
                                ])
                                ->required()
                                ->default('lt'),
                        ]),
                    TextInput::make('title')
                        ->label(__('admin.slider_translations.title'))
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->label(__('admin.slider_translations.description'))
                        ->maxLength(1000)
                        ->rows(3),
                    TextInput::make('button_text')
                        ->label(__('admin.slider_translations.button_text'))
                        ->maxLength(255),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                TextColumn::make('slider.title')
                    ->label(__('admin.slider_translations.slider'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('locale')
                    ->label(__('admin.slider_translations.locale'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'en'    => 'success',
                        'lt'    => 'info',
                        'de'    => 'warning',
                        'fr'    => 'danger',
                        'es'    => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('title')
                    ->label(__('admin.slider_translations.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = (string) $column->getState();

                        if (! is_string($state)) {
                            return null;
                        }

                        return Str::length($state) > 50 ? $state : null;
                    }),
                TextColumn::make('description')
                    ->label(__('admin.slider_translations.description'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = (string) $column->getState();

                        if (! is_string($state)) {
                            return null;
                        }

                        return Str::length($state) > 50 ? $state : null;
                    }),
                TextColumn::make('button_text')
                    ->label(__('admin.slider_translations.button_text'))
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = (string) $column->getState();

                        if (! is_string($state)) {
                            return null;
                        }

                        return Str::length($state) > 30 ? $state : null;
                    }),
                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('slider')
                    ->label(__('admin.slider_translations.slider'))
                    // Reuse the title column here so filter dropdown values mirror the form relationship.
                    ->relationship('slider', 'title')
                    ->preload()
                    ->searchable(),
                SelectFilter::make('locale')
                    ->label(__('admin.slider_translations.locale'))
                    ->options([
                        'en' => 'English',
                        'lt' => 'Lithuanian',
                        'de' => 'German',
                        'fr' => 'French',
                        'es' => 'Spanish',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('locale');
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
            'index'  => Pages\ListSliderTranslations::route('/'),
            'create' => Pages\CreateSliderTranslation::route('/create'),
            'view'   => Pages\ViewSliderTranslation::route('/{record}'),
            'edit'   => Pages\EditSliderTranslation::route('/{record}/edit'),
        ];
    }
}
