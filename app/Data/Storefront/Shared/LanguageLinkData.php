<?php

declare(strict_types=1);

namespace App\Data\Storefront\Shared;

use Illuminate\Contracts\Support\Arrayable;

/**
 * DTO representing a localized navigation link for the language switcher widget.
 */
final class LanguageLinkData implements Arrayable
{
    public function __construct(
        public readonly string $locale,
        public readonly string $label,
        public readonly string $url,
        public readonly bool $active,
    ) {
        // No additional logic required; the widget only consumes these primitives.
    }

    /**
     * Rehydrate a DTO instance from cached array data.
     *
     * @param array{locale:string, label:string, url:string, active:bool} $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            (string) ($payload['locale'] ?? ''),
            (string) ($payload['label'] ?? ''),
            (string) ($payload['url'] ?? ''),
            (bool) ($payload['active'] ?? false),
        );
    }

    /**
     * Export to primitive array representation for caching/serialization.
     *
     * @return array{locale:string, label:string, url:string, active:bool}
     */
    public function toArray(): array
    {
        return [
            'locale' => $this->locale,
            'label'  => $this->label,
            'url'    => $this->url,
            'active' => $this->active,
        ];
    }
}
