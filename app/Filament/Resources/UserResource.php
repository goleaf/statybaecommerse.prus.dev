<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use BackedEnum;
use UnitEnum;

/**
 * UserResource
 *
 * Filament v4 resource for User management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('users.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::Users->label();
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('users.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
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
        return $schema->schema([
            Section::make(__('users.sections.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('users.fields.name'))
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(1),
                            TextInput::make('email')
                                ->label(__('users.fields.email'))
                                ->email()
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255)
                                ->columnSpan(1),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('phone')
                                ->label(__('users.fields.phone'))
                                ->tel()
                                ->maxLength(20)
                                ->columnSpan(1),
                            Select::make('locale')
                                ->label(__('users.fields.locale'))
                                ->options([
                                    'lt' => __('users.fields.locale_lt'),
                                    'en' => __('users.fields.locale_en'),
                                ])
                                ->default('lt')
                                ->columnSpan(1),
                        ]),
                    Textarea::make('bio')
                        ->label(__('users.fields.bio'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            Section::make(__('users.sections.security'))
                ->schema([
                    TextInput::make('password')
                        ->label(__('users.fields.password'))
                        ->password()
                        ->dehydrateStateUsing(fn($state) => filled($state) ? bcrypt($state) : null)
                        ->dehydrated(fn($state) => filled($state))
                        ->required(fn(string $context): bool => $context === 'create')
                        ->columnSpan(1),
                    TextInput::make('password_confirmation')
                        ->label(__('users.fields.password_confirmation'))
                        ->password()
                        ->required(fn(string $context): bool => $context === 'create')
                        ->same('password')
                        ->dehydrated(false)
                        ->columnSpan(1),
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
                TextColumn::make('name')
                    ->label(__('users.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('users.fields.email'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label(__('users.fields.phone'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('locale')
                    ->label(__('users.fields.locale'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
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
                    ->label(__('users.fields.locale'))
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Get the resource pages.
     * @return array
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
