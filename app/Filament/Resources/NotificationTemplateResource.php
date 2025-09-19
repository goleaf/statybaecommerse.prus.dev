<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\NotificationTemplateResource\Pages;
use App\Models\NotificationTemplate;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * NotificationTemplateResource
 *
 * Filament v4 resource for NotificationTemplate management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class NotificationTemplateResource extends Resource
{
    protected static ?string $model = NotificationTemplate::class;
    protected static ?int $navigationSort = 6;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationGroup = NavigationGroup::Content;


    protected static $navigationGroup = NavigationGroup::Content;

    public static function getNavigationLabel(): string
    {
        return __('admin.notification_templates.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.notification_templates.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.notification_templates.model_label');
    }

    public static function schema(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaSection::make(__('admin.notification_templates.basic_information'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('admin.notification_templates.name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $context, $state, callable $set) => 
                                        $context === 'create' ? $set('slug', \Str::slug($state)) : null
                                    ),

                                TextInput::make('slug')
                                    ->label(__('admin.notification_templates.slug'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(NotificationTemplate::class, 'slug', ignoreRecord: true)
                                    ->rules(['alpha_dash']),

                                Select::make('type')
                                    ->label(__('admin.notification_templates.type'))
                                    ->options([
                                        'email' => __('admin.notification_templates.types.email'),
                                        'sms' => __('admin.notification_templates.types.sms'),
                                        'push' => __('admin.notification_templates.types.push'),
                                        'in_app' => __('admin.notification_templates.types.in_app'),
                                    ])
                                    ->required()
                                    ->default('email')
                                    ->live(),

                                TextInput::make('event')
                                    ->label(__('admin.notification_templates.event'))
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText(__('admin.notification_templates.event_help')),
                            ]),
                    ]),

                SchemaSection::make(__('admin.notification_templates.content'))
                    ->schema([
                        TextInput::make('subject')
                            ->label(__('admin.notification_templates.subject'))
                            ->required()
                            ->maxLength(255)
                            ->helperText(__('admin.notification_templates.subject_help')),

                        Textarea::make('content')
                            ->label(__('admin.notification_templates.content'))
                            ->required()
                            ->rows(10)
                            ->helperText(__('admin.notification_templates.content_help')),

                        Textarea::make('variables')
                            ->label(__('admin.notification_templates.variables'))
                            ->rows(5)
                            ->helperText(__('admin.notification_templates.variables_help')),
                    ]),

                SchemaSection::make(__('admin.notification_templates.status'))
                    ->schema([
                        SchemaGrid::make(1)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label(__('admin.notification_templates.is_active'))
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.notification_templates.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label(__('admin.notification_templates.slug'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('type')
                    ->label(__('admin.notification_templates.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'email' => 'success',
                        'sms' => 'info',
                        'push' => 'warning',
                        'in_app' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('event')
                    ->label(__('admin.notification_templates.event'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subject')
                    ->label(__('admin.notification_templates.subject'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                IconColumn::make('is_active')
                    ->label(__('admin.notification_templates.is_active'))
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('admin.notification_templates.type'))
                    ->options([
                        'email' => __('admin.notification_templates.types.email'),
                        'sms' => __('admin.notification_templates.types.sms'),
                        'push' => __('admin.notification_templates.types.push'),
                        'in_app' => __('admin.notification_templates.types.in_app'),
                    ]),

                TernaryFilter::make('is_active')
                    ->label(__('admin.notification_templates.is_active')),
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
            ->defaultSort('name');
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
            'index' => Pages\ListNotificationTemplates::route('/'),
            'create' => Pages\CreateNotificationTemplate::route('/create'),
            'view' => Pages\ViewNotificationTemplate::route('/{record}'),
            'edit' => Pages\EditNotificationTemplate::route('/{record}/edit'),
        ];
    }
}
