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
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class NewsTagResource extends Resource
{
    use HasNav;

    protected static ?string $model = NewsTag::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $form): Schema
    {
        return NewsTagForm::configure($form);
    }

    public static function table(Table $table): Table|array
    {
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
