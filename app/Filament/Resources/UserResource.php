<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Services\Export\Contracts\DefinesExportColumns;
use App\Services\Export\ExportColumn;
use App\Services\Export\ExportFormat;
use App\Services\Export\ExportService;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * UserResource
 *
 * Filament v4 resource for User management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class UserResource extends Resource implements DefinesExportColumns
{
    protected static ?string $model = User::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static UnitEnum|string|null $navigationGroup = 'Users';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     */
    public static function getNavigationLabel(): string
    {
        return __('users.title');
    }

    /** Handle getNavigationGroup functionality with proper error handling. */

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('users.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('users.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('users.sections.basic_info'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('users.fields.name'))
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->label(__('users.fields.email'))
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                            ]),
                        TextInput::make('password')
                            ->label(__('users.fields.password'))
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->minLength(8)
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => bcrypt($state)),
                        Select::make('locale')
                            ->label(__('users.fields.locale'))
                            ->options([
                                'lt' => 'Lietuvių',
                                'en' => 'English',
                            ])
                            ->default('lt')
                            ->required(),
                        Toggle::make('is_active')
                            ->label(__('users.fields.is_active'))
                            ->default(true),
                    ])
                    ->columns(1),
                Section::make(__('users.sections.profile'))
                    ->schema([
                        FileUpload::make('avatar')
                            ->label(__('users.fields.avatar'))
                            ->image()
                            ->directory('users/avatars')
                            ->visibility('private'),
                        Textarea::make('bio')
                            ->label(__('users.fields.bio'))
                            ->maxLength(1000)
                            ->rows(3),
                    ])
                    ->columns(1),
            ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label(__('users.fields.avatar'))
                    ->circular()
                    ->defaultImageUrl(url('/images/default-avatar.png')),
                TextColumn::make('name')
                    ->label(__('users.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('users.fields.email'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('locale')
                    ->label(__('users.fields.locale'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'lt' => 'success',
                        'en' => 'info',
                        default => 'gray',
                    }),
                IconColumn::make('is_active')
                    ->label(__('users.fields.is_active'))
                    ->boolean(),
                IconColumn::make('email_verified_at')
                    ->label(__('users.fields.email_verified'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('users.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('locale')
                    ->options([
                        'lt' => 'Lietuvių',
                        'en' => 'English',
                    ]),
                TernaryFilter::make('is_active')
                    ->label(__('users.fields.is_active')),
                TernaryFilter::make('email_verified_at')
                    ->label(__('users.fields.email_verified')),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label(__('users.actions.activate'))
                        ->icon('heroicon-o-check-circle')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('users.messages.bulk_activate_success'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('deactivate')
                        ->label(__('users.actions.deactivate'))
                        ->icon('heroicon-o-x-circle')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('users.messages.bulk_deactivate_success'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('export_users')
                        ->label(__('exports.actions.export_users'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->form([
                            Select::make('format')
                                ->label(__('exports.form.format'))
                                ->options(collect(ExportFormat::cases())->mapWithKeys(fn (ExportFormat $format) => [$format->value => $format->label()])->all())
                                ->default(ExportFormat::Csv->value)
                                ->required(),
                            CheckboxList::make('columns')
                                ->label(__('exports.form.columns'))
                                ->options(self::exportColumnOptions())
                                ->default(array_keys(self::exportColumnOptions()))
                                ->columns(2)
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            /** @var ExportService $exportService */
                            $exportService = app(ExportService::class);

                            $exportService->queueResourceExport(
                                resourceClass: self::class,
                                records: $records,
                                columnKeys: $data['columns'],
                                format: ExportFormat::from($data['format']),
                                requestedBy: auth()->user(),
                            );

                            Notification::make()
                                ->title(__('exports.notifications.queued'))
                                ->body(__('exports.notifications.queued_body'))
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * @return array<string, ExportColumn>
     */
    public static function availableExportColumns(): array
    {
        return [
            'name' => new ExportColumn('name', __('users.fields.name'), fn (User $user): string => (string) $user->name),
            'email' => new ExportColumn('email', __('users.fields.email'), fn (User $user): string => (string) $user->email),
            'is_active' => new ExportColumn('is_active', __('users.fields.is_active'), fn (User $user): string => $user->is_active ? __('exports.boolean.yes') : __('exports.boolean.no')),
            'is_verified' => new ExportColumn('is_verified', __('users.fields.is_verified'), fn (User $user): string => $user->is_verified ? __('exports.boolean.yes') : __('exports.boolean.no')),
            'roles' => new ExportColumn('roles', __('users.fields.roles'), fn (User $user): string => (string) $user->roles_label),
            'created_at' => new ExportColumn('created_at', __('users.fields.created_at'), fn (User $user): string => optional($user->created_at)->toDateTimeString() ?? ''),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function exportColumnOptions(): array
    {
        return array_map(static fn (ExportColumn $column): string => $column->label, self::availableExportColumns());
    }

    /**
     * Get the resource pages.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
