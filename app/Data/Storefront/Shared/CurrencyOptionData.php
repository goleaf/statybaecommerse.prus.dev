<?php

declare(strict_types=1);

namespace App\Data\Storefront\Shared;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Lightweight DTO used by the currency selector widget so cached payloads remain predictable.
 */
final class CurrencyOptionData implements Arrayable
{
    public function __construct(
        public readonly int $id,
        public readonly string $code,
        public readonly string $symbol,
    ) {
        // DTO remains intentionally small because the widget only needs code and symbol when rendering.
    }

    /**
     * Hydrate the DTO from primitive arrays (used when loading cached data).
     *
     * @param  array{id:int, code:string, symbol:string} $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            (int) ($payload['id'] ?? 0),
            (string) ($payload['code'] ?? ''),
            (string) ($payload['symbol'] ?? ''),
        );
    }

    /**
     * Convert to array representation for serialization.
     *
     * @return array{id:int, code:string, symbol:string}
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
