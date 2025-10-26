<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountResource\RelationManagers;

use App\Filament\RelationManagers\Support\BaseRelationManager;
use App\Models\DiscountCode;
use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class CodesRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'codes';

    protected static ?string $title = 'Discount Codes';

    protected static ?string $modelLabel = 'Code';

    protected static ?string $pluralModelLabel = 'Codes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(255)
                    ->unique(DiscountCode::class, 'code', ignoreRecord: true)
                    ->helperText('Unique code for this discount'),
                Forms\Components\TextInput::make('usage_limit')
                    ->numeric()
                    ->minValue(1)
                    ->helperText('How many times this specific code can be used'),
                Forms\Components\TextInput::make('usage_count')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->disabled()
                    ->helperText('How many times this code has been used'),
                SupportFlatpickr::makeDateTime('expires_at')
                    ->label('Expires At')
                    ->helperText('When this specific code expires'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
        return $table
            ->recordTitleAttribute('code')
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('usage_count')
                    ->label('Used')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('usage_limit')
                    ->label('Limit')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Only'),
                Tables\Filters\Filter::make('expired')
                    ->query(fn (Builder $query): Builder => $query->where('expires_at', '<', now()))
                    ->label('Expired'),
            ])
            ->headerActions([
                RelationManagerRepeaterAction::make()
                    ->label('Quick edit ' . $this->getPluralModelLabel())
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Edit ' . $this->getPluralModelLabel())
                    ->modalWidth('5xl')
                    ->configureRepeater(function (Repeater $repeater): Repeater {
                        // Provide a quick-edit modal for managing records inline.
                        return $repeater->schema($this->getQuickEditSchema());
                    }),
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
