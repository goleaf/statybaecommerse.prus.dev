<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\EnhancedSettingResource\Pages;
use App\Models\EnhancedSetting;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;

final class EnhancedSettingResource extends Resource
{
    protected static ?string $model = EnhancedSetting::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('Enhanced Settings');
    }

    public static function getModelLabel(): string
    {
        return __('Enhanced Setting');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Enhanced Settings');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make(__('Setting Information'))
                    ->components([
                        Forms\Components\Select::make('group')
                            ->label(__('Group'))
                            ->options([
                                'general' => __('General'),
                                'ecommerce' => __('E-commerce'),
                                'email' => __('Email'),
                                'payment' => __('Payment'),
                                'shipping' => __('Shipping'),
                                'seo' => __('SEO'),
                                'security' => __('Security'),
                                'api' => __('API'),
                                'appearance' => __('Appearance'),
                                'notifications' => __('Notifications'),
                            ])
                            ->required()
                            ->default('general'),
                        Forms\Components\TextInput::make('key')
                            ->label(__('Key'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->regex('/^[a-z0-9_\.]+$/')
                            ->helperText(__('Use lowercase letters, numbers, underscores and dots only')),
                        Forms\Components\Select::make('type')
                            ->label(__('Type'))
                            ->options([
                                'text' => __('Text'),
                                'textarea' => __('Textarea'),
                                'number' => __('Number'),
                                'boolean' => __('Boolean'),
                                'json' => __('JSON'),
                                'array' => __('Array'),
                                'select' => __('Select'),
                                'file' => __('File'),
                                'color' => __('Color'),
                                'date' => __('Date'),
                                'datetime' => __('DateTime'),
                            ])
                            ->required()
                            ->default('text')
                            ->live(),
                        Forms\Components\TextInput::make('sort_order')
                            ->label(__('Sort Order'))
                            ->numeric()
                            ->default(0),
                    ])->columns(2),

                Forms\Components\Section::make(__('Value Configuration'))
                    ->components([
                        Forms\Components\TextInput::make('value')
                            ->label(__('Value'))
                            ->visible(fn (Forms\Get $get) => in_array($get('type'), ['text', 'number']))
                            ->required()
                            ->maxLength(1000),
                        Forms\Components\Textarea::make('value')
                            ->label(__('Value'))
                            ->visible(fn (Forms\Get $get) => $get('type') === 'textarea')
                            ->required()
                            ->rows(4),
                        Forms\Components\Toggle::make('value')
                            ->label(__('Value'))
                            ->visible(fn (Forms\Get $get) => $get('type') === 'boolean'),
                        Forms\Components\Textarea::make('value')
                            ->label(__('JSON Value'))
                            ->visible(fn (Forms\Get $get) => in_array($get('type'), ['json', 'array']))
                            ->required()
                            ->rows(6)
                            ->helperText(__('Enter valid JSON format')),
                        Forms\Components\ColorPicker::make('value')
                            ->label(__('Color Value'))
                            ->visible(fn (Forms\Get $get) => $get('type') === 'color'),
                        Forms\Components\DatePicker::make('value')
                            ->label(__('Date Value'))
                            ->visible(fn (Forms\Get $get) => $get('type') === 'date'),
                        Forms\Components\DateTimePicker::make('value')
                            ->label(__('DateTime Value'))
                            ->visible(fn (Forms\Get $get) => $get('type') === 'datetime'),
                        Forms\Components\Textarea::make('description')
                            ->label(__('Description'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make(__('Advanced Options'))
                    ->components([
                        Forms\Components\Toggle::make('is_public')
                            ->label(__('Is Public'))
                            ->helperText(__('Public settings can be accessed from frontend')),
                        Forms\Components\Toggle::make('is_encrypted')
                            ->label(__('Is Encrypted'))
                            ->helperText(__('Sensitive settings will be encrypted in database')),
                        Forms\Components\KeyValue::make('validation_rules')
                            ->label(__('Validation Rules'))
                            ->helperText(__('Laravel validation rules in key-value format')),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('group')
                    ->label(__('Group'))
                    ->badge()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('key')
                    ->label(__('Key'))
                    ->sortable()
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'boolean' => 'success',
                        'number' => 'info',
                        'json', 'array' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('value')
                    ->label(__('Value'))
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->description),
                Tables\Columns\IconColumn::make('is_public')
                    ->label(__('Public'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_encrypted')
                    ->label(__('Encrypted'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Updated At'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->label(__('Group'))
                    ->options(fn () => EnhancedSetting::distinct()->pluck('group', 'group')),
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options([
                        'text' => __('Text'),
                        'number' => __('Number'),
                        'boolean' => __('Boolean'),
                        'json' => __('JSON'),
                        'array' => __('Array'),
                    ]),
                Tables\Filters\TernaryFilter::make('is_public')
                    ->label(__('Is Public')),
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
            ->defaultSort('group', 'asc')
            ->groups([
                Tables\Grouping\Group::make('group')
                    ->label(__('Group')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnhancedSettings::route('/'),
            'create' => Pages\CreateEnhancedSetting::route('/create'),
            'view' => Pages\ViewEnhancedSetting::route('/{record}'),
            'edit' => Pages\EditEnhancedSetting::route('/{record}/edit'),
        ];
    }
}
