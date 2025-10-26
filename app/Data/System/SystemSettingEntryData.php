<?php

declare(strict_types=1);

namespace App\Data\System;

/**
 * SystemSettingEntryData
 *
 * Represents a flattened key/value pair for system settings displays.
 */
final class SystemSettingEntryData
{
    /**
     * Instantiate a new setting entry.
     */
    public function __construct(
        public readonly string $key,
        public readonly mixed $value,
        public readonly bool $isPublic,
    ) {
        // Mixed values remain documented for view level formatting.
    }

    /**
     * Convert to an array used by Livewire templates.
     *
     * @return array{key:string,value:mixed,isPublic:bool}
     */
    public function toArray(): array
    {
        return [
            'key'      => $this->key,
            'value'    => $this->value,
            'isPublic' => $this->isPublic,
        ];
    }
}
