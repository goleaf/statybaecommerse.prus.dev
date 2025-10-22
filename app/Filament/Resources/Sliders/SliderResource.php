<?php

declare(strict_types=1);

namespace App\Filament\Resources\Sliders;

use App\Enums\NavigationGroup;
use App\Filament\Resources\Sliders\Pages\CreateSlider;
use App\Filament\Resources\Sliders\Pages\EditSlider;
use App\Filament\Resources\Sliders\Pages\ListSliders;
use App\Filament\Resources\Sliders\Schemas\SliderForm;
use App\Filament\Resources\Sliders\Tables\SlidersTable;
use App\Models\Slider;
use BackedEnum;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

final class SliderResource extends Resource
{
    protected static ?string $model = Slider::class;

    /** @var string|BackedEnum|null */
    protected static $navigationIcon = Heroicon::OutlinedRectangleStack;

    /** @var UnitEnum|string|null */
    protected static $navigationGroup = NavigationGroup::Content;

    /**
     * @var UnitEnum|string|null
     */
    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return SliderForm::configure($form);
    }

    public static function table(Table $table): Table
    {
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
