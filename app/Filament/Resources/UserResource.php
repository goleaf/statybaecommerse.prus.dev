<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Enums\NavigationGroup;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use BackedEnum;
use UnitEnum;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Form;

/**
 * UserResource
 * 
 * Filament v4 resource for User management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class UserResource extends Resource
{
    protected static ?string $model = User::class;
    
    protected static $navigationGroup = 'Users';
    
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
        return 'Users'->label();
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
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
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

                            DatePicker::make('date_of_birth')
                                ->label(__('users.fields.date_of_birth'))
                                ->columnSpan(1),
                        ]),

                    Textarea::make('bio')
                        ->label(__('users.fields.bio'))
                        ->maxLength(1000)
                        ->rows(3),
                ])
                ->columns(1),

            Section::make(__('users.sections.preferences'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('locale')
                                ->label(__('users.fields.locale'))
                                ->options([
                                    'lt' => 'Lietuvių',
                                    'en' => 'English',
                                ])
                                ->default('lt')
                                ->columnSpan(1),

                            Select::make('timezone')
                                ->label(__('users.fields.timezone'))
                                ->options([
                                    'Europe/Vilnius' => 'Europe/Vilnius',
                                    'UTC' => 'UTC',
                                ])
                                ->default('Europe/Vilnius')
                                ->columnSpan(1),
                        ]),

                    Toggle::make('email_notifications')
                        ->label(__('users.fields.email_notifications'))
                        ->default(true),

                    Toggle::make('sms_notifications')
                        ->label(__('users.fields.sms_notifications'))
                        ->default(false),
                ])
                ->columns(1),

            Section::make(__('users.sections.status'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('users.fields.is_active'))
                                ->default(true)
                                ->columnSpan(1),

                            DatePicker::make('email_verified_at')
                                ->label(__('users.fields.email_verified_at'))
                                ->columnSpan(1),
                        ]),
                ])
                ->columns(1),
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

                    DeleteBulkAction::make(),
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
