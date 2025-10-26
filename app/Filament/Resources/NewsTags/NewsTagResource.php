<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsTags;

use App\Filament\Resources\NewsTags\Pages\CreateNewsTag;
use App\Filament\Resources\NewsTags\Pages\EditNewsTag;
use App\Filament\Resources\NewsTags\Pages\ListNewsTags;
use App\Filament\Resources\NewsTags\Pages\ViewNewsTag;
use App\Filament\Resources\NewsTags\Schemas\NewsTagForm;
use App\Filament\Resources\NewsTags\Tables\NewsTagsTable;
use App\Support\Concerns\HasNav;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NewsTagResource extends Resource
{
    use HasNav;

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations.
     */
    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return NewsTagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
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
            'view'   => ViewNewsTag::route('/{record}'),
            'edit'   => EditNewsTag::route('/{record}/edit'),
        ];
    }
}
