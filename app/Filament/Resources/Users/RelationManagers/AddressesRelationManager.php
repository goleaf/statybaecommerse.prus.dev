<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Enums\AddressType;
use App\Filament\RelationManagers\Concerns\ResolvesOwnerPageRedirect;
use App\Filament\Resources\AddressResource;
use App\Filament\Resources\UserResource;
use App\Models\Address;
use App\Models\Country;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AddressesRelationManager extends RelationManager
{
    use ResolvesOwnerPageRedirect;

    protected static string $relationship = 'addresses';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->options(AddressType::options())
                    ->label(__('messages.type'))
                    ->default(AddressType::SHIPPING->value)
                    ->required(),
                TextInput::make('first_name')
                    ->label(__('messages.first_name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->label(__('messages.last_name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('company')
                    ->label(__('messages.company'))
                    ->maxLength(255),
                TextInput::make('company_vat')
                    ->label(__('messages.company_vat'))
                    ->maxLength(50),
                TextInput::make('address_line_1')
                    ->label(__('messages.address_line_1'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('address_line_2')
                    ->label(__('messages.address_line_2'))
                    ->maxLength(255),
                TextInput::make('city')
                    ->label(__('messages.city'))
                    ->required()
                    ->maxLength(100),
                TextInput::make('postal_code')
                    ->label(__('messages.postal_code'))
                    ->required()
                    ->maxLength(20),
                Select::make('country_code')
                    ->label(__('messages.country'))
                    ->options(static fn (): array => self::countryOptions())
                    ->searchable()
                    ->preload()
                    ->getSearchResultsUsing(static function (string $search): array {
                        $needle = '%' . trim($search) . '%';

                        return Country::query()
                            ->withoutGlobalScopes()
                            ->where(function (Builder $query) use ($needle): void {
                                $query
                                    ->where('cca2', 'like', $needle)
                                    ->orWhere('name', 'like', $needle);
                            })
                            ->orderByRaw('COALESCE(name, cca2)')
                            ->limit(50)
                            ->get(['cca2', 'name'])
                            ->mapWithKeys(static fn (Country $country): array => [
                                strtoupper((string) $country->cca2) => self::countryLabel($country),
                            ])
                            ->all();
                    })
                    ->getOptionLabelUsing(static function (mixed $value): ?string {
                        if (! is_scalar($value)) {
                            return null;
                        }

                        $code = strtoupper(trim((string) $value));

                        if ($code === '') {
                            return null;
                        }

                        $country = Country::query()
                            ->withoutGlobalScopes()
                            ->where('cca2', $code)
                            ->first(['cca2', 'name']);

                        if ($country instanceof Country) {
                            return self::countryLabel($country);
                        }

                        return $code;
                    })
                    ->default('LT')
                    ->required()
                    ->native(false),
                TextInput::make('phone')
                    ->label(__('messages.phone'))
                    ->tel()
                    ->maxLength(20),
                TextInput::make('email')
                    ->label(__('messages.email'))
                    ->email()
                    ->maxLength(255),
                Toggle::make('is_default')
                    ->label(__('messages.is_default')),
                Toggle::make('is_active')
                    ->label(__('messages.active'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(static fn (Builder $query): Builder => $query->withoutGlobalScopes())
            ->recordTitleAttribute('address_line_1')
            ->columns([
                TextColumn::make('type')
                    ->sortable()
                    ->label(__('messages.type'))
                    ->badge()
                    ->searchable(),
                TextColumn::make('full_name')
                    ->label(__('messages.name'))
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('address_line_1')
                    ->sortable()
                    ->label(__('messages.address_line_1'))
                    ->searchable(),
                TextColumn::make('city')
                    ->sortable()
                    ->label(__('messages.city'))
                    ->searchable(),
                TextColumn::make('postal_code')
                    ->sortable()
                    ->label(__('messages.postal_code'))
                    ->searchable(),
                TextColumn::make('country.name')
                    ->label(__('messages.country'))
                    ->searchable(),
                IconColumn::make('is_default')
                    ->sortable()
                    ->boolean()
                    ->label(__('messages.is_default')),
                IconColumn::make('is_active')
                    ->sortable()
                    ->boolean()
                    ->label(__('messages.active')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('create')
                    ->icon('heroicon-m-plus')
                    ->url(fn (): string => AddressResource::getUrl('create', [
                        'user_id'  => $this->getOwnerRecord()->getKey(),
                        'redirect' => $this->resolveOwnerPageRedirectUrl(UserResource::class),
                    ])),
            ])
            ->recordActions([
                TableAction::make('set_default')
                    ->label(__('messages.set_as_default') ?? 'Set as Default')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Address $record) {
                        Address::withoutGlobalScopes()
                            ->where('user_id', $record->user_id)
                            ->where('id', '!=', $record->id)
                            ->update(['is_default' => false]);

                        Address::withoutGlobalScopes()
                            ->whereKey($record->getKey())
                            ->update(['is_default' => true]);

                        Notification::make()
                            ->title(__('messages.address_set_as_default') ?? 'Address set as default successfully.')
                            ->success()
                            ->send();
                    })
                    ->hidden(fn (Address $record): bool => $record->is_default),
                Action::make('view')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Address $record): string => AddressResource::getUrl('view', [
                        'record'   => $record,
                        'redirect' => $this->resolveOwnerPageRedirectUrl(UserResource::class),
                    ])),
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn (Address $record): string => AddressResource::getUrl('edit', [
                        'record'   => $record,
                        'redirect' => $this->resolveOwnerPageRedirectUrl(UserResource::class),
                    ])),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeAddressData(array $data): array
    {
        $data['type'] = AddressType::tryFrom((string) ($data['type'] ?? ''))?->value ?? AddressType::SHIPPING->value;
        $countryCode = strtoupper(trim((string) ($data['country_code'] ?? '')));
        $data['country_code'] = strlen($countryCode) === 2 ? $countryCode : 'LT';
        $data['is_default'] = (bool) ($data['is_default'] ?? false);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        return $data;
    }

    /**
     * @return array<string, string>
     */
    private static function countryOptions(): array
    {
        return Country::query()
            ->withoutGlobalScopes()
            ->orderByRaw('COALESCE(name, cca2)')
            ->get(['cca2', 'name'])
            ->mapWithKeys(static fn (Country $country): array => [
                strtoupper((string) $country->cca2) => self::countryLabel($country),
            ])
            ->all();
    }

    private static function countryLabel(Country $country): string
    {
        $code = strtoupper((string) $country->cca2);
        $name = trim((string) ($country->name ?? ''));

        if ($name === '') {
            return $code;
        }

        return sprintf('%s (%s)', $name, $code);
    }
}
