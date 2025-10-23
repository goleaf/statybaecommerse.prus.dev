<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountRedemptionResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use App\Filament\RelationManagers\Support\BaseRelationManager;
use App\Support\Filament\Components\Flatpickr;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use App\Support\Filament\Components\Flatpickr;
use Filament\Schemas\Schema;

class CodeRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'code';

    protected static ?string $title = 'Discount Code Details';

    protected static ?string $modelLabel = 'Code';

    protected static ?string $pluralModelLabel = 'Codes';

    public function form(Schema $schema): Schema
    {
        // Configure the Filament resource form schema using the v4 Schema API.
        return $schema
            ->schema([
                Forms\Components\Section::make('Code Information')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Textarea::make('description_lt')
                            ->label('Description (LT)')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description_en')
                            ->label('Description (EN)')
                            ->columnSpanFull(),
                        Flatpickr::makeDateTime('starts_at')
                            ->label('Starts At'),
                        Flatpickr::makeDateTime('expires_at')
                            ->label('Expires At'),
                        Forms\Components\TextInput::make('usage_limit')
                            ->label('Usage Limit')
                            ->numeric(),
                        Forms\Components\TextInput::make('usage_limit_per_user')
                            ->label('Usage Limit Per User')
                            ->numeric(),
                        Forms\Components\TextInput::make('usage_count')
                            ->label('Usage Count')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active'    => 'Active',
                                'inactive'  => 'Inactive',
                                'expired'   => 'Expired',
                                'suspended' => 'Suspended',
                            ])
                            ->default('active'),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        // Configure the Filament table definition for the resource.
        return $table
            ->recordTitleAttribute('code')
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->copyable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('description_lt')
                    ->label('Description (LT)')
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('description_en')
                    ->label('Description (EN)')
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Starts At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('usage_count')
                    ->label('Usage Count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('usage_limit')
                    ->label('Usage Limit')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'    => 'success',
                        'inactive'  => 'gray',
                        'expired'   => 'danger',
                        'suspended' => 'warning',
                        default     => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active'    => 'Active',
                        'inactive'  => 'Inactive',
                        'expired'   => 'Expired',
                        'suspended' => 'Suspended',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                Tables\Filters\Filter::make('expires_at')
                    ->form([
                        Flatpickr::makeDate('expires_from')
                            ->label('Expires From'),
                        Flatpickr::makeDate('expires_until')
                            ->label('Expires Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['expires_from'],
                                fn ($query, $date) => $query->whereDate('expires_at', '>=', $date),
                            )
                            ->when(
                                $data['expires_until'],
                                fn ($query, $date) => $query->whereDate('expires_at', '<=', $date),
                            );
                    }),
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
                Tables\Actions\AttachAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}