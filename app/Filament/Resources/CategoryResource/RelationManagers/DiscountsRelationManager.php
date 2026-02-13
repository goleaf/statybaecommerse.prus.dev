<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use App\Models\Discount;
use App\Models\DiscountCode;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Actions\DetachBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use Illuminate\Validation\ValidationException;

class DiscountsRelationManager extends RelationManager
{
    protected static string $relationship = 'discounts';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.discounts.plural_model_label');
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return SchemaFacade::hasTable('discounts') && SchemaFacade::hasTable('discount_categories');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->label(__('messages.name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('code')
                    ->label(__('messages.code'))
                    ->required()
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->label(__('admin.discounts.is_active'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('code')
                    ->label(__('messages.code'))
                    ->state(static function (Discount $record): ?string {
                        return self::resolveCode($record);
                    })
                    ->sortable(query: static function (Builder $query, string $direction): Builder {
                        if (SchemaFacade::hasColumn('discounts', 'code')) {
                            return $query->orderBy('code', $direction);
                        }

                        if (! SchemaFacade::hasTable('discount_codes')) {
                            return $query;
                        }

                        return $query->orderBy(
                            DiscountCode::withoutGlobalScopes()
                                ->select('code')
                                ->whereColumn('discount_codes.discount_id', 'discounts.id')
                                ->limit(1),
                            $direction,
                        );
                    })
                    ->searchable(query: static function (Builder $query, string $search): Builder {
                        if (SchemaFacade::hasColumn('discounts', 'code')) {
                            return $query->where('code', 'like', "%{$search}%");
                        }

                        if (! SchemaFacade::hasTable('discount_codes')) {
                            return $query;
                        }

                        return $query->whereHas('codes', static function (Builder $codeQuery) use ($search): void {
                            $codeQuery
                                ->withoutGlobalScopes()
                                ->where('code', 'like', "%{$search}%");
                        });
                    }),
                IconColumn::make('is_active')
                    ->sortable()
                    ->label(__('admin.discounts.is_active'))
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect(),
            ])
            ->actions([
                EditAction::make()
                    ->mutateRecordDataUsing(static function (array $data, Discount $record): array {
                        $data['code'] = self::resolveCode($record);

                        return $data;
                    })
                    ->using(static function (array $data, Discount $record): void {
                        $code = trim((string) ($data['code'] ?? ''));

                        if ($code === '') {
                            throw ValidationException::withMessages([
                                'code' => __('validation.required', ['attribute' => __('messages.code')]),
                            ]);
                        }

                        if (SchemaFacade::hasColumn('discounts', 'code')) {
                            $record->update([
                                ...$data,
                                'code' => $code,
                            ]);

                            return;
                        }

                        $record->update(Arr::except($data, ['code']));

                        if (! SchemaFacade::hasTable('discount_codes')) {
                            return;
                        }

                        $conflict = DiscountCode::withoutGlobalScopes()
                            ->where('code', $code)
                            ->where('discount_id', '!=', $record->getKey())
                            ->exists();

                        if ($conflict) {
                            throw ValidationException::withMessages([
                                'code' => __('validation.unique', ['attribute' => __('messages.code')]),
                            ]);
                        }

                        DiscountCode::withoutGlobalScopes()->updateOrCreate(
                            ['discount_id' => $record->getKey()],
                            [
                                'code'        => $code,
                                'name'        => $record->name,
                                'status'      => (string) ($record->status ?? 'active'),
                                'is_active'   => (bool) $record->is_active,
                                'type'        => (string) ($record->type ?? 'fixed'),
                                'value'       => (float) ($record->value ?? 0),
                                'starts_at'   => $record->starts_at,
                                'expires_at'  => $record->ends_at,
                                'valid_from'  => $record->starts_at,
                                'valid_until' => $record->ends_at,
                            ],
                        );
                    }),
                DetachAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }

    private static function resolveCode(Discount $discount): ?string
    {
        if (SchemaFacade::hasColumn('discounts', 'code')) {
            return $discount->getAttribute('code');
        }

        if (! SchemaFacade::hasTable('discount_codes')) {
            return null;
        }

        if ($discount->relationLoaded('codes')) {
            /** @var \Illuminate\Support\Collection<int, DiscountCode> $codes */
            $codes = $discount->getRelation('codes');

            return $codes->first()?->code;
        }

        return $discount->codes()
            ->withoutGlobalScopes()
            ->value('code');
    }
}
