<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentTemplateCategory: string
{
    case Business = 'business';
    case Sales = 'sales';
    case Legal = 'legal';
    case Financial = 'financial';
    case Marketing = 'marketing';
    case Technical = 'technical';
    case Other = 'other';

    public function label(): string
    {
        return __('document_templates.categories.' . $this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Business  => 'primary',
            self::Sales     => 'success',
            self::Legal     => 'danger',
            self::Financial => 'info',
            self::Marketing => 'warning',
            self::Technical => 'gray',
            self::Other     => 'secondary',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case): array => [$case->value => $case->label()])
            ->all();
    }
}
