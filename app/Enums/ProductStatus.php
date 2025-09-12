<?php declare(strict_types=1);

namespace App\Enums;

use Illuminate\Support\Collection;

enum ProductStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case OUT_OF_STOCK = 'out_of_stock';
    case DISCONTINUED = 'discontinued';
    case ARCHIVED = 'archived';
    case PENDING_REVIEW = 'pending_review';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => __('translations.product_status_draft'),
            self::ACTIVE => __('translations.product_status_active'),
            self::INACTIVE => __('translations.product_status_inactive'),
            self::OUT_OF_STOCK => __('translations.product_status_out_of_stock'),
            self::DISCONTINUED => __('translations.product_status_discontinued'),
            self::ARCHIVED => __('translations.product_status_archived'),
            self::PENDING_REVIEW => __('translations.product_status_pending_review'),
            self::REJECTED => __('translations.product_status_rejected'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::DRAFT => __('translations.product_status_draft_description'),
            self::ACTIVE => __('translations.product_status_active_description'),
            self::INACTIVE => __('translations.product_status_inactive_description'),
            self::OUT_OF_STOCK => __('translations.product_status_out_of_stock_description'),
            self::DISCONTINUED => __('translations.product_status_discontinued_description'),
            self::ARCHIVED => __('translations.product_status_archived_description'),
            self::PENDING_REVIEW => __('translations.product_status_pending_review_description'),
            self::REJECTED => __('translations.product_status_rejected_description'),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DRAFT => 'heroicon-o-document-text',
            self::ACTIVE => 'heroicon-o-check-circle',
            self::INACTIVE => 'heroicon-o-pause-circle',
            self::OUT_OF_STOCK => 'heroicon-o-x-circle',
            self::DISCONTINUED => 'heroicon-o-no-symbol',
            self::ARCHIVED => 'heroicon-o-archive-box',
            self::PENDING_REVIEW => 'heroicon-o-clock',
            self::REJECTED => 'heroicon-o-exclamation-triangle',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::ACTIVE => 'green',
            self::INACTIVE => 'yellow',
            self::OUT_OF_STOCK => 'red',
            self::DISCONTINUED => 'red',
            self::ARCHIVED => 'gray',
            self::PENDING_REVIEW => 'blue',
            self::REJECTED => 'red',
        };
    }

    public function priority(): int
    {
        return match ($this) {
            self::ACTIVE => 1,
            self::PENDING_REVIEW => 2,
            self::OUT_OF_STOCK => 3,
            self::INACTIVE => 4,
            self::DRAFT => 5,
            self::REJECTED => 6,
            self::DISCONTINUED => 7,
            self::ARCHIVED => 8,
        };
    }

    public function isVisible(): bool
    {
        return match ($this) {
            self::ACTIVE => true,
            default => false,
        };
    }

    public function isPurchasable(): bool
    {
        return match ($this) {
            self::ACTIVE => true,
            default => false,
        };
    }

    public function isEditable(): bool
    {
        return match ($this) {
            self::DRAFT, self::INACTIVE, self::REJECTED => true,
            default => false,
        };
    }

    public function isPublishable(): bool
    {
        return match ($this) {
            self::DRAFT, self::INACTIVE, self::REJECTED => true,
            default => false,
        };
    }

    public function isArchivable(): bool
    {
        return match ($this) {
            self::DISCONTINUED, self::INACTIVE => true,
            default => false,
        };
    }

    public function isDiscontinuable(): bool
    {
        return match ($this) {
            self::ACTIVE, self::OUT_OF_STOCK => true,
            default => false,
        };
    }

    public function requiresReview(): bool
    {
        return match ($this) {
            self::PENDING_REVIEW => true,
            default => false,
        };
    }

    public function canBeActivated(): bool
    {
        return match ($this) {
            self::DRAFT, self::INACTIVE, self::REJECTED => true,
            default => false,
        };
    }

    public function canBeDeactivated(): bool
    {
        return match ($this) {
            self::ACTIVE, self::OUT_OF_STOCK => true,
            default => false,
        };
    }

    public function nextStatuses(): array
    {
        return match ($this) {
            self::DRAFT => [self::PENDING_REVIEW, self::ACTIVE, self::ARCHIVED],
            self::PENDING_REVIEW => [self::ACTIVE, self::REJECTED],
            self::ACTIVE => [self::INACTIVE, self::OUT_OF_STOCK, self::DISCONTINUED],
            self::INACTIVE => [self::ACTIVE, self::ARCHIVED],
            self::OUT_OF_STOCK => [self::ACTIVE, self::DISCONTINUED],
            self::REJECTED => [self::DRAFT, self::PENDING_REVIEW],
            self::DISCONTINUED => [self::ARCHIVED],
            self::ARCHIVED => [],
        };
    }

    public function previousStatuses(): array
    {
        return match ($this) {
            self::PENDING_REVIEW => [self::DRAFT, self::REJECTED],
            self::ACTIVE => [self::DRAFT, self::PENDING_REVIEW, self::INACTIVE, self::OUT_OF_STOCK],
            self::INACTIVE => [self::ACTIVE],
            self::OUT_OF_STOCK => [self::ACTIVE],
            self::REJECTED => [self::PENDING_REVIEW],
            self::DISCONTINUED => [self::ACTIVE, self::OUT_OF_STOCK],
            self::ARCHIVED => [self::DRAFT, self::INACTIVE, self::DISCONTINUED],
            default => [],
        };
    }

    public function seoImpact(): string
    {
        return match ($this) {
            self::ACTIVE => 'positive',
            self::OUT_OF_STOCK => 'neutral',
            self::INACTIVE => 'negative',
            self::DISCONTINUED => 'negative',
            self::ARCHIVED => 'negative',
            default => 'none',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->sortBy('priority')
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }

    public static function optionsWithDescriptions(): array
    {
        return collect(self::cases())
            ->sortBy('priority')
            ->mapWithKeys(fn($case) => [
                $case->value => [
                    'label' => $case->label(),
                    'description' => $case->description(),
                    'icon' => $case->icon(),
                    'color' => $case->color(),
                    'priority' => $case->priority(),
                    'is_visible' => $case->isVisible(),
                    'is_purchasable' => $case->isPurchasable(),
                    'is_editable' => $case->isEditable(),
                    'is_publishable' => $case->isPublishable(),
                    'is_archivable' => $case->isArchivable(),
                    'is_discontinuable' => $case->isDiscontinuable(),
                    'requires_review' => $case->requiresReview(),
                    'can_be_activated' => $case->canBeActivated(),
                    'can_be_deactivated' => $case->canBeDeactivated(),
                    'next_statuses' => $case->nextStatuses(),
                    'previous_statuses' => $case->previousStatuses(),
                    'seo_impact' => $case->seoImpact(),
                ]
            ])
            ->toArray();
    }

    public static function visible(): Collection
    {
        return collect(self::cases())
            ->filter(fn($case) => $case->isVisible());
    }

    public static function purchasable(): Collection
    {
        return collect(self::cases())
            ->filter(fn($case) => $case->isPurchasable());
    }

    public static function editable(): Collection
    {
        return collect(self::cases())
            ->filter(fn($case) => $case->isEditable());
    }

    public static function publishable(): Collection
    {
        return collect(self::cases())
            ->filter(fn($case) => $case->isPublishable());
    }

    public static function ordered(): Collection
    {
        return collect(self::cases())
            ->sortBy('priority');
    }

    public static function fromLabel(string $label): ?self
    {
        return collect(self::cases())
            ->first(fn($case) => $case->label() === $label);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return collect(self::cases())
            ->map(fn($case) => $case->label())
            ->toArray();
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label(),
            'description' => $this->description(),
            'icon' => $this->icon(),
            'color' => $this->color(),
            'priority' => $this->priority(),
            'is_visible' => $this->isVisible(),
            'is_purchasable' => $this->isPurchasable(),
            'is_editable' => $this->isEditable(),
            'is_publishable' => $this->isPublishable(),
            'is_archivable' => $this->isArchivable(),
            'is_discontinuable' => $this->isDiscontinuable(),
            'requires_review' => $this->requiresReview(),
            'can_be_activated' => $this->canBeActivated(),
            'can_be_deactivated' => $this->canBeDeactivated(),
            'next_statuses' => $this->nextStatuses(),
            'previous_statuses' => $this->previousStatuses(),
            'seo_impact' => $this->seoImpact(),
        ];
    }
}
