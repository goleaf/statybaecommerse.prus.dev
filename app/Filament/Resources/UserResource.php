<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

final class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('admin.user.personal_information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('admin.user.name'))
                            ->required()
                            ->maxLength(255),
                        
                        TextInput::make('email')
                            ->label(__('admin.user.email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        
                        TextInput::make('first_name')
                            ->label(__('admin.user.first_name'))
                            ->maxLength(255),
                        
                        TextInput::make('last_name')
                            ->label(__('admin.user.last_name'))
                            ->maxLength(255),
                        
                        Select::make('gender')
                            ->label(__('admin.user.gender'))
                            ->options([
                                'male' => __('admin.user.gender_male'),
                                'female' => __('admin.user.gender_female'),
                                'other' => __('admin.user.gender_other'),
                            ])
                            ->nullable(),
                        
                        DatePicker::make('birth_date')
                            ->label(__('admin.user.birth_date'))
                            ->nullable(),
                        
                        TextInput::make('phone_number')
                            ->label(__('admin.user.phone_number'))
                            ->tel()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                
                Section::make(__('admin.user.account_settings'))
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('admin.user.is_active'))
                            ->default(true),
                        
                        Toggle::make('is_verified')
                            ->label(__('admin.user.is_verified')),
                        
                        Toggle::make('accepts_marketing')
                            ->label(__('admin.user.accepts_marketing')),
                        
                        Toggle::make('two_factor_enabled')
                            ->label(__('admin.user.two_factor_enabled')),
                        
                        Select::make('preferred_locale')
                            ->label(__('admin.user.preferred_locale'))
                            ->options([
                                'lt' => __('admin.locale.lithuanian'),
                                'en' => __('admin.locale.english'),
                            ])
                            ->default('lt'),
                        
                        TextInput::make('timezone')
                            ->label(__('admin.user.timezone'))
                            ->maxLength(255)
                            ->default('Europe/Vilnius'),
                    ])
                    ->columns(2),
                
                Section::make(__('admin.user.company_information'))
                    ->schema([
                        TextInput::make('company')
                            ->label(__('admin.user.company'))
                            ->maxLength(255),
                        
                        TextInput::make('job_title')
                            ->label(__('admin.user.job_title'))
                            ->maxLength(255),
                        
                        TextInput::make('website')
                            ->label(__('admin.user.website'))
                            ->url()
                            ->maxLength(255),
                        
                        Textarea::make('bio')
                            ->label(__('admin.user.bio'))
                            ->rows(3),
                    ])
                    ->columns(2),
                
                Section::make(__('admin.user.preferences'))
                    ->schema([
                        KeyValue::make('preferences')
                            ->label(__('admin.user.preferences'))
                            ->keyLabel(__('admin.user.preference_key'))
                            ->valueLabel(__('admin.user.preference_value')),
                        
                        KeyValue::make('notification_preferences')
                            ->label(__('admin.user.notification_preferences'))
                            ->keyLabel(__('admin.user.notification_key'))
                            ->valueLabel(__('admin.user.notification_value')),
                        
                        KeyValue::make('marketing_preferences')
                            ->label(__('admin.user.marketing_preferences'))
                            ->keyLabel(__('admin.user.marketing_key'))
                            ->valueLabel(__('admin.user.marketing_value')),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('admin.user.id'))
                    ->sortable(),
                
                TextColumn::make('name')
                    ->label(__('admin.user.name'))
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('email')
                    ->label(__('admin.user.email'))
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('first_name')
                    ->label(__('admin.user.first_name'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('last_name')
                    ->label(__('admin.user.last_name'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                BadgeColumn::make('gender')
                    ->label(__('admin.user.gender'))
                    ->colors([
                        'primary' => 'male',
                        'secondary' => 'female',
                        'success' => 'other',
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('phone_number')
                    ->label(__('admin.user.phone_number'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_active')
                    ->label(__('admin.user.is_active'))
                    ->boolean(),
                
                IconColumn::make('is_verified')
                    ->label(__('admin.user.is_verified'))
                    ->boolean(),
                
                IconColumn::make('accepts_marketing')
                    ->label(__('admin.user.accepts_marketing'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('last_login_at')
                    ->label(__('admin.user.last_login_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label(__('admin.user.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label(__('admin.user.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('gender')
                    ->label(__('admin.user.gender'))
                    ->options([
                        'male' => __('admin.user.gender_male'),
                        'female' => __('admin.user.gender_female'),
                        'other' => __('admin.user.gender_other'),
                    ]),
                
                TernaryFilter::make('is_active')
                    ->label(__('admin.user.is_active')),
                
                TernaryFilter::make('is_verified')
                    ->label(__('admin.user.is_verified')),
                
                TernaryFilter::make('accepts_marketing')
                    ->label(__('admin.user.accepts_marketing')),
                
                SelectFilter::make('preferred_locale')
                    ->label(__('admin.user.preferred_locale'))
                    ->options([
                        'lt' => __('admin.locale.lithuanian'),
                        'en' => __('admin.locale.english'),
                    ]),
                
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
