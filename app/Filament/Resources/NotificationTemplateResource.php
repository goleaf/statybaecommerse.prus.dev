<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\Concerns\HasNav;

use App\Enums\NavigationGroup;
use App\Filament\Resources\NotificationTemplateResource\Pages;
use App\Models\NotificationTemplate;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use UnitEnum;
use Filament\Schemas\Schema;

use Filament\Schemas\Schema;
/**
 * NotificationTemplateResource
 *
 * Filament v4 resource for NotificationTemplate management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class NotificationTemplateResource extends Resource
{
    use HasNav;

    protected static ?string $model = NotificationTemplate::class;

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'name';

    /** @var string|\UnitEnum|null */
    protected static UnitEnum|string|null $navigationGroup = NavigationGroup::Content;

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

    public static function form(Schema $form): Schema
    {
        return $schema
            ->schema([
                Section::make(__('admin.notification_templates.basic_information'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('admin.notification_templates.name'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                function (string $operation, ?string $state, callable $set, ?callable $get = null): void {
                                    if ($operation !== 'create') {
                                        return;
                                    }

                                    if ($get !== null && filled($get('slug'))) {
                                        return;
                                    }

                                    if (! filled($state)) {
                                        return;
                                    }

                                    $set('slug', Str::slug((string) $state));
                                }
                            ),
                        TextInput::make('slug')
                            ->label(__('admin.notification_templates.slug'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                static function (?string $state, callable $set): void {
                                    $set('slug', filled($state) ? Str::slug($state) : null);
                                }
                            )
                            ->dehydrateStateUsing(
                                static fn (?string $state): ?string => filled($state) ? Str::slug($state) : null
                            )
                            ->unique(NotificationTemplate::class, 'slug', ignoreRecord: true)
                            ->rules(['alpha_dash']),
                        Select::make('type')
                            ->label(__('admin.notification_templates.type'))
                            ->options([
                                'email'  => __('admin.notification_templates.types.email'),
                                'sms'    => __('admin.notification_templates.types.sms'),
                                'push'   => __('admin.notification_templates.types.push'),
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
                    ])
                    ->columns(2),
                Section::make(__('admin.notification_templates.content'))
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
                Section::make(__('admin.notification_templates.status'))
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('admin.notification_templates.is_active'))
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
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
                        'email'  => 'success',
                        'sms'    => 'info',
                        'push'   => 'warning',
                        'in_app' => 'danger',
                        default  => 'gray',
                    }),
                TextColumn::make('event')
                    ->label(__('admin.notification_templates.event'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject')
                    ->label(__('admin.notification_templates.subject'))
                    ->limit(50)
                    ->tooltip(
                        static function (TextColumn $column): ?string {
                            $state = $column->getState();

                            if (! is_string($state)) {
                                return null;
                            }

                            $state = trim($state);

                            return mb_strlen($state) > 50 ? $state : null;
                        }
                    ),
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
                        'email'  => __('admin.notification_templates.types.email'),
                        'sms'    => __('admin.notification_templates.types.sms'),
                        'push'   => __('admin.notification_templates.types.push'),
                        'in_app' => __('admin.notification_templates.types.in_app'),
                    ]),
                TernaryFilter::make('is_active')
                    ->label(__('admin.notification_templates.is_active')),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
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
            'index'  => Pages\ListNotificationTemplates::route('/'),
            'create' => Pages\CreateNotificationTemplate::route('/create'),
            'view'   => Pages\ViewNotificationTemplate::route('/{record}'),
            'edit'   => Pages\EditNotificationTemplate::route('/{record}/edit'),
        ];
    }
}