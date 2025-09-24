<?php

declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

final class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'System';
    }

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-tag';
    }

    public static function getPluralModelLabel(): string
    {
        return __('categories.plural');
    }

    public static function getModelLabel(): string
    {
        return __('categories.single');
    }

    public static function form(Schema $schema): Schema
    {
        return $form->schema([
            Section::make(__('categories.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('categories.name'))
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', \Str::slug($state)) : null),
                            TextInput::make('slug')
                                ->label(__('categories.slug'))
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    Select::make('parent_id')
                        ->label(__('categories.parent_category'))
                        ->relationship('parent', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            TextInput::make('name')
                                ->label(__('categories.name'))
                                ->required()
                                ->maxLength(255),
                        ]),
                    Textarea::make('description')
                        ->label(__('categories.description'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
            Section::make(__('categories.media'))
                ->schema([
                    FileUpload::make('image')
                        ->label(__('categories.image'))
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatios([
                            '1:1',
                            '16:9',
                            '4:3',
                        ])
                        ->directory('categories/images')
                        ->visibility('public'),
                    FileUpload::make('banner')
                        ->label(__('categories.banner'))
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatios([
                            '21:9',
                        ])
                        ->directory('categories/banners')
                        ->visibility('public'),
                ]),
            Section::make(__('categories.appearance'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            ColorPicker::make('color')
                                ->label(__('categories.color'))
                                ->hex(),
                            TextInput::make('sort_order')
                                ->label(__('categories.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                        ]),
                ]),
            Section::make(__('categories.seo'))
                ->schema([
                    TextInput::make('seo_title')
                        ->label(__('categories.seo_title'))
                        ->maxLength(255),
                    Textarea::make('seo_description')
                        ->label(__('categories.seo_description'))
                        ->rows(2)
                        ->maxLength(500),
                ]),
            Section::make(__('categories.settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('categories.is_active'))
                                ->default(true),
                            Toggle::make('is_featured')
                                ->label(__('categories.is_featured')),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label(__('categories.image'))
                    ->circular()
                    ->size(40),
                TextColumn::make('name')
                    ->label(__('categories.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->formatStateUsing(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        $record = $column->getRecord();
                        if ($record->parent) {
                            return "{$record->parent->name} → {$state}";
                        }

                        return $state;
                    }),
                TextColumn::make('slug')
                    ->label(__('categories.slug'))
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ColorColumn::make('color')
                    ->label(__('categories.color'))
                    ->toggleable(),
                TextColumn::make('products_count')
                    ->label(__('categories.products_count'))
                    ->counts('products')
                    ->sortable(),
                TextColumn::make('children_count')
                    ->label(__('categories.subcategories_count'))
                    ->counts('children'),
                IconColumn::make('is_active')
                    ->label(__('categories.is_active'))
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label(__('categories.is_featured'))
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label(__('categories.sort_order'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('categories.created_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('categories.updated_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('parent_id')
                    ->label(__('categories.parent_category'))
                    ->relationship('parent', 'name')
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->trueLabel(__('categories.active_only'))
                    ->falseLabel(__('categories.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_featured')
                    ->trueLabel(__('categories.featured_only'))
                    ->falseLabel(__('categories.not_featured'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn (Category $record): string => $record->is_active ? __('categories.deactivate') : __('categories.activate'))
                    ->icon(fn (Category $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Category $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Category $record): void {
                        $record->update(['is_active' => ! $record->is_active]);
                        Notification::make()
                            ->title($record->is_active ? __('categories.activated_successfully') : __('categories.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('categories.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('categories.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('categories.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('categories.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'view' => Pages\ViewCategory::route('/{record}'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
