<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ApiKeyScope;
use App\Filament\Resources\ApiKeyResource\Concerns\HandlesApiKeyCredentials;
use App\Filament\Resources\ApiKeyResource\Pages;
use App\Models\ApiKey;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ApiKeyResource extends Resource
{
    use HandlesApiKeyCredentials;

    protected static ?string $model = ApiKey::class;

    protected static ?string $navigationLabel = 'api_keys.navigation.label';

    /**
     * Navigation icon for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationIcon = 'heroicon-o-key';

    /** @var string|BackedEnum|null Using BackedEnum removes the redundant UnitEnum import for navigation grouping. */
    protected static $navigationGroup = null;

    protected static ?int $navigationSort = 4;

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return 'heroicon-o-key';
    }

    public static function getNavigationLabel(): string
    {
        return __('api_keys.navigation.label');
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('navigation.groups.system');
    }

    public static function getModelLabel(): string
    {
        return __('api_keys.navigation.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('api_keys.navigation.plural');
    }

    public static function form(Form $form): Form
    {
        // Filament 4 expects returning the Form builder instance.
        return $form->schema([
            Section::make(__('api_keys.sections.details'))
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label(__('api_keys.fields.name'))
                        ->required()
                        ->maxLength(255)
                        ->placeholder(__('api_keys.placeholders.name')),
                    Toggle::make('active')
                        ->label(__('api_keys.fields.active'))
                        ->default(true),
                    CheckboxList::make('scopes')
                        ->label(__('api_keys.fields.scopes'))
                        ->required()
                        ->options(ApiKeyScope::options())
                        ->helperText(__('api_keys.hints.scopes'))
                        ->columns(2)
                        ->bulkToggleable(),
                    TextInput::make('rate_limit')
                        ->label(__('api_keys.fields.rate_limit'))
                        ->numeric()
                        ->minValue(0)
                        ->hint(__('api_keys.hints.rate_limit'))
                        ->placeholder(__('api_keys.placeholders.rate_limit')),
                ]),
            Section::make(__('api_keys.sections.credentials'))
                ->schema([
                    TextInput::make('plain_text_key')
                        ->label(__('api_keys.fields.plain_text_key'))
                        ->password()
                        ->revealable()
                        ->copyable()
                        ->readOnly()
                        ->dehydrated(false)
                        ->helperText(__('api_keys.hints.generated_once'))
                        ->afterStateHydrated(static function (TextInput $component, ?string $state, ?ApiKey $record): void {
                            if (filled($state)) {
                                return;
                            }

                            $plainText = session()->get(self::getCredentialSessionKey($record));

                            if (filled($plainText)) {
                                $component->state($plainText);
                            }
                        })
                        ->suffixAction(
                            FormAction::make('refresh')
                                ->label(__('api_keys.actions.regenerate'))
                                ->icon('heroicon-o-arrow-path')
                                ->color('warning')
                                ->requiresConfirmation()
                                ->action('generateFreshPlainTextKey')
                        ),
                ]),
            Section::make(__('api_keys.sections.activity'))
                ->columns(3)
                ->visible(fn (?ApiKey $record): bool => $record !== null)
                ->schema([
                    Placeholder::make('last_used_at')
                        ->label(__('api_keys.fields.last_used_at'))
                        ->content(fn (?ApiKey $record): string => $record?->last_used_at?->diffForHumans() ?? '—'),
                    Placeholder::make('created_at')
                        ->label(__('api_keys.fields.created_at'))
                        ->content(fn (?ApiKey $record): string => $record?->created_at?->toDateTimeString() ?? '—'),
                    Placeholder::make('updated_at')
                        ->label(__('api_keys.fields.updated_at'))
                        ->content(fn (?ApiKey $record): string => $record?->updated_at?->toDateTimeString() ?? '—'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('api_keys.fields.name'))
                    ->searchable()
                    ->sortable(),
                TagsColumn::make('scopes')
                    ->label(__('api_keys.fields.scopes'))
                    ->separator(', ')
                    ->formatStateUsing(static fn (?array $state): array => Collection::make($state)
                        ->map(fn (string $scope) => ApiKeyScope::tryFrom($scope)?->label() ?? $scope)
                        ->all())
                    ->limit(3)
                    ->toggleable(),
                TextColumn::make('rate_limit')
                    ->label(__('api_keys.fields.rate_limit'))
                    ->formatStateUsing(fn ($state, ApiKey $record): string => $record->formattedRateLimit())
                    ->sortable(),
                IconColumn::make('active')
                    ->label(__('api_keys.fields.active'))
                    ->boolean(),
                TextColumn::make('last_used_at')
                    ->label(__('api_keys.fields.last_used_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('api_keys.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('active')
                    ->label(__('api_keys.filters.active')),
                SelectFilter::make('scopes')
                    ->label(__('api_keys.filters.scope'))
                    ->multiple()
                    ->options(ApiKeyScope::options())
                    ->query(function (Builder $query, array $values): Builder {
                        $scopes = Collection::make($values)->filter()->values();

                        if ($scopes->isEmpty()) {
                            return $query;
                        }

                        return $scopes->reduce(
                            fn (Builder $builder, string $scope): Builder => $builder->whereJsonContains('scopes', $scope),
                            $query,
                        );
                    }),
            ])
            ->actions([
                TableAction::make('reveal')
                    ->label(__('api_keys.actions.reveal'))
                    ->icon('heroicon-o-eye')
                    ->visible(fn (ApiKey $record): bool => session()->has(self::getCredentialSessionKey($record)))
                    ->modalHeading(fn (ApiKey $record): string => __('api_keys.modals.reveal_title', ['name' => $record->name]))
                    ->modalSubmitAction(false)
                    ->modalIcon('heroicon-o-eye')
                    ->modalCancelActionLabel(__('api_keys.actions.close'))
                    ->modalContent(fn (ApiKey $record) => view('filament.resources.api-key.actions.reveal', [
                        'plainTextKey' => session()->get(self::getCredentialSessionKey($record)),
                    ])),
                TableAction::make('regenerate')
                    ->label(__('api_keys.actions.regenerate'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalContent(fn (ApiKey $record) => view('filament.resources.api-key.actions.regenerate', [
                        'record' => $record,
                    ]))
                    ->modalSubmitActionLabel(__('api_keys.actions.confirm_regenerate'))
                    ->action(static function (ApiKey $record): void {
                        $credentials = ApiKey::generateCredentials();

                        $record->forceFill([
                            'key'          => $credentials['hashed'],
                            'last_used_at' => null,
                        ])->save();

                        session()->flash(self::getCredentialSessionKey($record), $credentials['plain_text']);
                    })
                    ->successRedirectUrl(fn (ApiKey $record): string => Pages\EditApiKey::getUrl(['record' => $record]))
                    ->successNotificationTitle(__('api_keys.notifications.regenerated')),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListApiKeys::route('/'),
            'create' => Pages\CreateApiKey::route('/create'),
            'edit'   => Pages\EditApiKey::route('/{record}/edit'),
        ];
    }
}
