<?php

declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\VariantImageResource\Pages;
use App\Models\ProductVariant;
use App\Models\VariantImage;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * VariantImageResource
 *
 * Filament v4 resource for VariantImage management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class VariantImageResource extends Resource
{
    protected static ?string $model = VariantImage::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-photo';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Inventory';
    }

    protected static ?int $navigationSort = 15;

    public static function getNavigationLabel(): string
    {
        return __('admin.variant_images.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.variant_images.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.variant_images.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.variant_images.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('variant_id')
                                ->label(__('admin.variant_images.variant'))
                                ->relationship('variant', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state, $set) {
                                    if ($state) {
                                        // Auto-generate sort order based on existing images
                                        $nextSortOrder = VariantImage::where('variant_id', $state)
                                            ->max('sort_order') + 1;
                                        $set('sort_order', $nextSortOrder);
                                    }
                                }),
                            Placeholder::make('variant_info')
                                ->label(__('admin.variant_images.variant_info'))
                                ->content(function ($get) {
                                    $variantId = $get('variant_id');
                                    if ($variantId) {
                                        $variant = ProductVariant::find($variantId);

                                        return $variant ? "SKU: {$variant->sku}" : '';
                                    }

                                    return '';
                                })
                                ->visible(fn($get) => !empty($get('variant_id'))),
                        ]),
                ]),
            Section::make(__('admin.variant_images.image_details'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            FileUpload::make('image_path')
                                ->label(__('admin.variant_images.image'))
                                ->image()
                                ->directory('variant-images')
                                ->visibility('public')
                                ->required()
                                ->imageEditor()
                                ->imageEditorAspectRatios([
                                    '16:9',
                                    '4:3',
                                    '1:1',
                                ])
                                ->maxSize(5120)  // 5MB
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                ->helperText(__('admin.variant_images.image_help')),
                            TextInput::make('alt_text')
                                ->label(__('admin.variant_images.alt_text'))
                                ->maxLength(255)
                                ->helperText(__('admin.variant_images.alt_text_help')),
                        ]),
                    Textarea::make('description')
                        ->label(__('admin.variant_images.description'))
                        ->maxLength(1000)
                        ->rows(3)
                        ->columnSpanFull()
                        ->helperText(__('admin.variant_images.description_help')),
                ]),
            Section::make(__('admin.variant_images.display_settings'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('sort_order')
                                ->label(__('admin.variant_images.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->helperText(__('admin.variant_images.sort_order_help')),
                            Toggle::make('is_primary')
                                ->label(__('admin.variant_images.is_primary'))
                                ->default(false)
                                ->helperText(__('admin.variant_images.is_primary_help')),
                            Toggle::make('is_active')
                                ->label(__('admin.variant_images.is_active'))
                                ->default(true)
                                ->helperText(__('admin.variant_images.is_active_help')),
                        ]),
                ]),
            Section::make(__('admin.variant_images.metadata'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('file_size')
                                ->label(__('admin.variant_images.file_size'))
                                ->disabled()
                                ->dehydrated(false),
                            TextInput::make('dimensions')
                                ->label(__('admin.variant_images.dimensions'))
                                ->disabled()
                                ->dehydrated(false),
                        ]),
                    Hidden::make('created_by')
                        ->default(auth()->id()),
                ])
                ->collapsible()
                ->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label(__('admin.variant_images.image'))
                    ->size(80)
                    ->circular(false)
                    ->square()
                    ->grow(false),
                TextColumn::make('variant.name')
                    ->label(__('admin.variant_images.variant'))
                    ->sortable()
                    ->searchable()
                    ->weight('bold')
                    ->limit(30),
                TextColumn::make('variant.sku')
                    ->label(__('admin.variant_images.sku'))
                    ->sortable()
                    ->searchable()
                    ->color('gray')
                    ->limit(20),
                TextColumn::make('alt_text')
                    ->label(__('admin.variant_images.alt_text'))
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    })
                    ->toggleable(),
                TextColumn::make('description')
                    ->label(__('admin.variant_images.description'))
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 40 ? $state : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sort_order')
                    ->label(__('admin.variant_images.sort_order'))
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info'),
                IconColumn::make('is_primary')
                    ->label(__('admin.variant_images.is_primary'))
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                IconColumn::make('is_active')
                    ->label(__('admin.variant_images.is_active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->toggleable(),
                TextColumn::make('file_size')
                    ->label(__('admin.variant_images.file_size'))
                    ->formatStateUsing(function ($state) {
                        if ($state) {
                            return number_format($state / 1024, 2) . ' KB';
                        }

                        return '-';
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('dimensions')
                    ->label(__('admin.variant_images.dimensions'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('admin.variant_images.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.variant_images.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('variant_id')
                    ->label(__('admin.variant_images.variant'))
                    ->relationship('variant', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_primary')
                    ->label(__('admin.variant_images.is_primary'))
                    ->trueLabel(__('admin.variant_images.primary_only'))
                    ->falseLabel(__('admin.variant_images.non_primary_only'))
                    ->native(false),
                TernaryFilter::make('is_active')
                    ->label(__('admin.variant_images.is_active'))
                    ->trueLabel(__('admin.variant_images.active_only'))
                    ->falseLabel(__('admin.variant_images.inactive_only'))
                    ->native(false),
                Filter::make('created_at')
                    ->form([
                        DateFilter::make('created_from')
                            ->label(__('admin.variant_images.created_from')),
                        DateFilter::make('created_until')
                            ->label(__('admin.variant_images.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('set_as_primary')
                    ->label(__('admin.variant_images.set_as_primary'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->action(function (VariantImage $record): void {
                        // Remove primary from other images of the same variant
                        VariantImage::where('variant_id', $record->variant_id)
                            ->where('id', '!=', $record->id)
                            ->update(['is_primary' => false]);

                        // Set this image as primary
                        $record->update(['is_primary' => true]);

                        Notification::make()
                            ->title(__('admin.variant_images.set_as_primary_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->visible(fn(VariantImage $record): bool => !$record->is_primary),
                Action::make('toggle_active')
                    ->label(fn(VariantImage $record): string => $record->is_active
                        ? __('admin.variant_images.deactivate')
                        : __('admin.variant_images.activate'))
                    ->icon(fn(VariantImage $record): string => $record->is_active
                        ? 'heroicon-o-x-circle'
                        : 'heroicon-o-check-circle')
                    ->color(fn(VariantImage $record): string => $record->is_active
                        ? 'danger'
                        : 'success')
                    ->action(function (VariantImage $record): void {
                        $record->update(['is_active' => !$record->is_active]);

                        Notification::make()
                            ->title($record->is_active
                                ? __('admin.variant_images.activated_successfully')
                                : __('admin.variant_images.deactivated_successfully'))
                            ->success()
                            ->send();
                    }),
                Action::make('duplicate')
                    ->label(__('admin.variant_images.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->action(function (VariantImage $record): void {
                        $newImage = $record->replicate();
                        $newImage->is_primary = false;
                        $newImage->sort_order = VariantImage::where('variant_id', $record->variant_id)
                            ->max('sort_order') + 1;
                        $newImage->save();

                        Notification::make()
                            ->title(__('admin.variant_images.duplicated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate_selected')
                        ->label(__('admin.variant_images.activate_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('admin.variant_images.bulk_activated_successfully'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate_selected')
                        ->label(__('admin.variant_images.deactivate_selected'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('admin.variant_images.bulk_deactivated_successfully'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('reorder_images')
                        ->label(__('admin.variant_images.reorder_images'))
                        ->icon('heroicon-o-arrows-up-down')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            // Auto-reorder based on current sort order
                            $records->sortBy('sort_order')->each(function ($record, $index) {
                                $record->update(['sort_order' => $index + 1]);
                            });

                            Notification::make()
                                ->title(__('admin.variant_images.reordered_successfully'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('set_primary')
                        ->label(__('admin.variant_images.set_primary_selected'))
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            // Group by variant_id and set first image as primary for each variant
                            $records->groupBy('variant_id')->each(function ($variantImages) {
                                // Remove primary from all images in this variant
                                VariantImage::where('variant_id', $variantImages->first()->variant_id)
                                    ->update(['is_primary' => false]);

                                // Set first image as primary
                                $variantImages->first()->update(['is_primary' => true]);
                            });

                            Notification::make()
                                ->title(__('admin.variant_images.bulk_primary_set_successfully'))
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
            'index' => Pages\ListVariantImages::route('/'),
            'create' => Pages\CreateVariantImage::route('/create'),
            'view' => Pages\ViewVariantImage::route('/{record}'),
            'edit' => Pages\EditVariantImage::route('/{record}/edit'),
        ];
    }
}
