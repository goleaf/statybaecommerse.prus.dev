<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CountryResource\Pages;
use App\Models\Country;
use App\Services\MultiLanguageTabService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Actions as Actions;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\TabLayoutPlugin\Components\Tabs\Tab as TabLayoutTab;
use SolutionForest\TabLayoutPlugin\Components\Tabs;
use BackedEnum;
use UnitEnum;

final class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-globe-europe-africa';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.countries');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.countries.sections.country_information'))
                    ->components([
                        Forms\Components\TextInput::make('cca2')
                            ->label(__('admin.countries.fields.cca2'))
                            ->required()
                            ->maxLength(2)
                            ->unique(Country::class, 'cca2', ignoreRecord: true),
                        Forms\Components\TextInput::make('cca3')
                            ->label(__('admin.countries.fields.cca3'))
                            ->required()
                            ->maxLength(3)
                            ->unique(Country::class, 'cca3', ignoreRecord: true),
                        Forms\Components\TextInput::make('phone_calling_code')
                            ->label(__('admin.countries.fields.phone_calling_code'))
                            ->required()
                            ->maxLength(10),
                        Forms\Components\TextInput::make('flag')
                            ->label(__('admin.countries.fields.flag'))
                            ->maxLength(10),
                    ])
                    ->columns(2),
                Section::make(__('admin.countries.sections.geographic_information'))
                    ->components([
                        Forms\Components\Select::make('region')
                            ->options([
                                'Europe' => __('admin.countries.regions.europe'),
                                'Asia' => __('admin.countries.regions.asia'),
                                'Africa' => __('admin.countries.regions.africa'),
                                'Americas' => __('admin.countries.regions.americas'),
                                'Oceania' => __('admin.countries.regions.oceania'),
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('subregion')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->step(0.000001),
                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->step(0.000001),
                        Forms\Components\TagsInput::make('currencies')
                            ->placeholder(__('admin.countries.placeholders.add_currency_codes')),
                    ])
                    ->columns(2),
                // Multilanguage Tabs for Country Names
                Tabs::make('country_translations')
                    ->tabs(
                        MultiLanguageTabService::createSectionedTabs([
                            'country_information' => [
                                'name' => [
                                    'type' => 'text',
                                    'label' => __('translations.country_name'),
                                    'required' => true,
                                    'maxLength' => 255,
                                ],
                                'name_official' => [
                                    'type' => 'text',
                                    'label' => __('translations.country_official_name'),
                                    'maxLength' => 255,
                                    'placeholder' => __('translations.official_name_help'),
                                ],
                            ],
                        ])
                    )
                    ->activeTab(MultiLanguageTabService::getDefaultActiveTab())
                    ->persistTabInQueryString('country_tab')
                    ->contained(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('flag')
                    ->label(__('admin.countries.fields.flag'))
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('cca2')
                    ->label(__('admin.countries.fields.code'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('translated_name')
                    ->label(__('admin.countries.fields.name'))
                    ->searchable(['name'])
                    ->sortable()
                    ->getStateUsing(fn(Country $record): string => $record->trans('name') ?? $record->cca2),
                Tables\Columns\TextColumn::make('region')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Europe' => 'success',
                        'Asia' => 'warning',
                        'Africa' => 'danger',
                        'Americas' => 'info',
                        'Oceania' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('subregion')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone_calling_code')
                    ->label(__('admin.countries.fields.phone_calling_code'))
                    ->prefix('+')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('currencies')
                    ->badge()
                    ->separator(',')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('translations_count')
                    ->counts('translations')
                    ->label(__('admin.countries.fields.translations'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('region')
                    ->options([
                        'Europe' => __('admin.countries.regions.europe'),
                        'Asia' => __('admin.countries.regions.asia'),
                        'Africa' => __('admin.countries.regions.africa'),
                        'Americas' => __('admin.countries.regions.americas'),
                        'Oceania' => __('admin.countries.regions.oceania'),
                    ]),
                Tables\Filters\Filter::make('has_translations')
                    ->query(fn(Builder $query): Builder => $query->has('translations'))
                    ->label(__('admin.countries.filters.has_translations')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('cca2');
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
            'index' => Pages\ListCountries::route('/'),
            'create' => Pages\CreateCountry::route('/create'),
            'view' => Pages\ViewCountry::route('/{record}'),
            'edit' => Pages\EditCountry::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.system');
    }
}
