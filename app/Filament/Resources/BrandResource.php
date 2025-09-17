<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\BrandResource\Pages;
use App\Models\Brand;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * BrandResource
 *
 * Filament v4 resource for Brand management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('brands.title');
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
        return __('brands.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('brands.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('brands.basic_information'))
                ->schema([
                    Forms\Components\Tabs::make('i18n')
                        ->tabs([
                            Forms\Components\Tabs\Tab::make('LT')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('name.lt')
                                                ->label(__('brands.name') . ' (LT)')
                                                ->required()
                                                ->maxLength(255),
                                            TextInput::make('slug.lt')
                                                ->label(__('brands.slug') . ' (LT)')
                                                ->maxLength(255),
                                        ]),
                                    Textarea::make('description.lt')
                                        ->label(__('brands.description') . ' (LT)')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ]),
                            Forms\Components\Tabs\Tab::make('EN')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('name.en')
                                                ->label(__('brands.name') . ' (EN)')
                                                ->maxLength(255),
                                            TextInput::make('slug.en')
                                                ->label(__('brands.slug') . ' (EN)')
                                                ->maxLength(255),
                                        ]),
                                    Textarea::make('description.en')
                                        ->label(__('brands.description') . ' (EN)')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ]),
                        ]),
                ]),
            Section::make(__('brands.media'))
                ->schema([
                    FileUpload::make('logo')
                        ->label(__('brands.logo'))
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatios([
                            '1:1',
                            '16:9',
                            '4:3',
                        ])
                        ->directory('brands/logos')
                        ->visibility('public')
                        ->columnSpanFull(),
                ]),
            Section::make(__('brands.visibility'))
                ->schema([
                    Toggle::make('is_featured')
                        ->label(__('brands.is_featured'))
                        ->default(false),
                    Toggle::make('is_active')
                        ->label(__('brands.is_active'))
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
                ImageColumn::make('logo')
                    ->label(__('brands.logo'))
                    ->circular()
                    ->size(40),
                TextColumn::make('name')
                    ->label(__('brands.name'))
                    ->getStateUsing(fn(Brand $record) => is_array($record->name) ? ($record->name[app()->getLocale()] ?? ($record->name['lt'] ?? $record->name['en'] ?? reset($record->name))) : $record->name)
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('slug')
                    ->label(__('brands.slug'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('products_count')
                    ->label(__('brands.products_count'))
                    ->counts('products')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('brands.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label(__('brands.is_featured'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('brands.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('brands.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('brands.is_active'))
                    ->boolean()
                    ->trueLabel(__('brands.active_only'))
                    ->falseLabel(__('brands.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_featured')
                    ->label(__('brands.is_featured'))
                    ->boolean()
                    ->trueLabel(__('brands.featured_only'))
                    ->falseLabel(__('brands.not_featured'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                TableAction::make('toggle_active')
                    ->label(fn(Brand $record): string => $record->is_active ? __('brands.deactivate') : __('brands.activate'))
                    ->icon(fn(Brand $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn(Brand $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Brand $record): void {
                        $record->update(['is_active' => !$record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? __('brands.activated_successfully') : __('brands.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('brands.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);

                            Notification::make()
                                ->title(__('brands.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('brands.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);

                            Notification::make()
                                ->title(__('brands.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('name');
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
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'view' => Pages\ViewBrand::route('/{record}'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
