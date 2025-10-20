<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountResource\Pages;
use App\Filament\Resources\DiscountResource\RelationManagers\CodesRelationManager;
use App\Filament\Resources\DiscountResource\RelationManagers\ConditionsRelationManager;
use App\Filament\Resources\DiscountResource\RelationManagers\RedemptionsRelationManager;
use App\Models\Discount;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiscounts::route('/'),
            'create' => Pages\CreateDiscount::route('/create'),
            'view' => Pages\ViewDiscount::route('/{record}'),
            'edit' => Pages\EditDiscount::route('/{record}/edit'),
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
        $newDiscount->slug = self::generateDuplicateSlug($discount);
        $newDiscount->status = 'draft';
        $newDiscount->usage_count = 0;

        $newDiscount->save();

        return $newDiscount;
    }

    /**
     * @return array<string, string>
     */
    private static function getTypeOptions(): array
    {
        return self::formatOptions(['percentage', 'fixed', 'free_shipping']);
    }

    /**
     * @return array<string, string>
     */
    private static function getStatusOptions(): array
    {
        return self::formatOptions(['draft', 'active', 'scheduled', 'expired']);
    }

    /**
     * @param  array<int, string>  $values
     * @return array<string, string>
     */
    private static function formatOptions(array $values): array
    {
        return collect($values)
            ->mapWithKeys(fn (string $value): array => [$value => Str::headline($value)])
            ->all();
    }

    private static function generateDuplicateSlug(Discount $discount): string
    {
        $baseSlug = $discount->slug ?: Str::slug($discount->name) ?: 'discount';
        $candidate = $baseSlug.'-copy';
        $suffix = 2;

        while (Discount::withoutGlobalScopes()->withTrashed()->where('slug', $candidate)->exists()) {
            $candidate = sprintf('%s-copy-%d', $baseSlug, $suffix);
            $suffix++;
        }

        return $candidate;
    }
}
