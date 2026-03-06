<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\RelationManagers\Concerns\ResolvesOwnerPageRedirect;
use App\Filament\Resources\CouponUsageResource;
use App\Filament\Resources\UserResource;
use App\Models\CouponUsage;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CouponUsagesRelationManager extends RelationManager
{
    use ResolvesOwnerPageRedirect;

    protected static string $relationship = 'couponUsages';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('coupon_id')
                    ->relationship(
                        name: 'coupon',
                        titleAttribute: 'code',
                        modifyQueryUsing: static fn (Builder $query): Builder => $query
                            ->withoutGlobalScopes()
                            ->orderBy('code'),
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('order_id')
                    ->relationship(
                        name: 'order',
                        titleAttribute: 'number',
                        modifyQueryUsing: fn (Builder $query): Builder => $query
                            ->withoutGlobalScopes()
                            ->where('user_id', $this->getOwnerRecord()->getKey())
                            ->latest('id'),
                    )
                    ->searchable()
                    ->preload()
                    ->nullable(),
                TextInput::make('discount_amount')
                    ->numeric()
                    ->prefix('€')
                    ->required(),
                DateTimePicker::make('used_at')
                    ->default(now()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(static fn (Builder $query): Builder => $query->withoutGlobalScopes())
            ->recordTitleAttribute('discount_amount')
            ->columns([
                TextColumn::make('coupon.code')
                    ->label(__('admin.labels.coupon'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order.number')
                    ->label(__('admin.labels.order'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('discount_amount')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('used_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('create')
                    ->icon('heroicon-m-plus')
                    ->url(fn (): string => CouponUsageResource::getUrl('create', [
                        'user_id'  => $this->getOwnerRecord()->getKey(),
                        'redirect' => $this->resolveOwnerPageRedirectUrl(UserResource::class),
                    ])),
            ])
            ->recordActions([
                Action::make('view')
                    ->icon('heroicon-m-eye')
                    ->url(fn (CouponUsage $record): string => CouponUsageResource::getUrl('view', [
                        'record'   => $record,
                        'redirect' => $this->resolveOwnerPageRedirectUrl(UserResource::class),
                    ])),
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn (CouponUsage $record): string => CouponUsageResource::getUrl('edit', [
                        'record'   => $record,
                        'redirect' => $this->resolveOwnerPageRedirectUrl(UserResource::class),
                    ])),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeUsagePayload(array $data): array
    {
        $data['coupon_id'] = (int) ($data['coupon_id'] ?? 0);
        $data['user_id'] = $this->getOwnerRecord()->getKey();
        $data['order_id'] = is_numeric($data['order_id'] ?? null)
            ? (int) $data['order_id']
            : null;
        $data['discount_amount'] = is_numeric($data['discount_amount'] ?? null)
            ? round((float) $data['discount_amount'], 2)
            : 0.0;
        $data['used_at'] = $data['used_at'] ?? now();

        return $data;
    }
}
