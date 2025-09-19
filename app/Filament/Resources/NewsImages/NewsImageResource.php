<?php

namespace App\Filament\Resources\NewsImages;
use App\Filament\Resources\NewsImages\Pages\CreateNewsImage;
use App\Filament\Resources\NewsImages\Pages\EditNewsImage;
use App\Filament\Resources\NewsImages\Pages\ListNewsImages;
use App\Filament\Resources\NewsImages\Schemas\NewsImageForm;
use App\Filament\Resources\NewsImages\Tables\NewsImagesTable;
use App\Models\NewsImage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
class NewsImageResource extends Resource
{
    protected static ?string $model = NewsImage::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    public static function form(Schema $schema): Schema
    {
        return NewsImageForm::configure($schema);
    }
    public static function table(Table $table): Table
        return NewsImagesTable::configure($table);
    public static function getRelations(): array
        return [
            //
        ];
    public static function getPages(): array
            'index' => ListNewsImages::route('/'),
            'create' => CreateNewsImage::route('/create'),
            'edit' => EditNewsImage::route('/{record}/edit'),
}
