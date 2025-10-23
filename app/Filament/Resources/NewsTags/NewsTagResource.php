<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsTags;
use App\Support\Concerns\HasNav;

use App\Filament\Resources\NewsTags\Pages\CreateNewsTag;
use App\Filament\Resources\NewsTags\Pages\EditNewsTag;
use App\Filament\Resources\NewsTags\Pages\ListNewsTags;
use App\Filament\Resources\NewsTags\Pages\ViewNewsTag;
use App\Filament\Resources\NewsTags\Schemas\NewsTagForm;
use App\Filament\Resources\NewsTags\Tables\NewsTagsTable;
use App\Models\NewsTag;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class NewsTagResource extends Resource
{
    use HasNav;

    protected static ?string $model = NewsTag::class;

    /**
     * Navigation icon for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Form $form): Form
    {
        // Filament 4 expects returning the Form builder instance.
        return NewsTagForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
        return NewsTagsTable::configure($table);
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
            'index'  => ListNewsTags::route('/'),
            'create' => CreateNewsTag::route('/create'),
            'view' => ViewNewsTag::route('/{record}'),
            'edit' => EditNewsTag::route('/{record}/edit'),
        ];
    }
}
