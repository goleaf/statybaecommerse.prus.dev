<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\StatusEnum;
use App\Enums\PriorityEnum;
use App\Enums\CurrencyEnum;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;
use UnitEnum;
use BackedEnum;

final class EnumManagementResource extends Resource
{
    protected static ?string $model = null;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    /** @var UnitEnum|string|null */
    protected static string|UnitEnum|null $navigationGroup = 'System';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Enum Management')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Status Enum')
                            ->schema([
                                Forms\Components\Section::make('Status Enum Values')
                                    ->schema([
                                        Forms\Components\Repeater::make('status_enum')
                                            ->schema([
                                                Forms\Components\TextInput::make('value')
                                                    ->label('Value')
                                                    ->required(),
                                                Forms\Components\TextInput::make('label')
                                                    ->label('Label')
                                                    ->required(),
                                                Forms\Components\Select::make('color')
                                                    ->label('Color')
                                                    ->options([
                                                        'success' => 'Success',
                                                        'warning' => 'Warning',
                                                        'danger' => 'Danger',
                                                        'info' => 'Info',
                                                        'gray' => 'Gray',
                                                        'secondary' => 'Secondary',
                                                    ])
                                                    ->required(),
                                                Forms\Components\TextInput::make('icon')
                                                    ->label('Icon')
                                                    ->placeholder('heroicon-o-check-circle'),
                                            ])
                                            ->defaultItems(5)
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): string => $state['label'] ?? 'Status Item'),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Priority Enum')
                            ->schema([
                                Forms\Components\Section::make('Priority Enum Values')
                                    ->schema([
                                        Forms\Components\Repeater::make('priority_enum')
                                            ->schema([
                                                Forms\Components\TextInput::make('value')
                                                    ->label('Value')
                                                    ->numeric()
                                                    ->required(),
                                                Forms\Components\TextInput::make('label')
                                                    ->label('Label')
                                                    ->required(),
                                                Forms\Components\Select::make('color')
                                                    ->label('Color')
                                                    ->options([
                                                        'success' => 'Success',
                                                        'warning' => 'Warning',
                                                        'danger' => 'Danger',
                                                        'info' => 'Info',
                                                        'gray' => 'Gray',
                                                        'secondary' => 'Secondary',
                                                    ])
                                                    ->required(),
                                                Forms\Components\TextInput::make('icon')
                                                    ->label('Icon')
                                                    ->placeholder('heroicon-o-arrow-up'),
                                            ])
                                            ->defaultItems(5)
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): string => $state['label'] ?? 'Priority Item'),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Currency Enum')
                            ->schema([
                                Forms\Components\Section::make('Currency Enum Values')
                                    ->schema([
                                        Forms\Components\Repeater::make('currency_enum')
                                            ->schema([
                                                Forms\Components\TextInput::make('code')
                                                    ->label('Currency Code')
                                                    ->required()
                                                    ->maxLength(3),
                                                Forms\Components\TextInput::make('label')
                                                    ->label('Label')
                                                    ->required(),
                                                Forms\Components\TextInput::make('symbol')
                                                    ->label('Symbol')
                                                    ->required(),
                                                Forms\Components\TextInput::make('decimal_places')
                                                    ->label('Decimal Places')
                                                    ->numeric()
                                                    ->default(2)
                                                    ->required(),
                                            ])
                                            ->defaultItems(10)
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): string => $state['code'] ?? 'Currency Item'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('enum_type')
                    ->label('Enum Type')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Value')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('label')
                    ->label('Label')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),
                Tables\Columns\TextColumn::make('color')
                    ->label('Color')
                    ->badge()
                    ->color(fn (string $state): string => $state),
                Tables\Columns\TextColumn::make('icon')
                    ->label('Icon')
                    ->icon(fn (string $state): string => $state)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('symbol')
                    ->label('Symbol')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('decimal_places')
                    ->label('Decimal Places')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('enum_type')
                    ->options([
                        'status' => 'Status',
                        'priority' => 'Priority',
                        'currency' => 'Currency',
                    ])
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('enum_type', 'asc');
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
            'index' => Pages\ListEnumManagement::route('/'),
            'create' => Pages\CreateEnumManagement::route('/create'),
            'view' => Pages\ViewEnumManagement::route('/{record}'),
            'edit' => Pages\EditEnumManagement::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return 'Enums';
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }
}