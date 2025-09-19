<?php declare(strict_types=1);

namespace App\Filament\Resources;
use App\Enums\NavigationGroup;
use App\Filament\Resources\ProductVariantResource\Pages;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\BadgeColumn;
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
use Illuminate\Support\Str;
use BackedEnum;
/**
 * ProductVariantResource
 *
 * Filament v4 resource for ProductVariant management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class ProductVariantResource extends Resource
{
    // protected static $navigationGroup = NavigationGroup::Products;
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'display_name';
    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('product_variants.title');
    }
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
    public static function getNavigationGroup(): ?string
        return "Products";
     * Handle getPluralModelLabel functionality with proper error handling.
    public static function getPluralModelLabel(): string
        return __('product_variants.plural');
     * Handle getModelLabel functionality with proper error handling.
    public static function getModelLabel(): string
        return __('product_variants.single');
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
    public static function form(Schema $schema): Schema
    {
                        'in_stock' => __('product_variants.stock_status.in_stock'),
                        'low_stock' => __('product_variants.stock_status.low_stock'),
                        'out_of_stock' => __('product_variants.stock_status.out_of_stock'),
                        'not_tracked' => __('product_variants.stock_status.not_tracked'),
                        default => $state,
                    }),
                IconColumn::make('is_enabled')
                    ->label(__('product_variants.fields.is_enabled'))
                    ->boolean(),
                IconColumn::make('is_default_variant')
                    ->label(__('product_variants.fields.is_default_variant'))
                IconColumn::make('is_featured')
                    ->label(__('product_variants.fields.is_featured'))
                    ->boolean()
                IconColumn::make('is_on_sale')
                    ->label(__('product_variants.fields.is_on_sale'))
                TextColumn::make('created_at')
                    ->label(__('product_variants.fields.created_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->preload(),
                SelectFilter::make('variant_type')
                    ->label(__('product_variants.fields.variant_type'))
                    ->options([
                        'size' => __('product_variants.variant_types.size'),
                        'color' => __('product_variants.variant_types.color'),
                        'material' => __('product_variants.variant_types.material'),
                        'style' => __('product_variants.variant_types.style'),
                        'custom' => __('product_variants.variant_types.custom'),
                    ]),
                SelectFilter::make('stock_status')
                TernaryFilter::make('is_enabled')
                    ->label(__('product_variants.fields.is_enabled')),
                TernaryFilter::make('is_default_variant')
                    ->label(__('product_variants.fields.is_default_variant')),
                TernaryFilter::make('is_featured')
                    ->label(__('product_variants.fields.is_featured')),
                TernaryFilter::make('is_on_sale')
                    ->label(__('product_variants.fields.is_on_sale')),
                TernaryFilter::make('is_new')
                    ->label(__('product_variants.fields.is_new')),
                TernaryFilter::make('is_bestseller')
                    ->label(__('product_variants.fields.is_bestseller')),
            ->actions([
                Action::make('set_default')
                    ->label(__('product_variants.actions.set_default'))
                    ->icon('heroicon-o-star')
                    ->action(function (ProductVariant $record) {
                        $record->setAsDefault();
                        Notification::make()
                            ->title(__('product_variants.messages.set_as_default_success'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn(ProductVariant $record): bool => !$record->is_default_variant),
                EditAction::make(),
                DeleteAction::make(),
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('enable')
                        ->label(__('product_variants.actions.enable'))
                        ->icon('heroicon-o-check-circle')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_enabled' => true]);
                            Notification::make()
                                ->title(__('product_variants.messages.bulk_enable_success'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('disable')
                        ->label(__('product_variants.actions.disable'))
                        ->icon('heroicon-o-x-circle')
                            $records->each->update(['is_enabled' => false]);
                                ->title(__('product_variants.messages.bulk_disable_success'))
                    DeleteBulkAction::make(),
                ]),
            ->defaultSort('created_at', 'desc');
     * Get the resource pages.
     * @return array
    public static function getPages(): array
        return [
            'index' => Pages\ListProductVariants::route('/'),
            'create' => Pages\CreateProductVariant::route('/create'),
            'edit' => Pages\EditProductVariant::route('/{record}/edit'),
        ];
}
