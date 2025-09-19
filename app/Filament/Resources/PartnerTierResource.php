<?php declare(strict_types=1);

namespace App\Filament\Resources;
use App\Filament\Resources\PartnerTierResource\Pages;
use App\Models\PartnerTier;
use Filament\Schemas\Schema;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use BackedEnum;
use App\Enums\NavigationGroup;
final class PartnerTierResource extends Resource
{
    protected static ?string $model = PartnerTier::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-star';
    // protected static $navigationGroup = NavigationGroup::System;
    protected static ?int $navigationSort = 2;
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->maxLength(255)
                            ->unique(PartnerTier::class, 'code', ignoreRecord: true),
                        Forms\Components\Toggle::make('is_enabled')
                            ->label('Enabled')
                            ->default(true),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Financial Settings')
                        Forms\Components\TextInput::make('discount_rate')
                            ->label('Discount Rate (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.0001),
                        Forms\Components\TextInput::make('commission_rate')
                            ->label('Commission Rate (%)')
                        Forms\Components\TextInput::make('minimum_order_value')
                            ->label('Minimum Order Value (€)')
                            ->step(0.01),
                Forms\Components\Section::make('Benefits')
                        Forms\Components\Repeater::make('benefits')
                            ->schema([
                                Forms\Components\TextInput::make('benefit')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->defaultItems(0)
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => $state['benefit'] ?? null),
                    ]),
            ]);
    }
    public static function table(Table $table): Table
    {record}/edit'),
}
