<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * EnumInterface
 *
 * Interface contract defining required methods and behavior.
 */
interface EnumInterface
{
    /**
     * Get the human-readable label for the enum case
     */
    public function label(): string;

    /**
     * Get the description for the enum case
     */
    public function description(): string;

    /**
     * Get the icon for the enum case
     */
    public function icon(): string;

    /**
     * Get the color for the enum case
     */
    public function color(): string;

    /**
     * Get the priority/order for the enum case
     */
    public function priority(): int;

    /**
     * Convert the enum case to an array representation
     */
    public function toArray(): array;

    /**
     * Get all enum values as an array
     */
    public static function values(): array;

    /**
     * Get all enum labels as an array
     */
    public static function labels(): array;

    /**
     * Get enum options for select dropdowns
     */
    public static function options(): array;

    /**
     * Get enum options with full metadata
     */
    public static function optionsWithDescriptions(): array;

    /**
     * Get ordered enum cases by priority
     */
    public static function ordered(): \Illuminate\Support\Collection;

    /**
     * Find enum case by label
     */
    public static function fromLabel(string $label): ?static;

    /**
     * Get enum cases as a collection
     */
    public static function collection(): \Illuminate\Support\Collection;
}
