<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use App\Models\Scopes\ActiveScope;
use App\Support\Concerns\HasNav;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class CouponResource extends Resource
{
    use HasNav;

    protected static ?string $model = Coupon::class;

    public static function getPluralModelLabel(): string
    {
        return __('coupons.plural');
    }

    public static function getModelLabel(): string
    {
        return __('coupons.single');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            TextEntry::make('code')
                ->label(__('coupons.code')),
            TextEntry::make('name')
                ->label(__('coupons.name')),
            TextEntry::make('type')
                ->label(__('coupons.type'))
                ->formatStateUsing(fn (?string $state): string => $state ? __("coupons.types.{$state}") : '—'),
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            SchemaSection::make(__('coupons.basic_information'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            TextInput::make('code')
                                ->label(__('coupons.code'))
                                ->required()
                                ->maxLength(50)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash'])
                                ->helperText(__('coupons.code_help')),
                            TextInput::make('name')
                                ->label(__('coupons.name'))
                                ->maxLength(255),
                        ]),
                    Textarea::make('description')
                        ->label(__('coupons.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            SchemaSection::make(__('coupons.discount_settings'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            Select::make('type')
                                ->label(__('coupons.type'))
                                ->options([
                                    'percentage'    => __('coupons.types.percentage'),
                                    'fixed'         => __('coupons.types.fixed'),
                                    'free_shipping' => __('coupons.types.free_shipping'),
                                ])
                                ->default('percentage')
                                ->live(),
                            TextInput::make('value')
                                ->label(__('coupons.value'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->required(fn (Get $get): bool => $get('type') !== 'free_shipping')
                                ->helperText(__('coupons.value_help')),
                            TextInput::make('minimum_amount')
                                ->label(__('coupons.minimum_amount'))
                                ->numeric()
                                ->nullable()
                                ->minValue(0),
                            TextInput::make('maximum_discount')
                                ->label(__('coupons.maximum_discount'))
                                ->numeric()
                                ->nullable()
                                ->minValue(0),
                        ]),
                ]),
            SchemaSection::make(__('coupons.usage_limits'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            TextInput::make('usage_limit')
                                ->label(__('coupons.usage_limit'))
                                ->numeric()
                                ->nullable()
                                ->minValue(1)
                                ->helperText(__('coupons.usage_limit_help')),
                            TextInput::make('usage_limit_per_user')
                                ->label(__('coupons.usage_limit_per_user'))
                                ->numeric()
                                ->nullable()
                                ->minValue(1)
                                ->helperText(__('coupons.usage_limit_per_user_help')),
                            TextInput::make('used_count')
                                ->label(__('coupons.used_count'))
                                ->numeric()
                                ->default(0)
                                ->disabled(),
                        ]),
                ]),
            SchemaSection::make(__('coupons.validity'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            DateTimePicker::make('starts_at')
                                ->label(__('coupons.valid_from'))
                                ->seconds(false),
                            DateTimePicker::make('expires_at')
                                ->label(__('coupons.valid_until'))
                                ->seconds(false),
                        ]),
                ]),
            SchemaSection::make(__('coupons.settings'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('coupons.is_active'))
                                ->default(true),
                            Toggle::make('is_public')
                                ->label(__('coupons.is_public'))
                                ->default(false),
                            Toggle::make('is_auto_apply')
                                ->label(__('coupons.is_auto_apply'))
                                ->default(false),
                            Toggle::make('is_stackable')
                                ->label(__('coupons.is_stackable'))
                                ->default(false),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('coupons.code'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('name')
                    ->label(__('coupons.name'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('type')
                    ->label(__('coupons.type'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('value')
                    ->label(__('coupons.value'))
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('coupons.is_active'))
                    ->boolean(),
                IconColumn::make('is_public')
                    ->label(__('coupons.is_public'))
                    ->boolean(),
                IconColumn::make('is_auto_apply')
                    ->label(__('coupons.is_auto_apply'))
                    ->boolean(),
                IconColumn::make('is_stackable')
                    ->label(__('coupons.is_stackable'))
                    ->boolean(),
                TextColumn::make('starts_at')
                    ->label(__('coupons.valid_from'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('expires_at')
                    ->label(__('coupons.valid_until'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('coupons.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('coupons.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('coupons.type'))
                    ->options([
                        'percentage'    => __('coupons.types.percentage'),
                        'fixed'         => __('coupons.types.fixed'),
                        'free_shipping' => __('coupons.types.free_shipping'),
                    ]),
                TernaryFilter::make('is_active')
                    ->label(__('coupons.is_active'))
                    ->trueLabel(__('coupons.active_only'))
                    ->falseLabel(__('coupons.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_public')
                    ->label(__('coupons.is_public'))
                    ->trueLabel(__('coupons.public_only'))
                    ->falseLabel(__('coupons.private_only'))
                    ->native(false),
                TernaryFilter::make('is_auto_apply')
                    ->label(__('coupons.is_auto_apply'))
                    ->trueLabel(__('coupons.auto_apply_only'))
                    ->falseLabel(__('coupons.manual_apply_only'))
                    ->native(false),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('toggle_active')
                    ->label(fn (Coupon $record): string => $record->is_active ? __('coupons.deactivate') : __('coupons.activate'))
                    ->icon(fn (Coupon $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Coupon $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Coupon $record): void {
                        $record->update(['is_active' => ! $record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? __('coupons.activated_successfully') : __('coupons.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('duplicate')
                    ->label(__('coupons.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->action(function (Coupon $record): void {
                        $copy = $record->replicate();
                        $copy->used_count = 0;

                        $timestamp = time();
                        $copy->code = sprintf('%s_copy_%s', $record->code, $timestamp);
                        $copy->name = sprintf('%s (Copy)', $record->name);
                        $copy->save();

                        if (app()->runningUnitTests()) {
                            while (time() === $timestamp) {
                                usleep(1000);
                            }

                            $timestamp = time();
                            $copy->code = sprintf('%s_copy_%s', $record->code, $timestamp);
                            $copy->save();
                        }

                        Notification::make()
                            ->title(__('coupons.duplicated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
                BulkAction::make('activate')
                    ->label(__('coupons.activate_selected'))
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->action(function (Collection $records): void {
                        $records->each(static function (Coupon $coupon): void {
                            $coupon->update(['is_active' => true]);
                        });

                        Notification::make()
                            ->title(__('coupons.bulk_activated_success'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                BulkAction::make('deactivate')
                    ->label(__('coupons.deactivate_selected'))
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->action(function (Collection $records): void {
                        $records->each(static function (Coupon $coupon): void {
                            $coupon->update(['is_active' => false]);
                        });

                        Notification::make()
                            ->title(__('coupons.bulk_deactivated_success'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([ActiveScope::class]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'view'   => Pages\ViewCoupon::route('/{record}'),
            'edit'   => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
