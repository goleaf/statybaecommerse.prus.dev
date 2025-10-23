<?php

declare(strict_types=1);

namespace App\Filament\Resources\FeatureFlags;
use App\Support\Concerns\HasNav;

use App\Filament\Resources\FeatureFlags\Pages\CreateFeatureFlag;
use App\Filament\Resources\FeatureFlags\Pages\EditFeatureFlag;
use App\Filament\Resources\FeatureFlags\Pages\ListFeatureFlags;
use App\Filament\Resources\FeatureFlags\Schemas\FeatureFlagForm;
use App\Filament\Resources\FeatureFlags\Tables\FeatureFlagsTable;
use App\Models\FeatureFlag;
use BackedEnum;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FeatureFlagResource extends Resource
{
    use HasNav;

    protected static ?string $model = FeatureFlag::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Form $form): Form|array
    {
        return FeatureFlagForm::configure($form);
    }

    public static function table(Table $table): Table|array
    {
        return FeatureFlagsTable::configure($table);
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
            'index'  => ListFeatureFlags::route('/'),
            'create' => CreateFeatureFlag::route('/create'),
            'edit'   => EditFeatureFlag::route('/{record}/edit'),
        ];
    }
}
