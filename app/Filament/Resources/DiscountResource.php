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
use UnitEnum;

final class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-tag';
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Discounts';
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

    public static function getRelations(): array
    {
        return [
            CodesRelationManager::class,
            ConditionsRelationManager::class,
            RedemptionsRelationManager::class,
        ];
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

        while (Discount::withoutGlobalScopes()->withTrashed()->where('slug', $candidate)->exists()) {
            $candidate = sprintf('%s-copy-%d', $baseSlug, $suffix);
            $suffix++;
        }

        return $candidate;
    }
}
