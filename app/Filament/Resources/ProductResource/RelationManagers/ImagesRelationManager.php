<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ProductImages\ProductImageWriteService;
use App\Support\Filament\Forms\Components\SortOrderInput;
use Filament\Actions\AssociateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $recordTitleAttribute = 'path';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('messages.images');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                FileUpload::make('path')
                    ->label(__('messages.image'))
                    ->image()
                    ->disk('public')
                    ->directory('product-images')
                    ->visibility('public')
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->imagePreviewHeight('250'),
                TextInput::make('alt_text')
                    ->label(__('messages.alt_text'))
                    ->maxLength(255),
                SortOrderInput::make()
                    ->default(null),
                Toggle::make('is_default')
                    ->label(__('messages.is_main'))
                    ->default(fn (RelationManager $livewire): bool => $livewire->getOwnerRecord()
                        ->images()
                        ->withoutGlobalScopes()
                        ->count() === 0),
                Toggle::make('is_active')
                    ->label(__('messages.active'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(static fn ($query) => $query->withoutGlobalScopes())
            ->columns([
                ImageColumn::make('url')
                    ->label(__('messages.preview'))
                    ->circular(),
                TextColumn::make('alt_text')
                    ->sortable()
                    ->label(__('messages.alt_text'))
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->label(__('messages.sort_order'))
                    ->sortable(),
                IconColumn::make('is_default')
                    ->sortable()
                    ->label(__('messages.is_main'))
                    ->boolean()
                    ->trueIcon('heroicon-m-star')
                    ->falseIcon('heroicon-m-minus')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                ToggleColumn::make('is_active')
                    ->sortable()
                    ->label(__('messages.active')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AssociateAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['alt_text', 'path'])
                    ->recordSelectOptionsQuery(fn (Builder $query): Builder => $query->withoutGlobalScopes())
                    ->action(function (
                        AssociateAction $action,
                        array $arguments,
                        array $data,
                        Schema $schema,
                    ): void {
                        $sourceImage = ProductImage::query()
                            ->withoutGlobalScopes()
                            ->find($data['recordId'] ?? null);

                        $owner = $this->getOwnerRecord();

                        if (! $sourceImage instanceof ProductImage || ! $owner instanceof Product) {
                            return;
                        }

                        $action->record($sourceImage);
                        $this->imageWriteService()->cloneAttach($owner, $sourceImage);

                        if ($arguments['another'] ?? false) {
                            $action->callAfter();
                            $action->sendSuccessNotification();
                            $action->record(null);
                            $schema->fill();
                            $action->halt();

                            return;
                        }

                        $action->success();
                    }),
                CreateAction::make()
                    ->using(function (array $data): ProductImage {
                        $owner = $this->getOwnerRecord();

                        if (! $owner instanceof Product) {
                            throw new \RuntimeException('Owner record must be a product.');
                        }

                        return $this->imageWriteService()->create($owner, $data);
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->using(function (array $data, ProductImage $record): void {
                        $this->imageWriteService()->update($record, $data);
                    }),
                DeleteAction::make()
                    ->action(fn (ProductImage $record) => $this->imageWriteService()->delete($record)),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }

    private function imageWriteService(): ProductImageWriteService
    {
        return app(ProductImageWriteService::class);
    }
}
