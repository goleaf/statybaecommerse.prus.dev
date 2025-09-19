<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;

use App\Filament\Resources\DiscountResource\Pages;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\CustomerGroup;
use App\Models\Discount;
use App\Models\Product;
use App\Models\User;
use Filament\Schemas\Schema;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\BelongsToManyRelationManager;
use Filament\Resources\RelationManagers\HasManyRelationManager;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\TableWidget;
use Filament\Widgets\Widget;
use Filament\Forms;
use Filament\Infolists;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BackedEnum;

final class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    // protected static $navigationGroup = NavigationGroup::Marketing;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Discounts';

    protected static ?string $modelLabel = 'Discount';

    protected static ?string $pluralModelLabel = 'Discounts';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Tabs::make('Discount Details')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Basic Information')
                            ->schema([
                                Forms\Components\Section::make('Basic Information')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) =>
                                                $operation === 'create' ? $set('slug', \Str::slug($state)) : null),
                                        Forms\Components\TextInput::make('slug')
                                            ->maxLength(255)
                                            ->unique(Discount::class, 'slug', ignoreRecord: true)
                                            ->helperText('Leave empty to auto-generate from name'),
                                        Forms\Components\Textarea::make('description')
                                            ->columnSpanFull()
                                            ->rows(3),
                                        Forms\Components\Select::make('type')
                                            ->options([
                                                'percentage' => 'Percentage',
                                                'fixed' => 'Fixed Amount',
                                                'free_shipping' => 'Free Shipping',
                                            ])
                                            ->required()
                                            ->live(),
                                        Forms\Components\TextInput::make('value')
                                            ->numeric()
                                            ->minValue(0)
                                            ->step(0.01)
                                            ->visible(fn(Forms\Get $get): bool => $get('type') !== 'free_shipping')
                                            ->required(fn(Forms\Get $get): bool => $get('type') !== 'free_shipping'),
                                    ])
                                    ->columns(2),
                            ]),
                        Forms\Components\Tabs\Tab::make('Status & Dates')
                            ->schema([
                                Forms\Components\Section::make('Status & Dates')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_active')
                                            ->label('Active')
                                            ->default(true),
                                        Forms\Components\Toggle::make('is_enabled')
                                            ->label('Enabled')
                                            ->default(true),
                                        Forms\Components\Select::make('status')
                                            ->options([
                                                'draft' => 'Draft',
                                                'active' => 'Active',
                                                'inactive' => 'Inactive',
                                                'expired' => 'Expired',
                                            ])
                                            ->default('draft')
                                            ->required(),
                                        Forms\Components\DateTimePicker::make('starts_at')
                                            ->label('Starts At')
                                            ->helperText('Leave empty for immediate start'),
                                        Forms\Components\DateTimePicker::make('ends_at')
                                            ->label('Ends At')
                                            ->helperText('Leave empty for no expiration'),
                                    ])
                                    ->columns(2),
                            ]),
                        Forms\Components\Tabs\Tab::make('Usage Limits')
                            ->schema([
                                Forms\Components\Section::make('Usage Limits')
                                    ->schema([
                                        Forms\Components\TextInput::make('usage_limit')
                                            ->numeric()
                                            ->minValue(1)
                                            ->helperText('Total number of times this discount can be used'),
                                        Forms\Components\TextInput::make('per_customer_limit')
                                            ->numeric()
                                            ->minValue(1)
                                            ->helperText('Times per customer'),
                                        Forms\Components\TextInput::make('per_code_limit')
                                            ->numeric()
                                            ->minValue(1)
                                            ->helperText('Times per code'),
                                        Forms\Components\TextInput::make('per_day_limit')
                                            ->numeric()
                                            ->minValue(1)
                                            ->helperText('Times per day'),
                                        Forms\Components\TextInput::make('minimum_amount')
                                            ->numeric()
                                            ->minValue(0)
                                            ->step(0.01)
                                            ->helperText('Minimum order amount'),
                                        Forms\Components\TextInput::make('maximum_amount')
                                            ->numeric()
                                            ->minValue(0)
                                            ->step(0.01)
                                            ->helperText('Maximum order amount'),
                                    ])
                                    ->columns(2),
                            ]),
                        Forms\Components\Tabs\Tab::make('Advanced Settings')
                            ->schema([
                                Forms\Components\Section::make('Advanced Settings')
                                    ->schema([
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('name')
                                                    ->required()
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('code')
                                                    ->required()
                                                    ->maxLength(10),
                                            ]),
                                        Forms\Components\TextInput::make('priority')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Higher numbers = higher priority'),
                                        Forms\Components\Toggle::make('exclusive')
                                            ->label('Exclusive Discount')
                                            ->helperText('Cannot be combined with other discounts'),
                                        Forms\Components\Toggle::make('applies_to_shipping')
                                            ->label('Applies to Shipping'),
                                        Forms\Components\Toggle::make('free_shipping')
                                            ->label('Free Shipping'),
                                        Forms\Components\Toggle::make('first_order_only')
                                            ->label('First Order Only'),
                                    ])
                                    ->columns(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'primary' => 'percentage',
                        'success' => 'fixed',
                        'info' => 'free_shipping',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'percentage' => 'Percentage',
                        'fixed' => 'Fixed Amount',
                        'free_shipping' => 'Free Shipping',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('value')
                    ->label('Value')
                    ->formatStateUsing(fn($state, $record) =>
                        $record->type === 'percentage' ? $state . '%' : '€' . number_format($state, 2))
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'active',
                        'danger' => 'inactive',
                        'secondary' => 'expired',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'expired' => 'Expired',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('usage_count')
                    ->label('Used')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('usage_limit')
                    ->label('Limit')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed' => 'Fixed Amount',
                        'free_shipping' => 'Free Shipping',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'expired' => 'Expired',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Only'),
                Tables\Filters\TernaryFilter::make('is_enabled')
                    ->label('Enabled Only'),
                Tables\Filters\Filter::make('expired')
                    ->query(fn(Builder $query): Builder => $query->where('ends_at', '<', now()))
                    ->label('Expired'),
                Tables\Filters\Filter::make('scheduled')
                    ->query(fn(Builder $query): Builder => $query->where('starts_at', '>', now()))
                    ->label('Scheduled'),
                Tables\Filters\Filter::make('exclusive')
                    ->query(fn(Builder $query): Builder => $query->where('exclusive', true))
                    ->label('Exclusive Only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (Discount $record) {
                        $newDiscount = $record->replicate();
                        $newDiscount->name = $record->name . ' (Copy)';
                        $newDiscount->slug = $record->slug . '-copy';
                        $newDiscount->status = 'draft';
                        $newDiscount->usage_count = 0;
                        $newDiscount->save();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn(Collection $records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn(Collection $records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Discount Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Name'),
                        Infolists\Components\TextEntry::make('slug')
                            ->label('Slug'),
                        Infolists\Components\TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('type')
                            ->label('Type')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'percentage' => 'primary',
                                'fixed' => 'success',
                                'free_shipping' => 'info',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('value')
                            ->label('Value')
                            ->formatStateUsing(fn($state, $record) =>
                                $record->type === 'percentage' ? $state . '%' : '€' . number_format($state, 2)),
                    ])
                    ->columns(2),
                Infolists\Components\Section::make('Status & Usage')
                    ->schema([
                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean(),
                        Infolists\Components\IconEntry::make('is_enabled')
                            ->label('Enabled')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'draft' => 'warning',
                                'active' => 'success',
                                'inactive' => 'danger',
                                'expired' => 'secondary',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('usage_count')
                            ->label('Times Used'),
                        Infolists\Components\TextEntry::make('usage_limit')
                            ->label('Usage Limit'),
                        Infolists\Components\TextEntry::make('starts_at')
                            ->label('Starts At')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('ends_at')
                            ->label('Ends At')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CodesRelationManager::class,
            RelationManagers\ConditionsRelationManager::class,
            RelationManagers\RedemptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecords::route('/'),
            'create' => Pages\CreateRecord::route('/create'),
            'view' => Pages\ViewRecord::route('/{record}'),
            'edit' => Pages\EditRecord::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug', 'description'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Type' => $record->type,
            'Value' => $record->type === 'percentage' ? $record->value . '%' : '€' . number_format($record->value, 2),
            'Status' => $record->status,
        ];
    }
}
