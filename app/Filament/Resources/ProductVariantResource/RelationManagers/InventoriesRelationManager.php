<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\RelationManagers;

use App\Models\Location;
use App\Models\VariantInventory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class InventoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'inventories';

    protected static ?string $recordTitleAttribute = 'sku';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.inventory_management.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('location_id')
                    ->label(__('messages.warehouse'))
                    ->options(static fn (): array => Location::query()
                        ->withoutGlobalScopes()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('warehouse_code')
                    ->label(__('messages.code'))
                    ->maxLength(255),
                TextInput::make('stock')
                    ->label(__('messages.quantity'))
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->required(),
                TextInput::make('reserved')
                    ->label(__('admin.inventory.reserved_quantity'))
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->required(),
                Select::make('status')
                    ->label(__('messages.status'))
                    ->options([
                        'active'       => __('messages.active'),
                        'inactive'     => __('messages.inactive'),
                        'discontinued' => __('messages.inactive'),
                    ])
                    ->default('active')
                    ->required(),
                Toggle::make('is_tracked')
                    ->label(__('messages.enabled'))
                    ->default(true)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('location.name')
                    ->label(__('messages.warehouse'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('warehouse_code')
                    ->label(__('messages.code'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('stock')
                    ->label(__('messages.quantity'))
                    ->sortable(),
                TextColumn::make('reserved')
                    ->label(__('admin.inventory.reserved_quantity'))
                    ->sortable(),
                TextColumn::make('available')
                    ->label(__('admin.inventory.available_quantity'))
                    ->getStateUsing(static fn (VariantInventory $record): int => max(0, (int) $record->stock - (int) $record->reserved))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->sortable()
                    ->label(__('messages.status'))
                    ->badge(),
                IconColumn::make('is_tracked')
                    ->sortable()
                    ->label(__('messages.enabled'))
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(fn (array $data): array => $this->normalizePayload($data))
                    ->using(fn (array $data): VariantInventory => $this->getOwnerRecord()->inventories()->create($data)),
            ])
            ->actions([
                EditAction::make()
                    ->mutateDataUsing(fn (array $data): array => $this->normalizePayload($data)),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizePayload(array $data): array
    {
        $locationId = isset($data['location_id']) ? (int) $data['location_id'] : 0;
        $stock = max(0, (int) ($data['stock'] ?? 0));
        $reserved = max(0, (int) ($data['reserved'] ?? 0));
        $reserved = min($reserved, $stock);

        $warehouseCode = is_string($data['warehouse_code'] ?? null)
            ? trim((string) $data['warehouse_code'])
            : '';

        if ($warehouseCode === '' && $locationId > 0) {
            $location = Location::query()->withoutGlobalScopes()->find($locationId);
            $warehouseCode = is_string($location?->code) && $location->code !== ''
                ? $location->code
                : 'WH-' . $locationId;
        }

        $status = is_string($data['status'] ?? null) ? trim((string) $data['status']) : 'active';
        if (! in_array($status, ['active', 'inactive', 'discontinued'], true)) {
            $status = 'active';
        }

        $data['location_id'] = $locationId > 0 ? $locationId : null;
        $data['warehouse_code'] = strtoupper($warehouseCode !== '' ? $warehouseCode : 'WH-DEFAULT');
        $data['stock'] = $stock;
        $data['reserved'] = $reserved;
        $data['available'] = max(0, $stock - $reserved);
        $data['status'] = $status;
        $data['is_tracked'] = (bool) ($data['is_tracked'] ?? true);

        return $data;
    }
}
