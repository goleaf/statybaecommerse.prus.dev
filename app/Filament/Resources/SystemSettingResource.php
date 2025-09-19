<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SystemSettingResource\Pages;
use App\Models\SystemSetting;
use App\Enums\NavigationGroup;
use Filament\Forms;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Form;

/**
 * SystemSettingResource
 * 
 * Filament v4 resource for SystemSetting management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class SystemSettingResource extends Resource
{
    protected static ?string $model = SystemSetting::class;
    
    protected static $navigationGroup = 'System';
    
    protected static ?int $navigationSort = 18;
    protected static ?string $recordTitleAttribute = 'key';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('system_settings.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return 'System'->label();
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('system_settings.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('system_settings.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('system_settings.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('key')
                                ->label(__('system_settings.key'))
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash'])
                                ->helperText(__('system_settings.key_help')),
                            
                            Select::make('type')
                                ->label(__('system_settings.type'))
                                ->options([
                                    'string' => __('system_settings.types.string'),
                                    'integer' => __('system_settings.types.integer'),
                                    'float' => __('system_settings.types.float'),
                                    'boolean' => __('system_settings.types.boolean'),
                                    'array' => __('system_settings.types.array'),
                                    'json' => __('system_settings.types.json'),
                                    'file' => __('system_settings.types.file'),
                                    'url' => __('system_settings.types.url'),
                                    'email' => __('system_settings.types.email'),
                                    'password' => __('system_settings.types.password'),
                                ])
                                ->required()
                                ->default('string')
                                ->reactive(),
                        ]),
                    
                    Textarea::make('description')
                        ->label(__('system_settings.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            
            Section::make(__('system_settings.value'))
                ->schema([
                    Forms\Components\ViewField::make('value')
                        ->label(__('system_settings.value'))
                        ->view('filament.forms.components.system-setting-value')
                        ->viewData(fn (Forms\Get $get): array => [
                            'type' => $get('type'),
                            'value' => $get('value'),
                        ])
                        ->columnSpanFull(),
                ]),
            
            Section::make(__('system_settings.categorization'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('category')
                                ->label(__('system_settings.category'))
                                ->options([
                                    'general' => __('system_settings.categories.general'),
                                    'appearance' => __('system_settings.categories.appearance'),
                                    'email' => __('system_settings.categories.email'),
                                    'payment' => __('system_settings.categories.payment'),
                                    'shipping' => __('system_settings.categories.shipping'),
                                    'security' => __('system_settings.categories.security'),
                                    'performance' => __('system_settings.categories.performance'),
                                    'integration' => __('system_settings.categories.integration'),
                                    'analytics' => __('system_settings.categories.analytics'),
                                    'maintenance' => __('system_settings.categories.maintenance'),
                                    'custom' => __('system_settings.categories.custom'),
                                ])
                                ->required()
                                ->default('general')
                                ->searchable(),
                            
                            TextInput::make('group')
                                ->label(__('system_settings.group'))
                                ->maxLength(100)
                                ->helperText(__('system_settings.group_help')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('sort_order')
                                ->label(__('system_settings.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                            
                            Toggle::make('is_public')
                                ->label(__('system_settings.is_public'))
                                ->default(false)
                                ->helperText(__('system_settings.is_public_help')),
                        ]),
                ]),
            
            Section::make(__('system_settings.validation'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('min_value')
                                ->label(__('system_settings.min_value'))
                                ->maxLength(255)
                                ->helperText(__('system_settings.min_value_help')),
                            
                            TextInput::make('max_value')
                                ->label(__('system_settings.max_value'))
                                ->maxLength(255)
                                ->helperText(__('system_settings.max_value_help')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('allowed_values')
                                ->label(__('system_settings.allowed_values'))
                                ->maxLength(500)
                                ->helperText(__('system_settings.allowed_values_help')),
                            
                            TextInput::make('regex_pattern')
                                ->label(__('system_settings.regex_pattern'))
                                ->maxLength(255)
                                ->helperText(__('system_settings.regex_pattern_help')),
                        ]),
                ]),
            
            Section::make(__('system_settings.metadata'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('default_value')
                                ->label(__('system_settings.default_value'))
                                ->maxLength(500)
                                ->helperText(__('system_settings.default_value_help')),
                            
                            TextInput::make('unit')
                                ->label(__('system_settings.unit'))
                                ->maxLength(50)
                                ->helperText(__('system_settings.unit_help')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_required')
                                ->label(__('system_settings.is_required'))
                                ->default(false)
                                ->helperText(__('system_settings.is_required_help')),
                            
                            Toggle::make('is_encrypted')
                                ->label(__('system_settings.is_encrypted'))
                                ->default(false)
                                ->helperText(__('system_settings.is_encrypted_help')),
                        ]),
                ]),
            
            Section::make(__('system_settings.settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('system_settings.is_active'))
                                ->default(true),
                            
                            Toggle::make('is_readonly')
                                ->label(__('system_settings.is_readonly'))
                                ->default(false)
                                ->helperText(__('system_settings.is_readonly_help')),
                        ]),
                    
                    Textarea::make('notes')
                        ->label(__('system_settings.notes'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label(__('system_settings.key'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),
                
                TextColumn::make('type')
                    ->label(__('system_settings.type'))
                    ->formatStateUsing(fn (string $state): string => __("system_settings.types.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'string' => 'blue',
                        'integer' => 'green',
                        'float' => 'purple',
                        'boolean' => 'orange',
                        'array' => 'red',
                        'json' => 'indigo',
                        'file' => 'pink',
                        'url' => 'cyan',
                        'email' => 'teal',
                        'password' => 'gray',
                        default => 'gray',
                    }),
                
                TextColumn::make('value')
                    ->label(__('system_settings.value'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    })
                    ->formatStateUsing(function (string $state, SystemSetting $record): string {
                        if ($record->type === 'password') {
                            return str_repeat('*', min(strlen($state), 8));
                        }
                        if ($record->type === 'boolean') {
                            return $state ? __('system_settings.yes') : __('system_settings.no');
                        }
                        return $state;
                    }),
                
                TextColumn::make('category')
                    ->label(__('system_settings.category'))
                    ->formatStateUsing(fn (string $state): string => __("system_settings.categories.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'general' => 'gray',
                        'appearance' => 'blue',
                        'email' => 'green',
                        'payment' => 'purple',
                        'shipping' => 'orange',
                        'security' => 'red',
                        'performance' => 'indigo',
                        'integration' => 'pink',
                        'analytics' => 'cyan',
                        'maintenance' => 'teal',
                        'custom' => 'yellow',
                        default => 'gray',
                    }),
                
                TextColumn::make('group')
                    ->label(__('system_settings.group'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('default_value')
                    ->label(__('system_settings.default_value'))
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('unit')
                    ->label(__('system_settings.unit'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_public')
                    ->label(__('system_settings.is_public'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_required')
                    ->label(__('system_settings.is_required'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_encrypted')
                    ->label(__('system_settings.is_encrypted'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_readonly')
                    ->label(__('system_settings.is_readonly'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_active')
                    ->label(__('system_settings.is_active'))
                    ->boolean()
                    ->sortable(),
                
                TextColumn::make('sort_order')
                    ->label(__('system_settings.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label(__('system_settings.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label(__('system_settings.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('system_settings.type'))
                    ->options([
                        'string' => __('system_settings.types.string'),
                        'integer' => __('system_settings.types.integer'),
                        'float' => __('system_settings.types.float'),
                        'boolean' => __('system_settings.types.boolean'),
                        'array' => __('system_settings.types.array'),
                        'json' => __('system_settings.types.json'),
                        'file' => __('system_settings.types.file'),
                        'url' => __('system_settings.types.url'),
                        'email' => __('system_settings.types.email'),
                        'password' => __('system_settings.types.password'),
                    ]),
                
                SelectFilter::make('category')
                    ->label(__('system_settings.category'))
                    ->options([
                        'general' => __('system_settings.categories.general'),
                        'appearance' => __('system_settings.categories.appearance'),
                        'email' => __('system_settings.categories.email'),
                        'payment' => __('system_settings.categories.payment'),
                        'shipping' => __('system_settings.categories.shipping'),
                        'security' => __('system_settings.categories.security'),
                        'performance' => __('system_settings.categories.performance'),
                        'integration' => __('system_settings.categories.integration'),
                        'analytics' => __('system_settings.categories.analytics'),
                        'maintenance' => __('system_settings.categories.maintenance'),
                        'custom' => __('system_settings.categories.custom'),
                    ]),
                
                TernaryFilter::make('is_active')
                    ->label(__('system_settings.is_active'))
                    ->boolean()
                    ->trueLabel(__('system_settings.active_only'))
                    ->falseLabel(__('system_settings.inactive_only'))
                    ->native(false),
                
                TernaryFilter::make('is_public')
                    ->label(__('system_settings.is_public'))
                    ->boolean()
                    ->trueLabel(__('system_settings.public_only'))
                    ->falseLabel(__('system_settings.private_only'))
                    ->native(false),
                
                TernaryFilter::make('is_required')
                    ->label(__('system_settings.is_required'))
                    ->boolean()
                    ->trueLabel(__('system_settings.required_only'))
                    ->falseLabel(__('system_settings.optional_only'))
                    ->native(false),
                
                TernaryFilter::make('is_encrypted')
                    ->label(__('system_settings.is_encrypted'))
                    ->boolean()
                    ->trueLabel(__('system_settings.encrypted_only'))
                    ->falseLabel(__('system_settings.unencrypted_only'))
                    ->native(false),
                
                TernaryFilter::make('is_readonly')
                    ->label(__('system_settings.is_readonly'))
                    ->boolean()
                    ->trueLabel(__('system_settings.readonly_only'))
                    ->falseLabel(__('system_settings.editable_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                
                Action::make('reset_to_default')
                    ->label(__('system_settings.reset_to_default'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (SystemSetting $record): bool => !empty($record->default_value))
                    ->action(function (SystemSetting $record): void {
                        $record->update(['value' => $record->default_value]);
                        
                        Notification::make()
                            ->title(__('system_settings.reset_to_default_success'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                
                Action::make('toggle_active')
                    ->label(fn (SystemSetting $record): string => $record->is_active ? __('system_settings.deactivate') : __('system_settings.activate'))
                    ->icon(fn (SystemSetting $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (SystemSetting $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (SystemSetting $record): void {
                        $record->update(['is_active' => !$record->is_active]);
                        
                        Notification::make()
                            ->title($record->is_active ? __('system_settings.activated_successfully') : __('system_settings.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    
                    BulkAction::make('activate')
                        ->label(__('system_settings.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            
                            Notification::make()
                                ->title(__('system_settings.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    
                    BulkAction::make('deactivate')
                        ->label(__('system_settings.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            
                            Notification::make()
                                ->title(__('system_settings.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    
                    BulkAction::make('reset_to_default')
                        ->label(__('system_settings.reset_selected_to_default'))
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each(function (SystemSetting $record): void {
                                if (!empty($record->default_value)) {
                                    $record->update(['value' => $record->default_value]);
                                }
                            });
                            
                            Notification::make()
                                ->title(__('system_settings.bulk_reset_to_default_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    /**
     * Get the relations for this resource.
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSystemSettings::route('/'),
            'create' => Pages\CreateSystemSetting::route('/create'),
            'view' => Pages\ViewSystemSetting::route('/{record}'),
            'edit' => Pages\EditSystemSetting::route('/{record}/edit'),
        ];
    }
}
