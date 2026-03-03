<?php

declare(strict_types=1);

namespace App\Filament\Resources\Brochures;

use App\Enums\NavigationGroup;
use App\Filament\Resources\Brochures\Pages\CreateBrochure;
use App\Filament\Resources\Brochures\Pages\EditBrochure;
use App\Filament\Resources\Brochures\Pages\ListBrochures;
use App\Filament\Resources\Brochures\Schemas\BrochureForm;
use App\Filament\Resources\Brochures\Tables\BrochuresTable;
use App\Models\Brochure;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

final class BrochureResource extends Resource
{
    protected static ?string $model = Brochure::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-arrow-down';

    protected static ?int $navigationSort = 30;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return NavigationGroup::Content->label();
    }

    public static function getModelLabel(): string
    {
        return __('admin.brochures.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.brochures.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.brochures.navigation_label');
    }

    public static function form(Schema $schema): Schema
    {
        return BrochureForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BrochuresTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListBrochures::route('/'),
            'create' => CreateBrochure::route('/create'),
            'edit'   => EditBrochure::route('/{record}/edit'),
        ];
    }
}
