<?php declare(strict_types=1);

namespace App\Filament\Resources\DiscountResource\RelationManagers;

use App\Models\DiscountCondition;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class ConditionsRelationManager extends RelationManager
{
    protected static string $relationship = 'conditions';

    protected static ?string $title = 'Discount Conditions';

    protected static ?string $modelLabel = 'Condition';

    protected static ?string $pluralModelLabel = 'Conditions';

    public function form(Form $schema): Form
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('type')
                    ->options([
                        'minimum_amount' => 'Minimum Amount',
                        'minimum_quantity' => 'Minimum Quantity',
                        'customer_group' => 'Customer Group',
                        'product_category' => 'Product Category',
                        'product_brand' => 'Product Brand',
                        'time_period' => 'Time Period',
                        'day_of_week' => 'Day of Week',
                        'first_time_customer' => 'First Time Customer',
                        'loyalty_tier' => 'Loyalty Tier',
                    ])
                    ->required()
                    ->live(),
                Forms\Components\TextInput::make('value')
                    ->required()
                    ->helperText('Condition value (amount, quantity, etc.)'),
                Forms\Components\Select::make('operator')
                    ->options([
                        'equals' => 'Equals',
                        'greater_than' => 'Greater Than',
                        'less_than' => 'Less Than',
                        'greater_than_or_equal' => 'Greater Than or Equal',
                        'less_than_or_equal' => 'Less Than or Equal',
                        'not_equals' => 'Not Equals',
                        'contains' => 'Contains',
                        'not_contains' => 'Not Contains',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->rows(2)
                    ->helperText('Optional description of this condition'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'minimum_amount' => 'primary',
                        'minimum_quantity' => 'success',
                        'customer_group' => 'info',
                        'product_category' => 'warning',
                        'product_brand' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'minimum_amount' => 'Minimum Amount',
                        'minimum_quantity' => 'Minimum Quantity',
                        'customer_group' => 'Customer Group',
                        'product_category' => 'Product Category',
                        'product_brand' => 'Product Brand',
                        'time_period' => 'Time Period',
                        'day_of_week' => 'Day of Week',
                        'first_time_customer' => 'First Time Customer',
                        'loyalty_tier' => 'Loyalty Tier',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('operator')
                    ->label('Operator')
                    ->badge()
                    ->color('secondary'),
                Tables\Columns\TextColumn::make('value')
                    ->label('Value')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'minimum_amount' => 'Minimum Amount',
                        'minimum_quantity' => 'Minimum Quantity',
                        'customer_group' => 'Customer Group',
                        'product_category' => 'Product Category',
                        'product_brand' => 'Product Brand',
                        'time_period' => 'Time Period',
                        'day_of_week' => 'Day of Week',
                        'first_time_customer' => 'First Time Customer',
                        'loyalty_tier' => 'Loyalty Tier',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Only'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
