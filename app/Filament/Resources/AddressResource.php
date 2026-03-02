<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\AddressType;
use App\Filament\Resources\AddressResource\Pages\CreateAddress;
use App\Filament\Resources\AddressResource\Pages\EditAddress;
use App\Filament\Resources\AddressResource\Pages\ListAddresses;
use App\Filament\Resources\AddressResource\Pages\ViewAddress;
use App\Models\Address;
use App\Models\Country;
use App\Models\Scopes\UserOwnedScope;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AddressResource extends Resource
{
    protected static ?string $model = Address::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship(
                        name: 'user',
                        titleAttribute: 'name',
                        modifyQueryUsing: static fn (Builder $query): Builder => $query
                            ->withoutGlobalScopes()
                            ->orderBy('name'),
                    )
                    ->searchable()
                    ->preload()
                    ->default(static fn (): ?int => request()->integer('user_id') ?: null)
                    ->required(),
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
                    ->default('LT')
                    ->required(),
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('admin.navigation.users'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->label(__('messages.type'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('full_name')
                    ->label(__('messages.name'))
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('address_line_1')
                    ->label(__('messages.address_line_1'))
                    ->searchable(),
                TextColumn::make('city')
                    ->label(__('messages.city'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('postal_code')
                    ->label(__('messages.postal_code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('country_code')
                    ->label(__('messages.country'))
                    ->sortable(),
                IconColumn::make('is_default')
                    ->label(__('messages.is_default'))
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label(__('messages.active'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAddresses::route('/'),
            'create' => CreateAddress::route('/create'),
            'view'   => ViewAddress::route('/{record}'),
            'edit'   => EditAddress::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                UserOwnedScope::class,
            ]);
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function normalizePayload(array $data): array
    {
        $data['type'] = AddressType::tryFrom((string) ($data['type'] ?? ''))?->value ?? AddressType::SHIPPING->value;
        $countryCode = strtoupper(trim((string) ($data['country_code'] ?? '')));
        $data['country_code'] = strlen($countryCode) === 2 ? $countryCode : 'LT';
        $data['is_default'] = (bool) ($data['is_default'] ?? false);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        $data['user_id'] = is_numeric($data['user_id'] ?? null) ? (int) $data['user_id'] : null;

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
