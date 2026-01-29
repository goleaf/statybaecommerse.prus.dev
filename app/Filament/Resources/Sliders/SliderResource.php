<?php

declare(strict_types=1);

namespace App\Filament\Resources\Sliders;

use App\Enums\NavigationGroup;
use App\Filament\Resources\Sliders\Pages\CreateSlider;
use App\Filament\Resources\Sliders\Pages\EditSlider;
use App\Filament\Resources\Sliders\Pages\ListSliders;
use App\Filament\Resources\Sliders\RelationManagers;
use App\Filament\Resources\Sliders\Schemas\SliderForm;
use App\Filament\Resources\Sliders\Tables\SlidersTable;
use App\Models\Slider;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class SliderResource extends Resource
{
    protected static ?string $model = Slider::class;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::Content->label();
    }

    public static function getModelLabel(): string
    {
        return __('translations.slide');
    }

    public static function getPluralModelLabel(): string
    {
        return __('translations.slider_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('translations.slider_management');
    }

    public static function form(Schema $schema): Schema
    {
        return SliderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SlidersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TranslationsRelationManager::class,
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
