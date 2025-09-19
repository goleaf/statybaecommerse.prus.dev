<?php

namespace App\Filament\Resources\NewsTags;
use App\Filament\Resources\NewsTags\Pages\CreateNewsTag;
use App\Filament\Resources\NewsTags\Pages\EditNewsTag;
use App\Filament\Resources\NewsTags\Pages\ListNewsTags;
use App\Filament\Resources\NewsTags\Schemas\NewsTagForm;
use App\Filament\Resources\NewsTags\Tables\NewsTagsTable;
use App\Models\NewsTag;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
class NewsTagResource extends Resource
{
    protected static ?string $model = NewsTag::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    public static function form(Schema $schema): Schema
    {
        return NewsTagForm::configure($schema);
    }
    public static function table(Table $table): Table
        return NewsTagsTable::configure($table);
    public static function getRelations(): array
        return [
            //
        ];
    public static function getPages(): array
            'index' => ListNewsTags::route('/'),
            'create' => CreateNewsTag::route('/create'),
            'edit' => EditNewsTag::route('/{record}/edit'),
}
