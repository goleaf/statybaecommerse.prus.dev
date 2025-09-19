<?php declare(strict_types=1);

namespace App\Filament\Resources\Countries;

use App\Enums\NavigationGroup;
use App\Filament\Resources\Countries\Pages\CreateCountry;
use App\Filament\Resources\Countries\Pages\EditCountry;
use App\Filament\Resources\Countries\Pages\ListCountries;
use App\Filament\Resources\Countries\Pages\ViewCountry;
use App\Filament\Resources\Countries\Schemas\CountryForm;
use App\Filament\Resources\Countries\Tables\CountriesTable;
use App\Models\Country;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BackedEnum;
use UnitEnum;

/**
 * CountryResource
 *
 * Filament v4 resource for Country management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    protected static string|UnitEnum|null $navigationGroup = 'Geography';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';

    public static function form(Schema $schema): Schema
    {
        return CountryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CountriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('countries.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return 'Locations';
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('countries.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('countries.single');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCountries::route('/'),
            'create' => CreateCountry::route('/create'),
            'view' => ViewCountry::route('/{record}'),
            'edit' => EditCountry::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
