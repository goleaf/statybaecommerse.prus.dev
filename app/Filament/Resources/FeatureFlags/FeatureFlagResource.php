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
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use BackedEnum;

class FeatureFlagResource extends Resource
{
    use HasNav;

    protected static ?string $model = FeatureFlag::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        // Configure the Filament resource form schema using the v4 Schema API.
        return FeatureFlagForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        // Configure the Filament table definition for the resource.
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