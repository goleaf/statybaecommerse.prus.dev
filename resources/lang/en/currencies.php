<?php

declare(strict_types=1);

return [
    'plural' => 'Currencies',
    'single' => 'Currency',

    'basic_information' => 'Basic Information',
    'name'              => 'Name',
    'code'              => 'Currency Code',
    'code_help'         => 'Use the three-letter ISO currency code, for example EUR or USD.',
    'symbol'            => 'Symbol',
    'symbol_help'       => 'Visible mark shown alongside prices, such as €, $, or £.',
    'iso_code'          => 'ISO Identifier',
    'iso_code_help'     => 'Optional extended ISO or banking identifier for internal tracking.',
    'description'       => 'Description',

    'exchange_rates'     => 'Exchange Rates',
    'exchange_rate'      => 'Exchange Rate',
    'exchange_rate_help' => 'Set the conversion rate against your chosen base currency.',
    'base_currency'      => 'Base Currency',
    'base_currency_help' => 'The currency used as the reference point for conversions.',

    'formatting'      => 'Formatting',
    'decimal_places'  => 'Decimal Places',
    'symbol_position' => 'Symbol Position',
    'positions'       => [
        'before' => 'Before the amount',
        'after'  => 'After the amount',
    ],
    'thousands_separator'      => 'Thousands Separator',
    'thousands_separator_help' => 'Character used to separate thousands, for example , or space.',
    'decimal_separator'        => 'Decimal Separator',
    'decimal_separator_help'   => 'Character used to separate decimals, for example . or ,.',

    'settings'         => 'Settings',
    'is_active'        => 'Active',
    'is_default'       => 'Default Currency',
    'sort_order'       => 'Sort Order',
    'auto_update_rate' => 'Auto-update rate',

    'created_at' => 'Created At',
    'updated_at' => 'Updated At',

    'active_only'        => 'Active only',
    'inactive_only'      => 'Inactive only',
    'default_only'       => 'Default only',
    'non_default_only'   => 'Non-default only',
    'auto_update_only'   => 'Auto update only',
    'manual_update_only' => 'Manual update only',

    'deactivate'                  => 'Deactivate',
    'activate'                    => 'Activate',
    'activated_successfully'      => 'Currency activated successfully.',
    'deactivated_successfully'    => 'Currency deactivated successfully.',
    'set_default'                 => 'Set as default',
    'set_as_default_successfully' => 'Currency set as default successfully.',
    'update_rate'                 => 'Update rate',
    'rate_updated_successfully'   => 'Currency rate updated successfully.',

    'activate_selected'          => 'Activate selected',
    'deactivate_selected'        => 'Deactivate selected',
    'bulk_activated_success'     => 'Selected currencies activated successfully.',
    'bulk_deactivated_success'   => 'Selected currencies deactivated successfully.',
    'update_rates'               => 'Update rates',
    'rates_updated_successfully' => 'Exchange rates updated successfully.',
];
