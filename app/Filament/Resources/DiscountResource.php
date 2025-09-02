<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountResource\Pages;
use App\Models\Discount;
use App\Models\Zone;
use App\Models\Channel;
use App\Models\Currency;
use App\Models\CustomerGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BackedEnum;
use UnitEnum;

final class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make('Discount Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(1000)
                            ->rows(3),
                        Forms\Components\Select::make('type')
                            ->options([
                                'percentage' => 'Percentage',
                                'fixed_amount' => 'Fixed Amount',
                                'buy_x_get_y' => 'Buy X Get Y',
                                'free_shipping' => 'Free Shipping',
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\TextInput::make('value')
                            ->numeric()
                            ->required()
                            ->step(0.01)
                            ->suffix(fn(Forms\Get $get): string => $get('type') === 'percentage' ? '%' : '€'),
                        Forms\Components\TextInput::make('priority')
                            ->numeric()
                            ->default(0)
                            ->helperText('Higher numbers = higher priority'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Availability')
                    ->schema([
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->required(),
                        Forms\Components\DateTimePicker::make('ends_at')
                            ->after('starts_at'),
                        Forms\Components\TextInput::make('usage_limit')
                            ->numeric()
                            ->minValue(1)
                            ->helperText('Leave empty for unlimited usage'),
                        Forms\Components\TextInput::make('usage_limit_per_customer')
                            ->numeric()
                            ->minValue(1)
                            ->helperText('Maximum uses per customer'),
                        Forms\Components\Toggle::make('is_enabled')
                            ->label('Enabled')
                            ->default(true),
                        Forms\Components\Toggle::make('is_exclusive')
                            ->label('Exclusive')
                            ->helperText('Cannot be combined with other discounts'),
                    ])
                    ->columns(3),
                Forms\Components\Section::make('Conditions & Requirements')
                    ->schema([
                        Forms\Components\TextInput::make('minimum_amount')
                            ->label('Minimum Order Amount')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                        Forms\Components\TextInput::make('minimum_quantity')
                            ->label('Minimum Quantity')
                            ->numeric()
                            ->minValue(1),
                        Forms\Components\Select::make('zones')
                            ->relationship('zones', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Leave empty to apply to all zones'),
                        Forms\Components\Select::make('channels')
                            ->relationship('channels', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Leave empty to apply to all channels'),
                        Forms\Components\Select::make('currencies')
                            ->relationship('currencies', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Leave empty to apply to all currencies'),
                        Forms\Components\Select::make('customerGroups')
                            ->relationship('customerGroups', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Leave empty to apply to all customers'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'percentage' => 'success',
                        'fixed_amount' => 'primary',
                        'buy_x_get_y' => 'warning',
                        'free_shipping' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('value')
                    ->suffix(fn($record): string => $record->type === 'percentage' ? '%' : '€')
                    ->sortable(),
                Tables\Columns\TextColumn::make('priority')
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable()
                    ->color(fn($state): string => $state && $state->isPast() ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('usage_count')
                    ->label('Used')
                    ->sortable(),
                Tables\Columns\TextColumn::make('usage_limit')
                    ->label('Limit')
                    ->placeholder('Unlimited')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_exclusive')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed_amount' => 'Fixed Amount',
                        'buy_x_get_y' => 'Buy X Get Y',
                        'free_shipping' => 'Free Shipping',
                    ]),
                Tables\Filters\Filter::make('active')
                    ->query(fn(Builder $query): Builder => $query->active())
                    ->label('Active Only'),
                Tables\Filters\Filter::make('enabled')
                    ->query(fn(Builder $query): Builder => $query->where('is_enabled', true))
                    ->label('Enabled Only'),
                Tables\Filters\Filter::make('exclusive')
                    ->query(fn(Builder $query): Builder => $query->where('is_exclusive', true))
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
                        $newDiscount->save();
                        return redirect(DiscountResource::getUrl('edit', ['record' => $newDiscount]));
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('priority', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
