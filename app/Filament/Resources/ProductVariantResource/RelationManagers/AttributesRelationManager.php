<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\RelationManagers;

use App\Models\AttributeValue;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AttributesRelationManager extends RelationManager
{
    protected static string $relationship = 'attributes';

    protected static ?string $recordTitleAttribute = 'value';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('messages.attributes');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('attribute.name')
                    ->label(__('messages.attribute'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('value')
                    ->label(__('messages.attribute_value'))
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['value', 'display_value'])
                    ->recordSelectOptionsQuery(
                        static fn (Builder $query): Builder => $query
                            ->withoutGlobalScopes()
                            ->with('attribute')
                            ->orderBy('value'),
                    )
                    ->using(function (AttachAction $action, BelongsToMany $relationship): void {
                        $record = $action->getRecord();

                        if (! $record instanceof AttributeValue) {
                            return;
                        }

                        $variantId = (int) ($this->getOwnerRecord()->getKey() ?? 0);
                        $attributeValueId = (int) ($record->getKey() ?? 0);
                        $attributeId = (int) ($record->attribute_id ?? 0);

                        if ($variantId < 1 || $attributeId < 1 || $attributeValueId < 1) {
                            return;
                        }

                        // Keep one value per attribute on each variant to satisfy
                        // the unique (variant_id, attribute_id) database constraint.
                        $relationship->newPivotStatement()
                            ->where('variant_id', $variantId)
                            ->where('attribute_id', $attributeId)
                            ->delete();

                        $relationship->syncWithoutDetaching([
                            $attributeValueId => [
                                'attribute_id' => $attributeId,
                            ],
                        ]);
                    }),
            ])
            ->actions([
                DetachAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
