<?php

declare(strict_types=1);

namespace App\Filament\Resources\CouponResource\RelationManagers;

use App\Forms\Components\Flatpickr;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Filament\Forms\Components\Flatpickr;

final class UsagesRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'usages';

    protected static ?string $title = 'Coupon Usage History';

    protected static ?string $modelLabel = 'Usage';

    protected static ?string $pluralModelLabel = 'Usages';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('user_id')
                ->label(__('admin.common.user'))
                ->relationship('user', 'name')
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('order_id')
                ->label(__('admin.orders.title'))
                ->relationship('order', 'number')
                ->searchable()
                ->preload(),
            Forms\Components\TextInput::make('discount_amount')
                ->label(__('admin.coupons.additional_fields.discount_amount'))
                ->numeric()
                ->prefix('€')
                ->required(),
            Flatpickr::make('used_at')->asDateTime()
                ->label(__('admin.coupons.additional_fields.used_at'))
                ->required()
                ->default(now()),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('used_at')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('admin.common.user'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('order.number')
                    ->label(__('admin.orders.title'))
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => $record->order ? route('filament.admin.resources.orders.view', $record->order) : null)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('discount_amount')
                    ->label(__('admin.coupons.additional_fields.discount_amount'))
                    ->money('EUR')
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('used_at')
                    ->label(__('admin.coupons.additional_fields.used_at'))
                    ->dateTime()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.coupons.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('used_at')
                    ->form([
                        Flatpickr::make('used_from')->asDate()
                            ->label(__('admin.coupons.additional_fields.used_from')),
                        Flatpickr::make('used_until')->asDate()
                            ->label(__('admin.coupons.additional_fields.used_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['used_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('used_at', '>=', $date),
                            )
                            ->when(
                                $data['used_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('used_at', '<=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('has_user')
                    ->label(__('admin.coupons.additional_fields.has_user'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('user_id')),
                Tables\Filters\Filter::make('has_order')
                    ->label(__('admin.coupons.additional_fields.has_order'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('order_id')),
            ])
            ->headerActions([
                RelationManagerRepeaterAction::make(),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('used_at', 'desc')
            ->emptyStateHeading(__('admin.coupons.empty_state.heading'))
            ->emptyStateDescription(__('admin.coupons.empty_state.description'))
            ->emptyStateIcon('heroicon-o-ticket');
    }
}
