<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\Concerns\HasNav;

use App\Filament\Resources\DiscountResource\Pages;
use App\Filament\Resources\DiscountResource\RelationManagers\CodesRelationManager;
use App\Filament\Resources\DiscountResource\RelationManagers\ConditionsRelationManager;
use App\Filament\Resources\DiscountResource\RelationManagers\RedemptionsRelationManager;
use App\Models\Discount;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Throwable;
use UnitEnum;

final class DiscountResource extends Resource
{
    use HasNav;

    protected static ?string $model = Discount::class;

    

    

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('discounts.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('discounts.plural');
    }

    public static function getModelLabel(): string
    {
        return __('discounts.single');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDiscounts::route('/'),
            'create' => Pages\CreateDiscount::route('/create'),
            'view'   => Pages\ViewDiscount::route('/{record}'),
            'edit'   => Pages\EditDiscount::route('/{record}/edit'),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('translations.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('slug')
                    ->label(__('translations.slug'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                BadgeColumn::make('type')
                    ->label(__('translations.type'))
                    ->colors([
                        'percentage' => 'success',
                        'fixed' => 'info',
                        'free_shipping' => 'primary',
                        'buy_one_get_one' => 'warning',
                        'bogo' => 'warning',
                    ])
                    ->formatStateUsing(fn (?string $state): string => self::getTypeLabel($state))
                    ->sortable()
                    ->searchable(),
                BadgeColumn::make('status')
                    ->label(__('translations.status'))
                    ->colors([
                        'active' => 'success',
                        'draft' => 'gray',
                        'scheduled' => 'warning',
                        'expired' => 'danger',
                        'paused' => 'warning',
                    ])
                    ->formatStateUsing(fn (?string $state): string => self::getStatusLabel($state))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('value')
                    ->label(__('translations.value'))
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('translations.active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_enabled')
                    ->label(__('translations.enabled'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('usage_count')
                    ->label(__('translations.usage_count'))
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label(__('translations.starts_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ends_at')
                    ->label(__('translations.ends_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('translations.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label(__('translations.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('translations.type'))
                    ->options(self::getTypeOptions())
                    ->searchable(),
                SelectFilter::make('status')
                    ->label(__('translations.status'))
                    ->options(self::getStatusOptions())
                    ->searchable(),
                TernaryFilter::make('is_active')
                    ->label(__('translations.active'))
                    ->boolean(),
                TernaryFilter::make('is_enabled')
                    ->label(__('translations.enabled'))
                    ->boolean(),
                Filter::make('expired')
                    ->label(self::translateWithFallback('discounts.filters.expired', 'Expired'))
                    ->query(function (Builder $query): Builder {
                        return $query->where(function (Builder $query): void {
                            $query->where('status', 'expired')
                                ->orWhere(function (Builder $query): void {
                                    $query->whereNotNull('ends_at')
                                        ->where('ends_at', '<', now());
                                });
                        });
                    }),
                Filter::make('scheduled')
                    ->label(self::translateWithFallback('discounts.filters.scheduled', 'Scheduled'))
                    ->query(function (Builder $query): Builder {
                        return $query->where(function (Builder $query): void {
                            $query->where('status', 'scheduled')
                                ->orWhere(function (Builder $query): void {
                                    $query->whereNotNull('starts_at')
                                        ->where('starts_at', '>', now());
                                });
                        });
                    }),
                Filter::make('exclusive')
                    ->label(self::translateWithFallback('discounts.filters.exclusive', 'Exclusive'))
                    ->query(fn (Builder $query): Builder => $query->where('exclusive', true)),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('duplicate')
                    ->label(self::translateWithFallback(
                        'discounts.actions.duplicate',
                        self::translateWithFallback('translations.duplicate', 'Duplicate')
                    ))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (Discount $record): ?RedirectResponse {
                        try {
                            $duplicate = $record->replicate();
                            $duplicate->name = self::generateDuplicateName($record->name);
                            $duplicate->status = 'draft';
                            $duplicate->usage_count = 0;
                            $duplicate->is_active = false;
                            $duplicate->slug = self::generateDuplicateSlug($record->slug, $record->name);
                            $duplicate->save();

                            Notification::make()
                                ->title(__('translations.duplicated_successfully'))
                                ->success()
                                ->send();

                            return redirect(static::getUrl('edit', ['record' => $duplicate]));
                        } catch (Throwable $exception) {
                            report($exception);

                            Notification::make()
                                ->title(__('translations.error'))
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }

                        return null;
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('translations.activate'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);

                            Notification::make()
                                ->title(__('translations.activated_successfully'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('deactivate')
                        ->label(__('translations.deactivate'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);

                            Notification::make()
                                ->title(__('translations.deactivated_successfully'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            CodesRelationManager::class,
            ConditionsRelationManager::class,
            RedemptionsRelationManager::class,
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function getTypeOptions(): array
    {
        return [
            'percentage' => self::translateWithFallback('translations.percentage', 'Percentage'),
            'fixed' => self::translateWithFallback('translations.fixed_amount', 'Fixed Amount'),
            'free_shipping' => self::translateWithFallback('translations.free_shipping', 'Free Shipping'),
            'buy_one_get_one' => self::translateWithFallback('translations.buy_one_get_one', 'Buy One Get One'),
            'bogo' => self::translateWithFallback('translations.buy_one_get_one', 'Buy One Get One'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function getStatusOptions(): array
    {
        return [
            'draft' => self::translateWithFallback('translations.draft', 'Draft'),
            'active' => self::translateWithFallback('translations.active', 'Active'),
            'scheduled' => self::translateWithFallback('translations.scheduled', 'Scheduled'),
            'expired' => self::translateWithFallback('translations.expired', 'Expired'),
            'paused' => self::translateWithFallback('translations.paused', 'Paused'),
        ];
    }

    private static function getTypeLabel(?string $type): string
    {
        if (! $type) {
            return self::translateWithFallback('translations.unknown', 'Unknown');
        }

        return self::getTypeOptions()[$type] ?? Str::headline($type);
    }

    private static function getStatusLabel(?string $status): string
    {
        if (! $status) {
            return self::translateWithFallback('translations.unknown', 'Unknown');
        }

        return self::getStatusOptions()[$status] ?? Str::headline($status);
    }

    private static function generateDuplicateName(string $name): string
    {
        return trim($name.' (Copy)');
    }

    private static function generateDuplicateSlug(?string $slug, string $name): ?string
    {
        $base = $slug ?: Str::slug($name);

        if ($base === '') {
            return null;
        }

        $candidate = $base.'-copy';
        $counter = 2;

        while (Discount::where('slug', $candidate)->exists()) {
            $candidate = $base.'-copy-'.$counter;
            $counter++;
        }

        return $candidate;
    }

    private static function translateWithFallback(string $key, string $fallback): string
    {
        $translation = __($key);

        return $translation === $key ? $fallback : $translation;
    }
}
