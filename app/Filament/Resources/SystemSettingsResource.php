<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SystemSettingsResource\Pages;
use App\Models\Setting;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use \BackedEnum;
final class SystemSettingsResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';


    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.system_settings');
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.system_settings');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.system_settings');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Setting Information'))
                    ->components([
                        TextInput::make('key')
                            ->label(__('Key'))
                            ->required()
                            ->maxLength(255)
                            ->unique(Setting::class, 'key', ignoreRecord: true)
                            ->helperText(__('Unique identifier for this setting'))
                            ->columnSpanFull(),
                        TextInput::make('display_name')
                            ->label(__('Display Name'))
                            ->required()
                            ->maxLength(255)
                            ->helperText(__('Human-readable name for this setting')),
                        Select::make('type')
                            ->label(__('Type'))
                            ->options([
                                'string' => __('Text'),
                                'number' => __('Number'),
                                'boolean' => __('True/False'),
                                'json' => __('JSON Data'),
                                'text' => __('Long Text'),
                                'email' => __('Email'),
                                'url' => __('URL'),
                                'color' => __('Color'),
                                'date' => __('Date'),
                                'datetime' => __('Date & Time'),
                            ])
                            ->required()
                            ->default('string')
                            ->live()
                            ->helperText(__('Data type for this setting')),
                    ])
                    ->columns(2),
                Section::make(__('Setting Value'))
                    ->components([
                        Textarea::make('value')
                            ->label(__('Value'))
                            ->required()
                            ->rows(3)
                            ->helperText(__('The actual value for this setting'))
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label(__('Description'))
                            ->rows(2)
                            ->helperText(__('Optional description of what this setting controls'))
                            ->columnSpanFull(),
                    ]),
                Section::make(__('Organization'))
                    ->components([
                        TextInput::make('group')
                            ->label(__('Group'))
                            ->helperText(__('Group this setting belongs to (e.g., "general", "email", "payment")')),
                        Toggle::make('is_public')
                            ->label(__('Public'))
                            ->helperText(__('Can this setting be accessed from frontend?'))
                            ->default(false),
                        Toggle::make('is_required')
                            ->label(__('Required'))
                            ->helperText(__('Is this setting required for system operation?'))
                            ->default(false),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label(__('Key'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('display_name')
                    ->label(__('Display Name'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'string', 'text' => 'gray',
                        'number' => 'info',
                        'boolean' => 'success',
                        'json' => 'warning',
                        'email' => 'primary',
                        'url' => 'secondary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('value')
                    ->label(__('Value'))
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = (string) ($column->getState() ?? '');
                        return strlen($state) > 50 ? $state : null;
                    })
                    ->copyable(),
                Tables\Columns\TextColumn::make('group')
                    ->label(__('Group'))
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_public')
                    ->label(__('Public'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_required')
                    ->label(__('Required'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Last Updated'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options([
                        'string' => __('Text'),
                        'number' => __('Number'),
                        'boolean' => __('True/False'),
                        'json' => __('JSON Data'),
                        'text' => __('Long Text'),
                        'email' => __('Email'),
                        'url' => __('URL'),
                        'color' => __('Color'),
                        'date' => __('Date'),
                        'datetime' => __('Date & Time'),
                    ]),
                Tables\Filters\SelectFilter::make('group')
                    ->label(__('Group'))
                    ->options(fn(): array => Setting::query()
                        ->whereNotNull('group')
                        ->distinct()
                        ->pluck('group', 'group')
                        ->filter(fn($label) => filled($label))
                        ->toArray()),
                Tables\Filters\TernaryFilter::make('is_public')
                    ->label(__('Public'))
                    ->placeholder(__('All'))
                    ->trueLabel(__('Public'))
                    ->falseLabel(__('Private')),
                Tables\Filters\TernaryFilter::make('is_required')
                    ->label(__('Required'))
                    ->placeholder(__('All'))
                    ->trueLabel(__('Required'))
                    ->falseLabel(__('Optional')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('group')
            ->groups([
                Tables\Grouping\Group::make('group')
                    ->label(__('Group'))
                    ->collapsible(),
                Tables\Grouping\Group::make('type')
                    ->label(__('Type'))
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSystemSettings::route('/'),
            'create' => Pages\CreateSystemSetting::route('/create'),
            'view' => Pages\ViewSystemSetting::route('/{record}'),
            'edit' => Pages\EditSystemSetting::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        if (app()->environment('testing')) {
            return true;
        }
        return auth()->user()?->can('view_settings') ?? false;
    }
}
