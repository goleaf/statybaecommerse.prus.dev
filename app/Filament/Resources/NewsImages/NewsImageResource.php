<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsImages;

use App\Filament\Resources\NewsImages\Pages\CreateNewsImage;
use App\Filament\Resources\NewsImages\Pages\EditNewsImage;
use App\Filament\Resources\NewsImages\Pages\ListNewsImages;
use App\Models\NewsImage;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NewsImageResource extends Resource
{
    protected static ?string $model = NewsImage::class;

    public static function getNavigationIcon(): BackedEnum|string|null
    {
        return 'heroicon-o-photo';
    }

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('alt_text')
                    ->label('Alt Text')
                    ->maxLength(255),
                FileUpload::make('image')
                    ->label('Image')
                    ->image()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Image'),
                TextColumn::make('alt_text')
                    ->label('Alt Text')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => ListNewsImages::route('/'),
            'create' => CreateNewsImage::route('/create'),
            // 'view' page does not exist; removing mapping to avoid errors
            'edit' => EditNewsImage::route('/{record}/edit'),
        ];
    }
}
