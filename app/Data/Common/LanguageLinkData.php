<?php

declare(strict_types=1);

namespace App\Data\Common;

/**
 * LanguageLinkData
 *
 * Represents a localized link rendered by the language switcher.
 */
final class LanguageLinkData
{
    /**
     * Construct a new language link payload.
     */
    public function __construct(
        public readonly string $locale,
        public readonly string $label,
        public readonly string $url,
        public readonly bool $active,
    ) {
        // Simple DTO for repeated use across Livewire views.
    }

    /**
     * Flatten the link to an array for Blade friendly consumption.
     *
     * @return array{locale:string,label:string,url:string,active:bool}
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
