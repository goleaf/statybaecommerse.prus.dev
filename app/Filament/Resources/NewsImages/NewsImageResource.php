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
                            ->relationship('news', 'title')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Choose the related news article this image belongs to.'),
                        Toggle::make('is_featured')
                            ->label('Featured')
                            ->default(false)
                            ->helperText('Mark the image as featured to highlight it in listings.'),
                    ]),
                FileUpload::make('file_path')
                    ->label('Image')
                    ->image()
                    ->directory('news-images')
                    ->visibility('private')
                    ->required()
                    ->helperText('Only image files are allowed. Uploaded files are stored privately in the news-images directory.'),
                TextInput::make('alt_text')
                    ->label('Alt Text')
                    ->maxLength(255)
                    ->helperText('Optional descriptive text used for accessibility (max 255 characters).'),
                Textarea::make('caption')
                    ->maxLength(500)
                    ->columnSpanFull()
                    ->helperText('Optional caption displayed with the image (max 500 characters).'),
                Grid::make(3)
                    ->schema([
                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Controls display priority. Must be zero or a positive integer.'),
                        TextInput::make('file_size')
                            ->label('File Size (bytes)')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Stored in bytes. Automatically captured after upload when available.'),
                        TextInput::make('mime_type')
                            ->label('MIME Type')
                            ->maxLength(255)
                            ->helperText('Automatically detected from the uploaded file. You may override if necessary.'),
                    ]),
                Textarea::make('dimensions')
                    ->label('Dimensions')
                    ->columnSpanFull()
                    ->rows(3)
                    ->helperText('Store width and height as JSON, e.g. {"width": 800, "height": 600}.'),
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
