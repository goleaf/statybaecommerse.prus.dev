<?php

declare(strict_types=1);

namespace App\Enums;

use App\Contracts\EnumInterface;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Illuminate\Support\Collection;

enum ProductType: string implements EnumInterface, HasLabel
{
    case SIMPLE = 'simple';
    case VARIABLE = 'variable';
    case VIRTUAL = 'virtual';
    case DOWNLOADABLE = 'downloadable';

    public function label(): string { return __('enums.product_type.' . $this->value); }
    public function getLabel(): ?string { return $this->label(); }
    public function description(): string { return ''; }
    public function icon(): string { return 'heroicon-o-cube'; }
    public function color(): string { return 'gray'; }
    public function priority(): int { return 0; }
    public function toArray(): array { return ['value' => $this->value, 'label' => $this->label()]; }
    public static function values(): array { return array_column(self::cases(), 'value'); }
    public static function labels(): array { return self::collection()->map(fn($c) => $c->label())->toArray(); }
    public static function options(): array { return self::collection()->mapWithKeys(fn($c) => [$c->value => $c->label()])->toArray(); }
    public static function optionsWithDescriptions(): array { return self::options(); }
    public static function ordered(): Collection { return self::collection(); }
    public static function fromLabel(string $label): ?static { return self::collection()->first(fn($c) => $c->label() === $label); }
    public static function collection(): Collection { return collect(self::cases()); }
}
