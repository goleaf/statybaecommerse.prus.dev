<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountRedemptionResource\Pages;
use App\Filament\Resources\DiscountRedemptionResource\RelationManagers\CodeRelationManager;
use App\Filament\Resources\DiscountRedemptionResource\RelationManagers\DiscountRelationManager;
use App\Filament\Resources\DiscountRedemptionResource\RelationManagers\UserRelationManager;
use App\Models\DiscountRedemption;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class DiscountRedemptionResource extends Resource
{
    protected static ?string $model = DiscountRedemption::class;

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-receipt-refund';
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Discounts';
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.discount_redemptions.plural');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.discount_redemptions.plural');
    }

    public static function getModelLabel(): string
    {
        return __('admin.discount_redemptions.single');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('admin.discount_redemptions.form.sections.basic_information'))
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('discount_id')
                        ->label('Discount')
                        ->relationship('discount', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('code_id')
                        ->label(__('admin.discount_redemptions.form.fields.discount_code'))
                        ->relationship('code', 'code')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('user_id')
                        ->label(__('admin.discount_redemptions.form.fields.user'))
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('order_id')
                        ->label(__('admin.discount_redemptions.form.fields.order'))
                        ->relationship('order', 'number')
                        ->searchable()
                        ->preload()
                        ->nullable(),
                    Forms\Components\TextInput::make('amount_saved')
                        ->label(__('admin.discount_redemptions.form.fields.discount_amount'))
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->step(0.01),
                    Forms\Components\TextInput::make('currency_code')
                        ->label('Currency')
                        ->maxLength(3)
                        ->default('EUR')
                        ->required(),
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'pending' => 'Pending',
                            'redeemed' => 'Redeemed',
                            'cancelled' => 'Cancelled',
                            'refunded' => 'Refunded',
                            'expired' => 'Expired',
                        ])
                        ->default('pending'),
                    Forms\Components\DateTimePicker::make('redeemed_at')
                        ->label(__('admin.discount_redemptions.form.fields.redeemed_at'))
                        ->default(now())
                        ->required(),
                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->columnSpanFull(),
                    Forms\Components\KeyValue::make('metadata')
                        ->label('Metadata')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('ip_address')
                        ->label('IP Address')
                        ->maxLength(45)
                        ->columnSpan(1),
                    Forms\Components\TextInput::make('user_agent')
                        ->label('User Agent')
                        ->columnSpan(1),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('discount.name')
                    ->label('Discount')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code.code')
                    ->label(__('admin.discount_redemptions.table.discount_code'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('admin.discount_redemptions.table.user'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order.number')
                    ->label(__('admin.discount_redemptions.table.order'))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('amount_saved')
                    ->label(__('admin.discount_redemptions.table.discount_amount'))
                    ->sortable()
                    ->formatStateUsing(fn ($state, DiscountRedemption $record): string => $state === null
                        ? '-' : number_format((float) $state, 2).' '.($record->currency_code ?? 'EUR')),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'redeemed',
                        'secondary' => 'refunded',
                        'danger' => 'cancelled',
                        'gray' => 'expired',
                    ]),
                Tables\Columns\TextColumn::make('redeemed_at')
                    ->label(__('admin.discount_redemptions.table.redeemed_at'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.discount_redemptions.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('discount_id')
                    ->label('Discount')
                    ->relationship('discount', 'name'),
                Tables\Filters\SelectFilter::make('code_id')
                    ->label(__('admin.discount_redemptions.filters.discount_code'))
                    ->relationship('code', 'code'),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label(__('admin.discount_redemptions.filters.user'))
                    ->relationship('user', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'redeemed' => 'Redeemed',
                        'cancelled' => 'Cancelled',
                        'refunded' => 'Refunded',
                        'expired' => 'Expired',
                    ]),
                Tables\Filters\Filter::make('redeemed_between')
                    ->label(__('admin.discount_redemptions.filters.redeemed_at'))
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('redeemed_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('redeemed_at', '<=', $date));
                    }),
                Tables\Filters\Filter::make('recent')
                    ->label(__('admin.discount_redemptions.filters.recent'))
                    ->query(fn (Builder $query): Builder => $query->where('redeemed_at', '>=', now()->subDays(7))),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('redeemed_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            DiscountRelationManager::class,
            CodeRelationManager::class,
            UserRelationManager::class,
        ];
    }

    /**
     * @return array<string, array{route: string}>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiscountRedemptions::route('/'),
            'create' => Pages\CreateDiscountRedemption::route('/create'),
            'view' => Pages\ViewDiscountRedemption::route('/{record}'),
            'edit' => Pages\EditDiscountRedemption::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['discount', 'code', 'user', 'order']);
    }
}
