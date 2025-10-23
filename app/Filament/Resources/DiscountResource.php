<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\Concerns\HasNav;

use App\Filament\Resources\DiscountResource\Pages;
use App\Filament\Resources\DiscountResource\RelationManagers\CodesRelationManager;
use App\Filament\Resources\DiscountResource\RelationManagers\ConditionsRelationManager;
use App\Filament\Resources\DiscountResource\RelationManagers\RedemptionsRelationManager;
use App\Models\Discount;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use UnitEnum;

final class DiscountResource extends Resource
{
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

    /**
     * Build the Filament form configuration that powers the Discount CRUD view.
     * Keeping the schema close to the resource clarifies which inputs are required
     * for the automated tests while still matching Filament v4 conventions.
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('discounts.details_header') ?? 'Discount Details')
                    ->description(__('discounts.details_description') ?? 'Configure the core presentation details for this discount.')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('discounts.fields.name') ?? 'Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                                        // Automatically mirror the slug from the name when the editor has not touched the slug field.
                                        $currentSlug = $get('slug');

                                        if (! filled($currentSlug) && filled($state)) {
                                            $set('slug', Str::slug($state));
                                        }
                                    }),
                                Forms\Components\TextInput::make('slug')
                                    ->label(__('discounts.fields.slug') ?? 'Slug')
                                    ->helperText(__('discounts.fields.slug_helper') ?? 'Used for URLs and code references.')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Discount::class, 'slug', ignoreRecord: true)
                                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Str::slug($state) : null),
                                Forms\Components\Select::make('type')
                                    ->label(__('discounts.fields.type') ?? 'Type')
                                    ->options([
                                        'percentage' => __('discounts.types.percentage') ?? 'Percentage',
                                        'fixed'      => __('discounts.types.fixed') ?? 'Fixed amount',
                                    ])
                                    ->required()
                                    ->searchable(),
                                Forms\Components\TextInput::make('value')
                                    ->label(__('discounts.fields.value') ?? 'Value')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->helperText(__('discounts.fields.value_helper') ?? 'Use percentages (0-100) or a flat currency amount.'),
                                Forms\Components\Select::make('status')
                                    ->label(__('discounts.fields.status') ?? 'Status')
                                    ->options([
                                        'draft'     => __('discounts.statuses.draft') ?? 'Draft',
                                        'active'    => __('discounts.statuses.active') ?? 'Active',
                                        'scheduled' => __('discounts.statuses.scheduled') ?? 'Scheduled',
                                        'expired'   => __('discounts.statuses.expired') ?? 'Expired',
                                    ])
                                    ->required()
                                    ->default('draft'),
                                Forms\Components\Toggle::make('is_active')
                                    ->label(__('discounts.fields.is_active') ?? 'Active flag')
                                    ->default(true)
                                    ->inline(false),
                                Forms\Components\Toggle::make('is_enabled')
                                    ->label(__('discounts.fields.is_enabled') ?? 'Enabled flag')
                                    ->default(true)
                                    ->inline(false),
                            ])
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->label(__('discounts.fields.description') ?? 'Description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * Configure the table that backs the List page so tests can interact with
     * the filters, record actions, and bulk actions defined below.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('discounts.fields.name') ?? 'Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('discounts.fields.type') ?? 'Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'percentage' => __('discounts.types.percentage') ?? 'Percentage',
                        'fixed'      => __('discounts.types.fixed') ?? 'Fixed amount',
                        default      => ucfirst($state),
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label(__('discounts.fields.value') ?? 'Value')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('discounts.fields.status') ?? 'Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'    => 'success',
                        'scheduled' => 'warning',
                        'expired'   => 'danger',
                        default     => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('discounts.fields.is_active') ?? 'Active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('discounts.fields.is_enabled') ?? 'Enabled')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('discounts.fields.created_at') ?? 'Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('discounts.fields.type') ?? 'Type')
                    ->options([
                        'percentage' => __('discounts.types.percentage') ?? 'Percentage',
                        'fixed'      => __('discounts.types.fixed') ?? 'Fixed amount',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('discounts.fields.status') ?? 'Status')
                    ->options([
                        'draft'     => __('discounts.statuses.draft') ?? 'Draft',
                        'active'    => __('discounts.statuses.active') ?? 'Active',
                        'scheduled' => __('discounts.statuses.scheduled') ?? 'Scheduled',
                        'expired'   => __('discounts.statuses.expired') ?? 'Expired',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('duplicate')
                    ->label(__('discounts.actions.duplicate') ?? 'Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->requiresConfirmation()
                    ->action(function (Discount $record): void {
                        // Delegating to the helper keeps duplicate logic consistent between the table and the view page.
                        DiscountResource::duplicateDiscount($record);
                    })
                    ->successNotificationTitle(__('discounts.notifications.duplicated') ?? 'Discount duplicated successfully.'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label(__('discounts.bulk.activate') ?? 'Activate')
                        ->icon('heroicon-o-bolt')
                        ->requiresConfirmation()
                        ->action(function (EloquentCollection $records): void {
                            // Ensure each selected record is toggled on and marked active for downstream automation.
                            $records->each(function (Discount $discount): void {
                                $discount->update([
                                    'is_active' => true,
                                    'status'    => $discount->status === 'draft' ? 'active' : $discount->status,
                                ]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('deactivate')
                        ->label(__('discounts.bulk.deactivate') ?? 'Deactivate')
                        ->icon('heroicon-o-power')
                        ->requiresConfirmation()
                        ->action(function (EloquentCollection $records): void {
                            // Turning the flag off is enough for the feature tests; we keep the status in sync where practical.
                            $records->each(function (Discount $discount): void {
                                $discount->update([
                                    'is_active' => false,
                                    'status'    => $discount->status === 'active' ? 'draft' : $discount->status,
                                ]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General Information')
                    ->schema([
                        Forms\Components\Grid::make(2)
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
                Forms\Components\Section::make('Configuration')
                    ->schema([
                        Forms\Components\Grid::make(2)
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
                        Forms\Components\Grid::make(3)
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
                Forms\Components\Section::make('Availability')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('starts_at')
                                    ->label('Starts At')
                                    ->seconds(false),
                                Forms\Components\DateTimePicker::make('ends_at')
                                    ->label('Ends At')
                                    ->seconds(false),
                            ]),
                    ]),
                Forms\Components\Section::make('Usage Limits')
                    ->schema([
                        Forms\Components\Grid::make(2)
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
                        Forms\Components\Grid::make(2)
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Str::headline($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Value')
                    ->formatStateUsing(fn ($state, Discount $record): string => $record->type === 'percentage'
                        ? sprintf('%s%%', (float) $state)
                        : sprintf('€%0.2f', (float) $state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'scheduled' => 'warning',
                        'expired' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label('Enabled')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Starts At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Ends At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options(self::getTypeOptions()),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(self::getStatusOptions()),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (Discount $record): void {
                        self::duplicateDiscount($record);
                    })
                    ->requiresConfirmation(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-bolt')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(function (Discount $discount): void {
                                $discount->update([
                                    'is_active' => true,
                                    'status' => $discount->status === 'draft' ? 'active' : $discount->status,
                                ]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-pause')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(function (Discount $discount): void {
                                $discount->update([
                                    'is_active' => false,
                                    'status' => $discount->status === 'active' ? 'draft' : $discount->status,
                                ]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function duplicateDiscount(Discount $discount): Discount
    {
        $newDiscount = $discount->replicate();

        $newDiscount->name = sprintf('%s (Copy)', $discount->name);
        $newDiscount->slug = self::generateDuplicateSlug($discount->slug ?: $discount->name);
        $newDiscount->status = 'draft';
        $newDiscount->usage_count = 0;

        $newDiscount->save();

        return $newDiscount;
    }

    /**
     * Produce a collision-resistant slug for duplicated discounts while keeping the "-copy" suffix predictable.
     */
    private static function generateDuplicateSlug(string $name): string
    {
        $baseSlug = Str::slug($name) ?: 'discount';
        $candidate = $baseSlug . '-copy';
        $suffix = 2;

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
