<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\ProductImage;
use App\Support\Filament\Forms\Components\SortOrderInput;
use App\Support\Filament\ProductImageDataNormalizer;
use App\Support\Storage\SecureStorage;
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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\Relation;

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
                    ->disk(SecureStorage::disk())
                    ->directory('product-images')
                    ->visibility('private')
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
                        Table $table,
                    ): void {
                        /** @var HasMany|MorphMany $relationship */
                        $relationship = Relation::noConstraints(fn () => $table->getRelationship());

                        /** @var ProductImage|null $record */
                        $record = ProductImage::query()
                            ->withoutGlobalScopes()
                            ->find($data['recordId'] ?? null);

                        if (! $record instanceof ProductImage) {
                            return;
                        }

                        $action->record($record);

                        /** @var BelongsTo $inverseRelationship */
                        $inverseRelationship = $table->getInverseRelationshipFor($record);

                        $action->process(function () use ($inverseRelationship, $record, $relationship): void {
                            $inverseRelationship->associate($relationship->getParent());
                            $record->save();
                        }, [
                            'inverseRelationship' => $inverseRelationship,
                            'relationship'        => $relationship,
                        ]);

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
                    ->mutateDataUsing(function (array $data): array {
                        $ownerRecord = $this->getOwnerRecord();

                        $rawSortOrder = $data['sort_order'] ?? null;
                        $sortOrderWasProvided = is_string($rawSortOrder)
                            ? trim($rawSortOrder) !== ''
                            : $rawSortOrder !== null;

                        $data = ProductImageDataNormalizer::normalize($data);

                        $data['product_id'] = $ownerRecord->getKey();

                        if (! $sortOrderWasProvided) {
                            $nextSortOrder = (int) (
                                $ownerRecord->images()
                                    ->withoutGlobalScopes()
                                    ->max('sort_order')
                                ?? -1
                            ) + 1;

                            $data['sort_order'] = max(0, $nextSortOrder);
                        }

                        return $data;
                    })
                    ->using(fn (array $data): ProductImage => $this->getOwnerRecord()->images()->create($data)),
            ])
            ->actions([
                EditAction::make()
                    ->mutateDataUsing(static fn (array $data): array => ProductImageDataNormalizer::normalize($data, forUpdate: true)),
                DeleteAction::make(),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }
}
