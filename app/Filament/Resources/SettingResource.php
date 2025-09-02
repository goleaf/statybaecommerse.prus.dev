<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;

final class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 99;

    protected static ?string $navigationLabel = 'Settings';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Setting Details')
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->required()
                            ->unique(Setting::class, 'key', ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Unique identifier for this setting'),
                        Forms\Components\Select::make('type')
                            ->options([
                                'string' => 'String',
                                'number' => 'Number',
                                'boolean' => 'Boolean',
                                'json' => 'JSON',
                                'array' => 'Array',
                            ])
                            ->required()
                            ->default('string')
                            ->live()
                            ->helperText('Data type for this setting'),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(500)
                            ->rows(2)
                            ->helperText('Description of what this setting controls'),
                        Forms\Components\Toggle::make('is_public')
                            ->label('Public Setting')
                            ->helperText('Whether this setting can be accessed publicly'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Value')
                    ->schema([
                        Forms\Components\TextInput::make('value')
                            ->label('String Value')
                            ->visible(fn(Forms\Get $get): bool => $get('type') === 'string')
                            ->maxLength(1000),
                        Forms\Components\TextInput::make('value')
                            ->label('Number Value')
                            ->visible(fn(Forms\Get $get): bool => $get('type') === 'number')
                            ->numeric(),
                        Forms\Components\Toggle::make('value')
                            ->label('Boolean Value')
                            ->visible(fn(Forms\Get $get): bool => $get('type') === 'boolean'),
                        Forms\Components\Textarea::make('value')
                            ->label('JSON/Array Value')
                            ->visible(fn(Forms\Get $get): bool => in_array($get('type'), ['json', 'array']))
                            ->rows(4)
                            ->helperText('Enter valid JSON format'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'string' => 'gray',
                        'number' => 'blue',
                        'boolean' => 'green',
                        'json' => 'purple',
                        'array' => 'orange',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('value')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),
                Tables\Columns\IconColumn::make('is_public')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'string' => 'String',
                        'number' => 'Number',
                        'boolean' => 'Boolean',
                        'json' => 'JSON',
                        'array' => 'Array',
                    ]),
                Tables\Filters\TernaryFilter::make('is_public')
                    ->label('Public Settings'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('key');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'view' => Pages\ViewSetting::route('/{record}'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
