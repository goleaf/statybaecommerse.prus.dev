<?php

declare(strict_types=1);

namespace App\Enums;

use App\Contracts\EnumInterface;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Collection;

/**
 * Industry
 *
 * Enumeration describing the industry sectors for companies.
 */
enum Industry: string implements EnumInterface, HasLabel
{
    case CONSTRUCTION = 'construction';
    case TECHNOLOGY = 'technology';
    case MANUFACTURING = 'manufacturing';
    case RETAIL = 'retail';
    case FINANCE = 'finance';
    case EDUCATION = 'education';
    case HEALTHCARE = 'healthcare';
    case OTHER = 'other';

    private const LABEL_DEFAULTS = [
        'construction'  => 'Construction',
        'technology'    => 'Technology',
        'manufacturing' => 'Manufacturing',
        'retail'        => 'Retail',
        'finance'       => 'Finance',
        'education'     => 'Education',
        'healthcare'    => 'Healthcare',
        'other'         => 'Other',
    ];

    private static function translate(string $key, string $default): string
    {
        $translation = __($key);

        if (! is_string($translation) || $translation === $key) {
            return $default;
        }

        return $translation;
    }

    public function label(): string
    {
        return self::translate('enums.industry.' . $this->value, self::LABEL_DEFAULTS[$this->value]);
    }

    public function getLabel(): ?string
    {
        return $this->label();
    }

    public function description(): string
    {
        return '';
    }

    public function icon(): string
    {
        return match ($this) {
            self::CONSTRUCTION  => 'heroicon-o-truck',
            self::TECHNOLOGY    => 'heroicon-o-cpu-chip',
            self::MANUFACTURING => 'heroicon-o-building-factory',
            self::RETAIL        => 'heroicon-o-shopping-bag',
            self::FINANCE       => 'heroicon-o-banknotes',
            self::EDUCATION     => 'heroicon-o-academic-cap',
            self::HEALTHCARE    => 'heroicon-o-heart',
            self::OTHER         => 'heroicon-o-ellipsis-horizontal',
        };
    }

    public function color(): string
    {
        return 'gray';
    }

    public function priority(): int
    {
        return 0;
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label(),
        ];
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return self::collection()
            ->map(fn (self $case): string => $case->label())
            ->toArray();
    }

    public static function options(): array
    {
        return self::collection()
            ->mapWithKeys(fn (self $case): array => [$case->value => $case->label()])
            ->toArray();
    }

    public static function optionsWithDescriptions(): array
    {
        return self::options();
    }

    public static function ordered(): Collection
    {
        return self::collection();
    }

    public static function fromLabel(string $label): ?static
    {
        return self::collection()->first(fn (self $case): bool => $case->label() === $label);
    }

    public static function collection(): Collection
    {
        return collect(self::cases());
    }
}
