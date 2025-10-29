<?php

declare(strict_types=1);

namespace App\Filament\Resources\Sliders;

use App\Enums\NavigationGroup;
use App\Filament\Resources\Sliders\Pages\CreateSlider;
use App\Filament\Resources\Sliders\Pages\EditSlider;
use App\Filament\Resources\Sliders\Pages\ListSliders;
use App\Filament\Resources\Sliders\Schemas\SliderForm;
use App\Filament\Resources\Sliders\Tables\SlidersTable;
use App\Support\Concerns\HasNav;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

final class SliderResource extends Resource
{
    use HasNav;

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations while exposing a stable string for tests.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * Keeps the navigation group compatible with Filament's enum-based sidebar metadata.
     */
    protected static UnitEnum|string|null $navigationGroup = NavigationGroup::Content->value;

    /**
     * @var int|null provides explicit typing for the navigation sorting priority.
     */
    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return SliderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return SlidersTable::configure($table);
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
            'index'  => ListSliders::route('/'),
            'create' => CreateSlider::route('/create'),
            'edit'   => EditSlider::route('/{record}/edit'),
        ];
    }
}
