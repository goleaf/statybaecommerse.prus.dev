<?php

declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ChannelResource\Pages;
use App\Models\Channel;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

/**
 * ChannelResource
 *
 * Filament v4 resource for Channel management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class ChannelResource extends Resource
{
    protected static ?string $model = Channel::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Settings';
    }

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-rectangle-stack';
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.channels.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.channels.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.channels.model_label');
    }

    public static function schema(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaSection::make(__('admin.channels.basic_information'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('admin.channels.name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $context, $state, callable $set) => $context === 'create' ? $set('slug', \Str::slug($state)) : null),
                                TextInput::make('slug')
                                    ->label(__('admin.channels.slug'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Channel::class, 'slug', ignoreRecord: true)
                                    ->rules(['alpha_dash']),
                                TextInput::make('code')
                                    ->label(__('admin.channels.code'))
                                    ->required()
                                    ->maxLength(50)
                                    ->unique(Channel::class, 'code', ignoreRecord: true)
                                    ->rules(['alpha_dash']),
                                Select::make('type')
                                    ->label(__('admin.channels.type'))
                                    ->options([
                                        'web' => __('admin.channels.types.web'),
                                        'mobile' => __('admin.channels.types.mobile'),
                                        'api' => __('admin.channels.types.api'),
                                        'pos' => __('admin.channels.types.pos'),
                                    ])
                                    ->required()
                                    ->default('web'),
                            ]),
                        Textarea::make('description')
                            ->label(__('admin.channels.description'))
                            ->maxLength(1000)
                            ->rows(3),
                    ]),
                SchemaSection::make(__('admin.channels.configuration'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('url')
                                    ->label(__('admin.channels.url'))
                                    ->url()
                                    ->maxLength(255),
                                TextInput::make('domain')
                                    ->label(__('admin.channels.domain'))
                                    ->maxLength(255),
                                TextInput::make('timezone')
                                    ->label(__('admin.channels.timezone'))
                                    ->maxLength(50)
                                    ->default('UTC'),
                                TextInput::make('currency_code')
                                    ->label(__('admin.channels.currency_code'))
                                    ->maxLength(3)
                                    ->default('EUR'),
                                TextInput::make('currency_symbol')
                                    ->label(__('admin.channels.currency_symbol'))
                                    ->maxLength(10)
                                    ->default('€'),
                                Select::make('currency_position')
                                    ->label(__('admin.channels.currency_position'))
                                    ->options([
                                        'before' => __('admin.channels.currency_positions.before'),
                                        'after' => __('admin.channels.currency_positions.after'),
                                    ])
                                    ->default('after'),
                            ]),
                    ]),
                SchemaSection::make(__('admin.channels.status'))
                    ->schema([
                        SchemaGrid::make(3)
                            ->schema([
                                Toggle::make('is_enabled')
                                    ->label(__('admin.channels.is_enabled'))
                                    ->default(true),
                                Toggle::make('is_default')
                                    ->label(__('admin.channels.is_default'))
                                    ->default(false),
                                Toggle::make('is_active')
                                    ->label(__('admin.channels.is_active'))
                                    ->default(true),
                                Toggle::make('ssl_enabled')
                                    ->label(__('admin.channels.ssl_enabled'))
                                    ->default(true),
                                Toggle::make('analytics_enabled')
                                    ->label(__('admin.channels.analytics_enabled'))
                                    ->default(false),
                                TextInput::make('sort_order')
                                    ->label(__('admin.channels.sort_order'))
                                    ->numeric()
                                    ->default(0),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.channels.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label(__('admin.channels.code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('admin.channels.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'web' => 'success',
                        'mobile' => 'info',
                        'api' => 'warning',
                        'pos' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('url')
                    ->label(__('admin.channels.url'))
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    }),
                IconColumn::make('is_enabled')
                    ->label(__('admin.channels.is_enabled'))
                    ->boolean(),
                IconColumn::make('is_default')
                    ->label(__('admin.channels.is_default'))
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label(__('admin.channels.is_active'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('admin.channels.type'))
                    ->options([
                        'web' => __('admin.channels.types.web'),
                        'mobile' => __('admin.channels.types.mobile'),
                        'api' => __('admin.channels.types.api'),
                        'pos' => __('admin.channels.types.pos'),
                    ]),
                TernaryFilter::make('is_enabled')
                    ->label(__('admin.channels.is_enabled')),
                TernaryFilter::make('is_default')
                    ->label(__('admin.channels.is_default')),
                TernaryFilter::make('is_active')
                    ->label(__('admin.channels.is_active')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
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
            'index' => Pages\ListChannels::route('/'),
            'create' => Pages\CreateChannel::route('/create'),
            'view' => Pages\ViewChannel::route('/{record}'),
            'edit' => Pages\EditChannel::route('/{record}/edit'),
        ];
    }
}
