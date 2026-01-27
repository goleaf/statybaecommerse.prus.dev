<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\AddressType;
use App\Models\Address;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * AddressTableWidget
 *
 * Widget displaying recent addresses in a table format
 */
final class AddressTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Addresses';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    /**
     * Get table
     */
    public function table(Table $table): Table
    {
        // Configure the widget table to meet the Filament v4 return type contract.
        return $table
            ->query(
                Address::query()
                    ->with(['user', 'country'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('translations.user'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('display_name')
                    ->label(__('translations.full_name'))
                    ->searchable(['first_name', 'last_name', 'company_name']),
                TextColumn::make('type')
                    ->label(__('translations.type'))
                    ->formatStateUsing(
                        /**
                         * Provide precise typing so PHPStan understands the enum instance.
                         */
                        static function (?AddressType $state): string {
                            // Gracefully fall back to a human readable string when the enum is missing.
                            if ($state instanceof AddressType) {
                                return $state->label();
                            }

                            $fallback = __('translations.unknown');

                            return $fallback === 'translations.unknown' ? __('Unknown') : $fallback;
                        }
                    )
                    ->badge()
                    ->color(
                        /**
                         * Map the enum to Filament colour names with a clear fallback for unexpected values.
                         */
                        fn (?AddressType $state): string => match ($state) {
                            AddressType::SHIPPING => 'primary',
                            AddressType::BILLING  => 'success',
                            AddressType::HOME     => 'warning',
                            AddressType::WORK     => 'info',
                            AddressType::OTHER    => 'secondary',
                            default               => 'gray',
                        }
                    ),
                TextColumn::make('city')
                    ->label(__('translations.city'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('country.name')
                    ->label(__('translations.country'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('is_active')
                    ->label(__('translations.is_active'))
                    ->formatStateUsing(fn ($state) => $state ? __('translations.yes') : __('translations.no'))
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('translations.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
