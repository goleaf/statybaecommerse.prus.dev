<?php

declare(strict_types=1);

namespace App\Filament\Resources\Sliders;

use App\Filament\Resources\Sliders\Pages\CreateSlider;
use App\Filament\Resources\Sliders\Pages\EditSlider;
use App\Filament\Resources\Sliders\Pages\ListSliders;
use App\Models\Slider;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Form;
use UnitEnum;

final class SliderResource extends Resource
{
    protected static ?string $model = Slider::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    /** @var UnitEnum|string|null */
    protected static $navigationGroup = 'Content';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Translations')
                    ->tabs([
                        Tab::make('Lithuanian (LT)')
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('title')
                                    ->label('Title (LT)')
                                    ->required()
                                    ->maxLength(255),
                                \Filament\Forms\Components\Textarea::make('description')
                                    ->label('Description (LT)')
                                    ->maxLength(1000)
                                    ->columnSpanFull(),
                                \Filament\Forms\Components\TextInput::make('button_text')
                                    ->label('Button Text (LT)')
                                    ->maxLength(255),
                            ]),
                        Tab::make('English (EN)')
                            ->schema([
                                \Filament\Forms\Components\Repeater::make('translations')
                                    ->relationship('translations')
                                    ->schema([
                                        \Filament\Forms\Components\Hidden::make('locale')
                                            ->default('en'),
                                        \Filament\Forms\Components\TextInput::make('title')
                                            ->label('Title (EN)')
                                            ->required()
                                            ->maxLength(255),
                                        \Filament\Forms\Components\Textarea::make('description')
                                            ->label('Description (EN)')
                                            ->maxLength(1000)
                                            ->columnSpanFull(),
                                        \Filament\Forms\Components\TextInput::make('button_text')
                                            ->label('Button Text (EN)')
                                            ->maxLength(255),
                                    ])
                                    ->defaultItems(1)
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                            ]),
                    ])
                    ->columnSpanFull(),
                
                \Filament\Forms\Components\TextInput::make('button_url')
                    ->url()
                    ->maxLength(255),
                \Filament\Forms\Components\FileUpload::make('image')
                    ->image()
                    ->directory('sliders')
                    ->visibility('public'),
                \Filament\Forms\Components\ColorPicker::make('background_color')
                    ->default('#ffffff'),
                \Filament\Forms\Components\ColorPicker::make('text_color')
                    ->default('#000000'),
                \Filament\Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                \Filament\Forms\Components\Toggle::make('is_active')
                    ->default(true),
                \Filament\Forms\Components\KeyValue::make('settings')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\ImageColumn::make('image')
                    ->circular(),
                \Filament\Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('button_text')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),
                \Filament\Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->native(false),
            ])
            ->actions([
                \Filament\EditAction::make(),
                \Filament\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
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
            'index' => ListSliders::route('/'),
            'create' => CreateSlider::route('/create'),
            'edit' => EditSlider::route('/{record}/edit'),
        ];
    }
}
