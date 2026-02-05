<?php

declare(strict_types=1);

namespace App\Enums;

use App\Contracts\EnumInterface;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Collection;

enum OrganizationType: string implements EnumInterface, HasLabel
{
    case COMPANY = 'company';
    case TEAM = 'team';
    case DEPARTMENT = 'department';

    private const LABEL_DEFAULTS = [
        'company'    => 'Company',
        'team'       => 'Team',
        'department' => 'Department',
    ];

    public function label(): string
    {
        $key = 'enums.organization_type.' . $this->value;

        $translated = __($key);

        if (! is_string($translated) || $translated === $key) {
            return self::LABEL_DEFAULTS[$this->value] ?? $this->value;
        }

        return $translated;
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
            self::COMPANY => 'heroicon-o-building-office',
            self::TEAM => 'heroicon-o-user-group',
            self::DEPARTMENT => 'heroicon-o-squares-2x2',
        };
    }

    public function color(): string
    {
        return 'gray';
    }

    public function priority(): int
    {
        return match ($this) {
            self::COMPANY => 1,
            self::TEAM => 2,
            self::DEPARTMENT => 3,
        };
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
            ->map(static fn (self $case): string => $case->label())
            ->toArray();
    }

    public static function options(): array
    {
        return self::collection()
            ->sortBy(static fn (self $case): int => $case->priority())
            ->mapWithKeys(static fn (self $case): array => [$case->value => $case->label()])
            ->toArray();
    }

    public static function optionsWithDescriptions(): array
    {
        return self::options();
    }

    public static function ordered(): Collection
    {
        return self::collection()->sortBy(static fn (self $case): int => $case->priority());
    }

    public static function fromLabel(string $label): ?static
    {
        return self::collection()->first(static fn (self $case): bool => $case->label() === $label);
    }

    public static function collection(): Collection
    {
        return collect(self::cases());
    }
}
