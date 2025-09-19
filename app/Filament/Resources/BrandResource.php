<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\BrandResource\Pages;
use App\Models\Brand;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

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
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('brands.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('brands.name'))
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) =>
                                    $operation === 'create' ? $set('slug', \Str::slug($state)) : null),
                            TextInput::make('slug')
                                ->label(__('brands.slug'))
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    Textarea::make('description')
                        ->label(__('brands.description'))
                        ->rows(3)
                        ->columnSpanFull(),
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
                        ->visibility('public'),
                    FileUpload::make('banner')
                        ->label(__('brands.banner'))
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatios([
                            '21:9',
                        ])
                        ->directory('brands/banners')
                        ->visibility('public'),
                ]),
            Section::make(__('brands.seo'))
                ->schema([
                    TextInput::make('seo_title')
                        ->label(__('brands.seo_title'))
                        ->maxLength(255),
                    Textarea::make('seo_description')
                        ->label(__('brands.seo_description'))
                        ->rows(2)
                        ->maxLength(500),
                ]),
            Section::make(__('brands.settings'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('brands.is_active'))
                                ->default(true),
                            Toggle::make('is_visible')
                                ->label(__('brands.is_visible')),
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
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('products_count')
                    ->label(__('brands.products_count'))
                    ->counts('products')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('brands.is_active'))
                    ->boolean(),
                IconColumn::make('is_visible')
                    ->label(__('brands.is_visible'))
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label(__('brands.is_featured'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('brands.created_at'))
                    ->dateTime(),
                TextColumn::make('updated_at')
                    ->label(__('brands.updated_at'))
                    ->dateTime(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->trueLabel(__('brands.active_only'))
                    ->falseLabel(__('brands.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_featured')
                    ->trueLabel(__('brands.featured_only'))
                    ->falseLabel(__('brands.not_featured'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
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
