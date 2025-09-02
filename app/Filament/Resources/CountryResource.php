<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CountryResource\Pages;
use App\Models\Country;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use UnitEnum;

final class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-globe-europe-africa';

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make('Country Information')
                    ->schema([
                        Forms\Components\TextInput::make('cca2')
                            ->label('Country Code (2-letter)')
                            ->required()
                            ->maxLength(2)
                            ->unique(Country::class, 'cca2', ignoreRecord: true),
                        Forms\Components\TextInput::make('cca3')
                            ->label('Country Code (3-letter)')
                            ->required()
                            ->maxLength(3)
                            ->unique(Country::class, 'cca3', ignoreRecord: true),
                        Forms\Components\TextInput::make('phone_calling_code')
                            ->label('Phone Code')
                            ->required()
                            ->maxLength(10),
                        Forms\Components\TextInput::make('flag')
                            ->label('Flag Emoji')
                            ->maxLength(10),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Geographic Information')
                    ->schema([
                        Forms\Components\Select::make('region')
                            ->options([
                                'Europe' => 'Europe',
                                'Asia' => 'Asia',
                                'Africa' => 'Africa',
                                'Americas' => 'Americas',
                                'Oceania' => 'Oceania',
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
                            ->placeholder('Add currency codes (e.g., EUR, USD)'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Translations')
                    ->schema([
                        Forms\Components\Repeater::make('translations')
                            ->relationship('translations')
                            ->schema([
                                Forms\Components\Select::make('locale')
                                    ->options([
                                        'en' => 'English',
                                        'lt' => 'Lithuanian',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('name_official')
                                    ->label('Official Name')
                                    ->maxLength(255),
                            ])
                            ->columns(3)
                            ->defaultItems(2)
                            ->addActionLabel('Add Translation')
                            ->reorderableWithButtons()
                            ->collapsible(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('flag')
                    ->label('Flag')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('cca2')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('translated_name')
                    ->label('Name')
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
                    ->label('Phone Code')
                    ->prefix('+')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('currencies')
                    ->badge()
                    ->separator(',')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('translations_count')
                    ->counts('translations')
                    ->label('Translations')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('region')
                    ->options([
                        'Europe' => 'Europe',
                        'Asia' => 'Asia',
                        'Africa' => 'Africa',
                        'Americas' => 'Americas',
                        'Oceania' => 'Oceania',
                    ]),
                Tables\Filters\Filter::make('has_translations')
                    ->query(fn(Builder $query): Builder => $query->has('translations'))
                    ->label('Has Translations'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
}
