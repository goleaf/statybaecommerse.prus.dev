<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ApiKeyScope;
use App\Filament\Resources\ApiKeyResource\Pages;
use App\Models\ApiKey;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\View as ViewComponent;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Actions\Action as TableAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction as TableDeleteAction;
use Filament\Actions\EditAction as TableEditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use UnitEnum;

/**
 * @codeCoverageIgnore
 */
final class ApiKeyResource extends Resource
{
    protected static ?string $model = ApiKey::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-key';

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return __('navigation.groups.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('api_keys.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('api_keys.single');
    }

    public static function getPluralModelLabel(): string
    {
        return __('api_keys.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('api_keys.sections.details'))
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label(__('api_keys.fields.name'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('rate_limit')
                        ->label(__('api_keys.fields.rate_limit'))
                        ->numeric()
                        ->minValue(1)
                        ->nullable()
                        ->helperText(__('api_keys.helpers.rate_limit')),
                    CheckboxList::make('permissions')
                        ->label(__('api_keys.fields.scopes'))
                        ->options(ApiKeyScope::options())
                        ->columns(2)
                        ->helperText(__('api_keys.helpers.scopes'))
                        ->columnSpanFull(),
                    Toggle::make('is_active')
                        ->label(__('api_keys.fields.is_active'))
                        ->default(true),
                ]),
            Section::make(__('api_keys.sections.credentials'))
                ->schema([
                    Placeholder::make('masked_key')
                        ->label(__('api_keys.fields.masked_key'))
                        ->content(static fn (?ApiKey $record): string => $record?->maskKey() ?? __('api_keys.messages.no_key')),
                    ViewComponent::make('filament.forms.components.api-key-credentials')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('api_keys.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('permissions')
                    ->label(__('api_keys.fields.scopes'))
                    ->formatStateUsing(static fn (?array $state): string => Collection::make($state)
                        ->map(static fn (string $scope): ?string => ApiKeyScope::tryFrom($scope)?->label())
                        ->filter()
                        ->join(', ')
                    )
                    ->badge()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('rate_limit')
                    ->label(__('api_keys.fields.rate_limit'))
                    ->sortable()
                    ->formatStateUsing(static fn (?int $state): string => $state === null
                        ? __('api_keys.messages.unlimited')
                        : __('api_keys.messages.requests_per_minute', ['value' => $state])
                    ),
                IconColumn::make('is_active')
                    ->label(__('api_keys.fields.is_active'))
                    ->boolean()
                    ->toggleable(),
                TextColumn::make('last_used_at')
                    ->label(__('api_keys.fields.last_used_at'))
                    ->dateTime()
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                ActionGroup::make([
                    TableAction::make('reveal')
                        ->label(__('api_keys.actions.reveal_key'))
                        ->icon('heroicon-m-eye')
                        ->modalHeading(__('api_keys.modals.reveal_key.heading'))
                        ->modalContent(static fn (ApiKey $record): View => view('filament.api-keys.reveal-key', [
                            'key' => $record->key,
                            'secret' => $record->secret,
                        ]))
                        ->modalSubmitAction(false),
                    TableAction::make('revoke')
                        ->label(__('api_keys.actions.revoke'))
                        ->icon('heroicon-m-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(static fn (ApiKey $record): bool => $record->is_active)
                        ->action(static fn (ApiKey $record): bool => $record->update(['is_active' => false])),
                    TableAction::make('activate')
                        ->label(__('api_keys.actions.reactivate'))
                        ->icon('heroicon-m-bolt')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(static fn (ApiKey $record): bool => ! $record->is_active)
                        ->action(static fn (ApiKey $record): bool => $record->update(['is_active' => true])),
                    TableAction::make('regenerate')
                        ->label(__('api_keys.actions.regenerate_key'))
                        ->icon('heroicon-m-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(static function (ApiKey $record): void {
                            $credentials = $record->regenerateCredentials();

                            \Filament\Notifications\Notification::make()
                                ->title(__('api_keys.notifications.regenerated.title'))
                                ->body(__('api_keys.notifications.regenerated.body', ['key' => $credentials['key']]))
                                ->success()
                                ->send();
                        }),
                    TableEditAction::make(),
                    TableDeleteAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApiKeys::route('/'),
            'create' => Pages\CreateApiKey::route('/create'),
            'edit' => Pages\EditApiKey::route('/{record}/edit'),
        ];
    }
}
