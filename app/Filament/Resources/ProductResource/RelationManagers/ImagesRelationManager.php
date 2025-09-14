<?php

declare (strict_types=1);
namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\ProductImage;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
/**
 * ImagesRelationManager
 * 
 * Filament v4 resource for ImagesRelationManager management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $relationship
 * @property string|null $title
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';
    protected static ?string $title = 'Images';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $schema->schema([Forms\Components\FileUpload::make('path')->label(__('translations.image'))->image()->required()->disk('public')->directory('products')->visibility('public'), Forms\Components\TextInput::make('alt_text')->label(__('translations.alt_text'))->maxLength(255), Forms\Components\TextInput::make('title')->label(__('translations.title'))->maxLength(255), Forms\Components\TextInput::make('sort_order')->label(__('translations.sort_order'))->numeric()->default(0), Forms\Components\Toggle::make('is_primary')->label(__('translations.is_primary'))->default(false)]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('alt_text')->columns([Tables\Columns\ImageColumn::make('path')->label(__('translations.image'))->size(60)->square(), Tables\Columns\TextColumn::make('alt_text')->label(__('translations.alt_text'))->searchable()->sortable(), Tables\Columns\TextColumn::make('title')->label(__('translations.title'))->searchable()->sortable(), Tables\Columns\TextColumn::make('sort_order')->label(__('translations.sort_order'))->sortable(), Tables\Columns\IconColumn::make('is_primary')->label(__('translations.is_primary'))->boolean(), Tables\Columns\TextColumn::make('created_at')->label(__('translations.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([Tables\Filters\SelectFilter::make('is_primary')->label(__('translations.is_primary'))->options([true => __('translations.yes'), false => __('translations.no')])])->headerActions([Tables\Actions\CreateAction::make()])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make(), Tables\Actions\Action::make('set_primary')->label(__('translations.set_primary'))->icon('heroicon-o-star')->action(function (ProductImage $record) {
            // Remove primary from all other images
            $record->product->images()->update(['is_primary' => false]);
            // Set this image as primary
            $record->update(['is_primary' => true]);
        })->visible(fn(ProductImage $record) => !$record->is_primary)])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])->defaultSort('sort_order');
    }
}