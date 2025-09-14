<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductVariantResource\Pages;
use App\Models\ProductVariant;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

final class ProductVariantResource extends Resource
{
    protected static ?string $model = ProductVariant::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Products';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('admin.product_variant.basic_information'))
                    ->schema([
                        Select::make('product_id')
                            ->label(__('admin.product_variant.product'))
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        TextInput::make('sku')
                            ->label(__('admin.product_variant.sku'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        
                        TextInput::make('name')
                            ->label(__('admin.product_variant.name'))
                            ->required()
                            ->maxLength(255),
                        
                        TextInput::make('barcode')
                            ->label(__('admin.product_variant.barcode'))
                            ->maxLength(255),
                    ])
                    ->columns(2),
                
                Section::make(__('admin.product_variant.pricing'))
                    ->schema([
                        TextInput::make('price')
                            ->label(__('admin.product_variant.price'))
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01)
                            ->required(),
                        
                        TextInput::make('compare_price')
                            ->label(__('admin.product_variant.compare_price'))
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                        
                        TextInput::make('cost_price')
                            ->label(__('admin.product_variant.cost_price'))
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                    ])
                    ->columns(3),
                
                Section::make(__('admin.product_variant.inventory'))
                    ->schema([
                        Toggle::make('track_quantity')
                            ->label(__('admin.product_variant.track_quantity'))
                            ->default(true),
                        
                        TextInput::make('quantity')
                            ->label(__('admin.product_variant.quantity'))
                            ->numeric()
                            ->default(0)
                            ->visible(fn (Forms\Get $get) => $get('track_quantity')),
                        
                        TextInput::make('position')
                            ->label(__('admin.product_variant.position'))
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(3),
                
                Section::make(__('admin.product_variant.physical_properties'))
                    ->schema([
                        TextInput::make('weight')
                            ->label(__('admin.product_variant.weight'))
                            ->numeric()
                            ->suffix('kg')
                            ->step(0.001),
                        
                        TextInput::make('length')
                            ->label(__('admin.product_variant.length'))
                            ->numeric()
                            ->suffix('cm')
                            ->step(0.01),
                        
                        TextInput::make('width')
                            ->label(__('admin.product_variant.width'))
                            ->numeric()
                            ->suffix('cm')
                            ->step(0.01),
                        
                        TextInput::make('height')
                            ->label(__('admin.product_variant.height'))
                            ->numeric()
                            ->suffix('cm')
                            ->step(0.01),
                    ])
                    ->columns(4),
                
                Section::make(__('admin.product_variant.status'))
                    ->schema([
                        Toggle::make('is_enabled')
                            ->label(__('admin.product_variant.is_enabled'))
                            ->default(true),
                        
                        Select::make('status')
                            ->label(__('admin.product_variant.status'))
                            ->options([
                                'active' => __('admin.product_variant.status_active'),
                                'inactive' => __('admin.product_variant.status_inactive'),
                                'draft' => __('admin.product_variant.status_draft'),
                            ])
                            ->default('active')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('admin.product_variant.id'))
                    ->sortable(),
                
                TextColumn::make('product.name')
                    ->label(__('admin.product_variant.product'))
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('sku')
                    ->label(__('admin.product_variant.sku'))
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('name')
                    ->label(__('admin.product_variant.name'))
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('price')
                    ->label(__('admin.product_variant.price'))
                    ->money('EUR')
                    ->sortable(),
                
                TextColumn::make('compare_price')
                    ->label(__('admin.product_variant.compare_price'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('cost_price')
                    ->label(__('admin.product_variant.cost_price'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('quantity')
                    ->label(__('admin.product_variant.quantity'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                
                IconColumn::make('track_quantity')
                    ->label(__('admin.product_variant.track_quantity'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_enabled')
                    ->label(__('admin.product_variant.is_enabled'))
                    ->boolean(),
                
                BadgeColumn::make('status')
                    ->label(__('admin.product_variant.status'))
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                        'warning' => 'draft',
                    ]),
                
                TextColumn::make('position')
                    ->label(__('admin.product_variant.position'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label(__('admin.product_variant.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label(__('admin.product_variant.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label(__('admin.product_variant.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('status')
                    ->label(__('admin.product_variant.status'))
                    ->options([
                        'active' => __('admin.product_variant.status_active'),
                        'inactive' => __('admin.product_variant.status_inactive'),
                        'draft' => __('admin.product_variant.status_draft'),
                    ]),
                
                TernaryFilter::make('is_enabled')
                    ->label(__('admin.product_variant.is_enabled')),
                
                TernaryFilter::make('track_quantity')
                    ->label(__('admin.product_variant.track_quantity')),
                
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
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
            'index' => Pages\ListProductVariants::route('/'),
            'create' => Pages\CreateProductVariant::route('/create'),
            'view' => Pages\ViewProductVariant::route('/{record}'),
            'edit' => Pages\EditProductVariant::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
