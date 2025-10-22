<?php

declare(strict_types=1);

namespace App\Filament\Resources;


use Filament\Schemas\Schema;
use App\Data\ExportRequestData;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Services\Export\ExportColumn;
use App\Services\Export\Exporters\UserExport;
use App\Services\Export\ExportService;
use App\Support\Authorization\AuthorizationMatrix;
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
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use UnitEnum;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable as SpatieTranslatableResource;

/**
 * UserResource
 *
 * Filament v4 resource for User management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class UserResource extends Resource
{
    use SpatieTranslatableResource; // Enable locale-aware management for Spatie translatable attributes.

    protected static ?string $model = User::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static UnitEnum|string|null $navigationGroup = 'Users';

    public static function shouldRegisterNavigation(): bool
    {
        return AuthorizationMatrix::check('users', 'viewAny');
    }

    public static function canViewAny(): bool
    {
        return AuthorizationMatrix::check('users', 'viewAny');
    }

    public static function canView(Model $record): bool
    {
        return AuthorizationMatrix::check('users', 'view');
    }

    public static function canCreate(): bool
    {
        return AuthorizationMatrix::check('users', 'create');
    }

    public static function canEdit(Model $record): bool
    {
        return AuthorizationMatrix::check('users', 'update');
    }

    public static function canDelete(Model $record): bool
    {
        return AuthorizationMatrix::check('users', 'delete');
    }

    public static function canForceDelete(Model $record): bool
    {
        return AuthorizationMatrix::check('users', 'delete');
    }

    public static function canRestore(Model $record): bool
    {
        return AuthorizationMatrix::check('users', 'update');
    }

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
    public static function form(Schema $schema): Schema   
    {
        return $schema
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
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->dehydrateStateUsing(
                                fn (?string $state): ?string => filled($state)
                                    ? Hash::make($state)
                                    : null,
                            ),
                        Select::make('preferred_locale')
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
                        FileUpload::make('avatar_url')
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
                ImageColumn::make('avatar_url')
                    ->label(__('users.fields.avatar'))
                    ->circular()
                    ->defaultImageUrl(url('/images/logo.svg')),
                TextColumn::make('name')
                    ->label(__('users.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('users.fields.email'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('preferred_locale')
                    ->label(__('users.fields.locale'))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'lt'    => 'success',
                        'en'    => 'info',
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
                SelectFilter::make('preferred_locale')
                    ->options([
                        'lt' => 'Lietuvių',
                        'en' => 'English',
                    ]),
                TernaryFilter::make('is_active')
                    ->label(__('users.fields.is_active')),
                TernaryFilter::make('email_verified_at')
                    ->label(__('users.fields.email_verified')),
                TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make()
                    ->visible(fn () => AuthorizationMatrix::check('users', 'update')),
                DeleteAction::make()
                    ->visible(fn () => AuthorizationMatrix::check('users', 'delete')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('export_selected')
                        ->label(__('Export selected'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->form([
                            Select::make('format')
                                ->label(__('Format'))
                                ->options([
                                    'csv'  => 'CSV',
                                    'xlsx' => 'XLSX',
                                    'pdf'  => 'PDF',
                                ])
                                ->default('csv')
                                ->required(),
                            CheckboxList::make('columns')
                                ->label(__('Columns'))
                                ->options(fn () => collect(app(UserExport::class)->columns())->mapWithKeys(fn (ExportColumn $column) => [$column->key => $column->label])->all())
                                ->default(fn () => app(UserExport::class)->defaultColumns())
                                ->columns(2)
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            /** @var ExportService $service */
                            $service = app(ExportService::class);
                            $columns = $data['columns'] ?? app(UserExport::class)->defaultColumns();
                            $request = new ExportRequestData(
                                name: __('Users Export'),
                                exportable: UserExport::class,
                                format: $data['format'],
                                columns: $columns,
                                recordIds: $records->pluck('id')->all(),
                                userId: auth()->id(),
                            );

                            $service->queue($request);

                            Notification::make()
                                ->title(__('Export queued'))
                                ->body(__('You will receive a notification once the export has finished.'))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->visible(fn () => AuthorizationMatrix::check('users', 'viewAny')),
                    BulkAction::make('activate')
                        ->label(__('users.actions.activate'))
                        ->icon('heroicon-o-check-circle')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('users.messages.bulk_activate_success'))
                                ->success()
                                ->send();
                        })
                        ->visible(fn () => AuthorizationMatrix::check('users', 'update')),
                    BulkAction::make('deactivate')
                        ->label(__('users.actions.deactivate'))
                        ->icon('heroicon-o-x-circle')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('users.messages.bulk_deactivate_success'))
                                ->success()
                                ->send();
                        })
                        ->visible(fn () => AuthorizationMatrix::check('users', 'update')),
                    DeleteBulkAction::make()
                        ->visible(fn () => AuthorizationMatrix::check('users', 'delete')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Get the resource pages.
     */
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view'   => Pages\ViewUser::route('/{record}'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
