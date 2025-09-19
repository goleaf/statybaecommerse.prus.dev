<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Models\Brand;
use App\Enums\NavigationGroup;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
/**
 * BrandResource
 * 
 * Filament v4 resource for Brand management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;
    
    /** @var UnitEnum|string|null */
    protected static string | UnitEnum | null $navigationGroup = "Products";
    
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
        return "Products";
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
     * @param Form $form
     * @return Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('brands.basic_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            TextInput::make('name')
                                ->label(__('brands.name'))
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => 
                                    $operation === 'create' ? $set('slug', \Str::slug($state)) : null
                                ),
                            
                            TextInput::make('slug')
                                ->label(__('brands.slug'))
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    
                    Textarea::make('description')
                        ->label(__('brands.description'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
            
            Section::make(__('brands.media'))
                ->components([
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
                    
                    FileUpload::make('banner')
                        ->label(__('brands.banner'))
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatios([
                            '16:9',
                            '21:9',
                            '4:3',
                        ])
                        ->directory('brands/banners')
                        ->visibility('public')
                        ->columnSpanFull(),
                ]),
            
            Section::make(__('brands.seo'))
                ->components([
                    TextInput::make('seo_title')
                        ->label(__('brands.seo_title'))
                        ->maxLength(255)
                        ->columnSpanFull(),
                    
                    Textarea::make('seo_description')
                        ->label(__('brands.seo_description'))
                        ->rows(2)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            
            Section::make(__('brands.settings'))
                ->components([
                    Grid::make(3)
                        ->components([
                            Toggle::make('is_active')
                                ->label(__('brands.is_active'))
                                ->default(true),
                            
                            Toggle::make('is_visible')
                                ->label(__('brands.is_visible'))
                                ->default(true),
                            
                            Toggle::make('is_featured')
                                ->label(__('brands.is_featured')),
                        ]),
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
                
                IconColumn::make('is_visible')
                    ->label(__('brands.is_visible'))
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
                EditAction::make(),
                
                Action::make('toggle_active')
                    ->label(fn (Brand $record): string => $record->is_active ? __('brands.deactivate') : __('brands.activate'))
                    ->icon(fn (Brand $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Brand $record): string => $record->is_active ? 'warning' : 'success')
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
                    DeleteBulkAction::make(),
                    
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
