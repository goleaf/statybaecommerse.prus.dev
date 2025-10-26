<?php

declare(strict_types=1);

namespace App\Data\Currency;

/**
 * CurrencyOptionData
 *
 * Lightweight view model for rendered currency options.
 */
final class CurrencyOptionData
{
    /**
     * Create a new currency option representation.
     */
    public function __construct(
        public readonly int $id,
        public readonly string $code,
        public readonly string $symbol,
    ) {
        // Keep state minimal for Livewire hydration safety.
    }

    /**
     * Convert the option to an array structure consumed by Blade views.
     *
     * @return array{id:int,code:string,symbol:string}
     */
    public function toArray(): array
    {
        return [
            'id'     => $this->id,
            'code'   => $this->code,
            'symbol' => $this->symbol,
        ];
    }
}
