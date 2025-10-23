<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountResource\Pages;
use App\Filament\Resources\DiscountResource\RelationManagers\CodesRelationManager;
use App\Filament\Resources\DiscountResource\RelationManagers\ConditionsRelationManager;
use App\Filament\Resources\DiscountResource\RelationManagers\RedemptionsRelationManager;
use App\Models\Discount;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Support\Concerns\HasNav;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

final class DiscountResource extends Resource
{
    use HasNav;

    private const TYPE_OPTIONS = [
        'percentage' => 'Percentage',
        'fixed' => 'Fixed Amount',
        'free_shipping' => 'Free Shipping',
        'bogo' => 'Buy One Get One',
    ];

    private const STATUS_OPTIONS = [
        'draft' => 'Draft',
        'active' => 'Active',
        'scheduled' => 'Scheduled',
        'expired' => 'Expired',
        'archived' => 'Archived',
    ];

    protected static ?string $model = Discount::class;

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-tag';
    }

    

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

    /**
     * Remove front-end visibility scopes so administrators can manage every discount state.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            ActiveScope::class,
            EnabledScope::class,
        ]);
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

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\SchemaSection::make('General Information')
                    ->schema([
                        Forms\Components\SchemaGrid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->afterStateUpdated(fn (string $context, $state, callable $set) => $context === 'create'
                                        ? $set('slug', Str::slug((string) $state))
                                        : null),
                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Discount::class, 'slug', ignoreRecord: true),
                            ]),
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\SchemaSection::make('Configuration')
                    ->schema([
                        Forms\Components\SchemaGrid::make(2)
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->label('Type')
                                    ->required()
                                    ->options(self::getTypeOptions())
                                    ->rules(['in:percentage,fixed,free_shipping']),
                                Forms\Components\TextInput::make('value')
                                    ->label('Value')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->step(0.01),
                            ]),
                        Forms\Components\SchemaGrid::make(3)
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->required()
                                    ->default('draft')
                                    ->options(self::getStatusOptions())
                                    ->rules(['in:draft,active,scheduled,expired']),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),
                                Forms\Components\Toggle::make('is_enabled')
                                    ->label('Enabled')
                                    ->default(true),
                            ]),
                    ]),
                Forms\Components\SchemaSection::make('Availability')
                    ->schema([
                        Forms\Components\SchemaGrid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('starts_at')
                                    ->label('Starts At')
                                    ->seconds(false),
                                Forms\Components\DateTimePicker::make('ends_at')
                                    ->label('Ends At')
                                    ->seconds(false),
                            ]),
                    ]),
                Forms\Components\SchemaSection::make('Usage Limits')
                    ->schema([
                        Forms\Components\SchemaGrid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('usage_limit')
                                    ->label('Usage Limit')
                                    ->numeric()
                                    ->minValue(1)
                                    ->nullable(),
                                Forms\Components\TextInput::make('minimum_amount')
                                    ->label('Minimum Order Amount')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->nullable(),
                            ]),
                        Forms\Components\SchemaGrid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('maximum_amount')
                                    ->label('Maximum Discount Amount')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->nullable(),
                                Forms\Components\TextInput::make('usage_count')
                                    ->label('Usage Count')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),
                    ]),
            ]);
    }


    public static function duplicateDiscount(Discount $discount): Discount
    {
        $newDiscount = $discount->replicate();

        $newDiscount->name = sprintf('%s (Copy)', $discount->name);
        $newDiscount->slug = self::generateDuplicateSlug($discount->slug, $discount->name);
        $newDiscount->status = 'draft';
        $newDiscount->usage_count = 0;

        $newDiscount->save();

        return $newDiscount;
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
