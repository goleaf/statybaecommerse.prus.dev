<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsImages;

use App\Filament\Resources\NewsImages\Pages\CreateNewsImage;
use App\Filament\Resources\NewsImages\Pages\EditNewsImage;
use App\Filament\Resources\NewsImages\Pages\ListNewsImages;
use App\Models\NewsImage;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        Select::make('news_id')
                            ->label('News Item')
                            ->relationship('news', 'title')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Select the news article this image belongs to.'),
                        Toggle::make('is_featured')
                            ->label('Featured Image')
                            ->default(false)
                            ->helperText('Mark this image as featured to highlight it in listings.'),
                        FileUpload::make('file_path')
                            ->label('Image File')
                            ->image()
                            ->directory('news-images')
                            ->visibility('private')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                            ->required()
                            ->columnSpanFull()
                            ->helperText('Upload a JPG, PNG, GIF, or WEBP file. Stored privately.'),
                        TextInput::make('alt_text')
                            ->label('Alt Text')
                            ->maxLength(255)
                            ->helperText('Describe the image for accessibility (max 255 characters).'),
                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required()
                            ->helperText('Controls display order; must be a number zero or greater.'),
                        Textarea::make('caption')
                            ->label('Caption')
                            ->rows(3)
                            ->columnSpanFull()
                            ->maxLength(500)
                            ->helperText('Optional caption shown with the image (max 500 characters).'),
                        TextInput::make('file_size')
                            ->label('File Size (bytes)')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Optional: populate with the file size in bytes if known.'),
                        TextInput::make('mime_type')
                            ->label('MIME Type')
                            ->maxLength(255)
                            ->helperText('Optional MIME type value (e.g., image/jpeg).'),
                        Textarea::make('dimensions')
                            ->label('Dimensions')
                            ->rows(2)
                            ->columnSpanFull()
                            ->helperText('Optional JSON object storing width/height, e.g., {"width":800,"height":600}.'),
                    ]),
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
