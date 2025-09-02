<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-ticket';

    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Coupon Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(255)
                            ->unique(Coupon::class, 'code', ignoreRecord: true)
                            ->uppercase(),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(1000)
                            ->rows(3),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Discount Settings')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->options([
                                'percentage' => 'Percentage',
                                'fixed' => 'Fixed Amount',
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\TextInput::make('value')
                            ->required()
                            ->numeric()
                            ->suffix(fn(Forms\Get $get) => $get('type') === 'percentage' ? '%' : '€'),
                        Forms\Components\TextInput::make('minimum_amount')
                            ->numeric()
                            ->prefix('€')
                            ->label('Minimum Order Amount'),
                        Forms\Components\TextInput::make('usage_limit')
                            ->numeric()
                            ->label('Usage Limit'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Validity Period')
                    ->schema([
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Start Date'),
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expiry Date'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'percentage' => 'success',
                        'fixed' => 'primary',
                    }),
                Tables\Columns\TextColumn::make('value')
                    ->formatStateUsing(fn(string $state, Coupon $record): string =>
                        $record->type === 'percentage' ? $state . '%' : '€' . $state),
                Tables\Columns\TextColumn::make('minimum_amount')
                    ->money('EUR')
                    ->placeholder('No minimum'),
                Tables\Columns\TextColumn::make('usage_limit')
                    ->placeholder('Unlimited'),
                Tables\Columns\TextColumn::make('used_count')
                    ->label('Times Used'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('No start date'),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('No expiry'),
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
                        'fixed' => 'Fixed Amount',
                    ]),
                Tables\Filters\Filter::make('active')
                    ->query(fn(Builder $query): Builder => $query->where('is_active', true)),
                Tables\Filters\Filter::make('valid_now')
                    ->query(fn(Builder $query): Builder => $query
                        ->where('is_active', true)
                        ->where(function (Builder $q) {
                            $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                        })
                        ->where(function (Builder $q) {
                            $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                        })),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'view' => Pages\ViewCoupon::route('/{record}'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
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
