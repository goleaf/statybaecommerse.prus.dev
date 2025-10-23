<?php

declare(strict_types=1);

namespace App\Filament\Resources\Sliders;
use App\Support\Concerns\HasNav;

use App\Enums\NavigationGroup;
use App\Filament\Resources\Sliders\Pages\CreateSlider;
use App\Filament\Resources\Sliders\Pages\EditSlider;
use App\Filament\Resources\Sliders\Pages\ListSliders;
use App\Filament\Resources\Sliders\Schemas\SliderForm;
use App\Filament\Resources\Sliders\Tables\SlidersTable;
use App\Models\Slider;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;
use Filament\Schemas\Schema;

final class SliderResource extends Resource
{
    use HasNav;

    protected static ?string $model = Slider::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Content;

    /**
     * Navigation group for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationGroup = NavigationGroup::Content;

    /**
     * @var \UnitEnum|string|null
     */
    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $form): Schema
    {
        // Ensure compatibility with the Schema-based form builder introduced in Filament v4.
        return SliderForm::configure($form);
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