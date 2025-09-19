<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountRedemptionResource\Pages;
use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\DiscountRedemption;
use App\Models\Order;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Components\Tab;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Infolists;
use Filament\Tables;
use BackedEnum;
use UnitEnum;

final class DiscountRedemptionResource extends Resource
{
    protected static ?string $model = DiscountRedemption::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-ticket';
    /** @var UnitEnum|string|null */
    protected static string|UnitEnum|null $navigationGroup = "Marketing";
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Redemption Details')
                    ->schema([
                        Forms\Components\Select::make('discount_id')
                            ->relationship('discount', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Select::make('code_id')
                            ->relationship('code', 'code')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('code')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('discount_id')
                                    ->relationship('discount', 'name')
                                    ->required(),
                            ]),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('order_id')
                            ->relationship('order', 'id')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Financial Information')
                    ->schema([
                        Forms\Components\TextInput::make('amount_saved')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('€')
                            ->required(),
                        Forms\Components\Select::make('currency_code')
                            ->options([
                                'EUR' => 'EUR (€)',
                                'USD' => 'USD ($)',
                                'GBP' => 'GBP (£)',
                            ])
                            ->default('EUR')
                            ->required(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Status & Timing')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'redeemed' => 'Redeemed',
                                'expired' => 'Expired',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->required(),
                        Forms\Components\DateTimePicker::make('redeemed_at')
                            ->required()
                            ->default(now()),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull()
                            ->rows(3),
                        Forms\Components\TextInput::make('ip_address')
                            ->label('IP Address')
                            ->maxLength(45),
                        Forms\Components\TextInput::make('user_agent')
                            ->label('User Agent')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Audit Information')
                    ->schema([
                        Forms\Components\Select::make('created_by')
                            ->relationship('creator', 'name')
                            ->searchable()
                            ->disabled(),
                        Forms\Components\Select::make('updated_by')
                            ->relationship('updater', 'name')
                            ->searchable()
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('discount.name')
                    ->label('Discount')
                    ->sortable()
                    ->searchable()
                    ->weight(FontWeight::Bold),
                Tables\Columns\TextColumn::make('code.code')
                    ->label('Code')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('order.id')
                    ->label('Order')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('amount_saved')
                    ->label('Amount Saved')
                    ->money('EUR')
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->color('success'),
                Tables\Columns\TextColumn::make('currency_code')
                    ->label('Currency')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'redeemed',
                        'danger' => 'expired',
                        'secondary' => 'cancelled',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('redeemed_at')
                    ->label('Redeemed At')
                    ->dateTime()
                    ->sortable()
                    ->since(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'redeemed' => 'Redeemed',
                        'expired' => 'Expired',
                        'cancelled' => 'Cancelled',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('discount_id')
                    ->relationship('discount', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('currency_code')
                    ->options([
                        'EUR' => 'EUR',
                        'USD' => 'USD',
                        'GBP' => 'GBP',
                    ])
                    ->multiple(),
                Tables\Filters\Filter::make('redeemed_at')
                    ->form([
                        Forms\Components\DatePicker::make('redeemed_from')
                            ->label('Redeemed From'),
                        Forms\Components\DatePicker::make('redeemed_until')
                            ->label('Redeemed Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['redeemed_from'],
                                fn($query, $date) => $query->whereDate('redeemed_at', '>=', $date),
                            )
                            ->when(
                                $data['redeemed_until'],
                                fn($query, $date) => $query->whereDate('redeemed_at', '<=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('amount_range')
                    ->form([
                        Forms\Components\TextInput::make('amount_from')
                            ->label('Amount From')
                            ->numeric()
                            ->prefix('€'),
                        Forms\Components\TextInput::make('amount_to')
                            ->label('Amount To')
                            ->numeric()
                            ->prefix('€'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['amount_from'],
                                fn($query, $amount) => $query->where('amount_saved', '>=', $amount),
                            )
                            ->when(
                                $data['amount_to'],
                                fn($query, $amount) => $query->where('amount_saved', '<=', $amount),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_redeemed')
                        ->label('Mark as Redeemed')
                        ->icon('heroicon-o-check-circle')
                        ->action(function ($records) {
                            $records->each->update(['status' => 'redeemed']);
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('mark_expired')
                        ->label('Mark as Expired')
                        ->icon('heroicon-o-x-circle')
                        ->action(function ($records) {
                            $records->each->update(['status' => 'expired']);
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('redeemed_at', 'desc')
            ->poll('30s');
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
            'index' => Pages\ListDiscountRedemptions::route('/'),
            'create' => Pages\CreateDiscountRedemption::route('/create'),
            'view' => Pages\ViewDiscountRedemption::route('/{record}'),
            'edit' => Pages\EditDiscountRedemption::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getGlobalSearchResultTitle($record): string
    {
        return $record->discount->name . ' - ' . $record->code->code;
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'User' => $record->user->name,
            'Amount' => $record->formatted_amount_saved,
            'Status' => $record->status,
        ];
    }

    public static function getGlobalSearchResultUrl($record): string
    {
        return static::getUrl('view', ['record' => $record]);
    }
}
