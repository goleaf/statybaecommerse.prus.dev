<?php

declare(strict_types=1);

namespace App\Filament\Resources\CountryResource\RelationManagers;

use App\Filament\RelationManagers\Support\BaseRelationManager;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;
use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Schema;

final class UsersRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Users';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make(__('users.title'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('users.fields.name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label(__('users.fields.email'))
                            ->email()
                            ->required()
                            ->unique(User::class, 'email', ignoreRecord: true),
                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone_number')
                            ->label(__('users.fields.phone_number'))
                            ->tel()
                            ->maxLength(30),
                        Forms\Components\Select::make('preferred_locale')
                            ->label(__('users.fields.preferred_locale'))
                            ->options([
                                'en' => 'English',
                                'lt' => 'Lietuvių',
                            ])
                            ->default('en'),
                    ]),
                Forms\Components\Section::make(__('customers.account_settings'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('users.fields.is_active'))
                            ->default(true),
                        Forms\Components\Toggle::make('is_verified')
                            ->label(__('users.fields.is_verified')),
                        Forms\Components\Toggle::make('accepts_marketing')
                            ->label(__('users.fields.accepts_marketing')),
                        Forms\Components\Toggle::make('is_admin')
                            ->label('Admin')
                            ->helperText('Grants administrative access for this user'),
                    ]),
                Forms\Components\Section::make(__('users.fields.bio'))
                    ->schema([
                        Forms\Components\Textarea::make('bio')
                            ->label(__('users.fields.bio'))
                            ->rows(3),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('users.fields.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('users.fields.email'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('preferred_locale')
                    ->label(__('users.fields.preferred_locale'))
                    ->badge()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('users.fields.is_active'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_verified')
                    ->label(__('users.fields.is_verified'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('accepts_marketing')
                    ->label(__('users.fields.accepts_marketing'))
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('users.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('preferred_locale')
                    ->label(__('users.fields.preferred_locale'))
                    ->options([
                        'en' => 'English',
                        'lt' => 'Lietuvių',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('users.fields.is_active')),
                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label(__('users.fields.is_verified')),
                Tables\Filters\TernaryFilter::make('accepts_marketing')
                    ->label(__('users.fields.accepts_marketing')),
            ])
            ->headerActions([
                RelationManagerRepeaterAction::make()
                    ->label('Quick edit ' . $this->getPluralModelLabel())
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Edit ' . $this->getPluralModelLabel())
                    ->modalWidth('5xl')
                    ->configureRepeater(function (Repeater $repeater): Repeater {
                        // Provide a quick-edit modal for managing records inline.
                        return $repeater->schema($this->getQuickEditSchema());
                    }),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereNull('deleted_at'))
            ->defaultSort('created_at', 'desc');
    }
}