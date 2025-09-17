<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
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
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * CategoryResource
 *
 * Filament v4 resource for Category management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('categories.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::Products->label();
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('categories.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('categories.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('categories.basic_information'))
                ->schema([
                    Forms\Components\Tabs::make('i18n')
                        ->tabs([
                            Forms\Components\Tabs\Tab::make('LT')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('name.lt')
                                                ->label(__('categories.name') . ' (LT)')
                                                ->required()
                                                ->maxLength(255),
                                            TextInput::make('slug.lt')
                                                ->label(__('categories.slug') . ' (LT)')
                                                ->maxLength(255),
                                        ]),
                                    Textarea::make('description.lt')
                                        ->label(__('categories.description') . ' (LT)')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ]),
                            Forms\Components\Tabs\Tab::make('EN')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('name.en')
                                                ->label(__('categories.name') . ' (EN)')
                                                ->maxLength(255),
                                            TextInput::make('slug.en')
                                                ->label(__('categories.slug') . ' (EN)')
                                                ->maxLength(255),
                                        ]),
                                    Textarea::make('description.en')
                                        ->label(__('categories.description') . ' (EN)')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    Select::make('parent_id')
                        ->label(__('categories.parent_category'))
                        ->relationship('parent', 'name')
                        ->getOptionLabelFromRecordUsing(fn($record) => is_array($record->name) ? ($record->name[app()->getLocale()] ?? ($record->name['lt'] ?? $record->name['en'] ?? reset($record->name))) : $record->name)
                        ->searchable()
                        ->preload()
                        ->columnSpanFull(),
                ]),
            Section::make(__('categories.media'))
                ->schema([
                    FileUpload::make('image')
                        ->label(__('categories.image'))
                        ->image()
                        ->directory('categories/images')
                        ->visibility('public')
                        ->columnSpanFull(),
                ]),
            Section::make(__('categories.appearance'))
                ->schema([
                    ColorPicker::make('color')
                        ->label(__('categories.color'))
                        ->nullable(),
                    Toggle::make('is_featured')
                        ->label(__('categories.is_featured'))
                        ->default(false),
                    Toggle::make('is_active')
                        ->label(__('categories.is_active'))
                        ->default(true),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
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
                    ->getStateUsing(fn(Category $record) => is_array($record->name) ? ($record->name[app()->getLocale()] ?? ($record->name['lt'] ?? $record->name['en'] ?? reset($record->name))) : $record->name)
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
                    ->searchable()
                    ->sortable()
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
                    ->counts('children')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('categories.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label(__('categories.is_featured'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('categories.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('categories.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('categories.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('parent_id')
                    ->label(__('categories.parent_category'))
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label(__('categories.is_active'))
                    ->boolean()
                    ->trueLabel(__('categories.active_only'))
                    ->falseLabel(__('categories.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_featured')
                    ->label(__('categories.is_featured'))
                    ->boolean()
                    ->trueLabel(__('categories.featured_only'))
                    ->falseLabel(__('categories.not_featured'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                TableAction::make('toggle_active')
                    ->label(fn(Category $record): string => $record->is_active ? __('categories.deactivate') : __('categories.activate'))
                    ->icon(fn(Category $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn(Category $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Category $record): void {
                        $record->update(['is_active' => !$record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? __('categories.activated_successfully') : __('categories.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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

    /**
     * Get the relations for this resource.
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     * @return array
     */
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
