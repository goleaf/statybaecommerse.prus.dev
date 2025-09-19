<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountResource\Pages;
use App\Models\Discount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

final class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Discount::class, 'slug', ignoreRecord: true),

                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('type')
                            ->options([
                                'percentage' => 'Percentage',
                                'fixed' => 'Fixed Amount',
                                'free_shipping' => 'Free Shipping',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('value')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                    ])
                    ->columns(2),

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
                            ->required()
                            ->default('draft'),

                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Starts At'),

                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('Ends At'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Usage Limits')
                    ->schema([
                        Forms\Components\TextInput::make('usage_limit')
                            ->numeric()
                            ->minValue(0),

                        Forms\Components\TextInput::make('minimum_amount')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01),

                        Forms\Components\TextInput::make('maximum_amount')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01),

                        Forms\Components\TextInput::make('per_customer_limit')
                            ->numeric()
                            ->minValue(0),

                        Forms\Components\TextInput::make('per_code_limit')
                            ->numeric()
                            ->minValue(0),

                        Forms\Components\TextInput::make('per_day_limit')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Advanced Settings')
                    ->schema([
                        Forms\Components\Select::make('zone_id')
                            ->relationship('zone', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('priority')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('exclusive')
                            ->label('Exclusive Discount'),

                        Forms\Components\Toggle::make('applies_to_shipping')
                            ->label('Applies to Shipping'),

                        Forms\Components\Toggle::make('free_shipping')
                            ->label('Free Shipping'),

                        Forms\Components\Toggle::make('first_order_only')
                            ->label('First Order Only'),
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
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'primary' => 'percentage',
                        'success' => 'fixed',
                        'info' => 'free_shipping',
                    ]),

                Tables\Columns\TextColumn::make('value')
                    ->formatStateUsing(fn ($state, $record) => 
                        $record->type === 'percentage' ? $state . '%' : '€' . number_format($state, 2)
                    )
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'active',
                        'danger' => 'inactive',
                        'secondary' => 'expired',
                    ]),

                Tables\Columns\TextColumn::make('usage_count')
                    ->label('Used')
                    ->sortable(),

                Tables\Columns\TextColumn::make('usage_limit')
                    ->label('Limit')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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

                Tables\Filters\Filter::make('expired')
                    ->query(fn ($query) => $query->where('ends_at', '<', now())),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListDiscounts::route('/'),
            'create' => Pages\CreateDiscount::route('/create'),
            'edit' => Pages\EditDiscount::route('/{record}/edit'),
        ];
    }
}
