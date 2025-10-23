<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\Concerns\HasNav;

use App\Filament\Resources\SliderTranslationResource\Pages;
use App\Models\Slider;
use BackedEnum;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

/**
 * SliderTranslationResource
 *
 * Filament v4 resource for SliderTranslation management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class SliderTranslationResource extends Resource
{
    use HasNav;

    protected static ?string $model = SliderTranslation::class;

    /**
     * @var string|BackedEnum|null Icon used for the navigation menu.
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

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('admin.slider_translations.basic_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Select::make('slider_id')
                                ->label(__('admin.slider_translations.slider'))
                                ->options(fn (): array => Slider::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all())
                                ->required()
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
        return $table
            ->columns([
                TextColumn::make('slider.name')
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

                        return mb_strlen($state) > 50 ? $state : null;
                    }),
                TextColumn::make('description')
                    ->label(__('admin.slider_translations.description'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = (string) $column->getState();

                        return mb_strlen($state) > 50 ? $state : null;
                    }),
                TextColumn::make('button_text')
                    ->label(__('admin.slider_translations.button_text'))
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = (string) $column->getState();

                        return mb_strlen($state) > 30 ? $state : null;
                    }),
                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('slider_id')
                    ->label(__('admin.slider_translations.slider'))
                    ->options(fn (): array => Slider::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
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
