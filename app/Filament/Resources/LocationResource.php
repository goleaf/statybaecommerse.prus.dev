<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\LocationResource\Pages;
use App\Models\Location;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

final class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('translations.location_information'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('translations.name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->label(__('translations.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(Location::class, 'slug', ignoreRecord: true),
                        Forms\Components\Textarea::make('description')
                            ->label(__('translations.description'))
                            ->maxLength(1000)
                            ->rows(3),
                        Forms\Components\TextInput::make('address')
                            ->label(__('translations.address'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('city')
                            ->label(__('translations.city'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('state')
                            ->label(__('translations.state'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('postal_code')
                            ->label(__('translations.postal_code'))
                            ->maxLength(20),
                        Forms\Components\Select::make('country_id')
                            ->label(__('translations.country'))
                            ->relationship('country', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('phone')
                            ->label(__('translations.phone'))
                            ->tel()
                            ->maxLength(50),
                        Forms\Components\TextInput::make('email')
                            ->label(__('translations.email'))
                            ->email()
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_default')
                            ->label(__('translations.is_default'))
                            ->default(false),
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('translations.active'))
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('translations.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('city')
                    ->label(__('translations.city'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('country.name')
                    ->label(__('translations.country'))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('translations.phone'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('translations.email'))
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_default')
                    ->label(__('translations.is_default'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('translations.active'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('translations.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->label(__('translations.active_only'))
                    ->query(fn(Builder $query): Builder => $query->where('is_active', true)),
                Tables\Filters\Filter::make('default')
                    ->label(__('translations.default_only'))
                    ->query(fn(Builder $query): Builder => $query->where('is_default', true)),
                Tables\Filters\SelectFilter::make('country_id')
                    ->label(__('translations.country'))
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload(),
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
            ->defaultSort('name', 'asc');
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
            'index' => Pages\ListLocations::route('/'),
            'create' => Pages\CreateLocation::route('/create'),
            'view' => Pages\ViewLocation::route('/{record}'),
            'edit' => Pages\EditLocation::route('/{record}/edit'),
        ];
    }
}
