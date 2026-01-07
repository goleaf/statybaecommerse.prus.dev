<?php

declare(strict_types=1);

namespace App\Data\Orders;

use Illuminate\Database\Eloquent\Builder;
use Spatie\LaravelData\Data;

class OrderFilterData extends Data
{
    public function __construct(
        public ?string $status = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public ?float $minAmount = null,
        public ?float $maxAmount = null,
        public ?string $search = null,
        public ?int $perPage = 15,
    ) {}

    public function apply(Builder $query): void
    {
        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        if ($this->minAmount !== null) {
            $query->where('grand_total_amount', '>=', $this->minAmount);
        }

        if ($this->maxAmount !== null) {
            $query->where('grand_total_amount', '<=', $this->maxAmount);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('number', 'like', "%{$this->search}%")
                    ->orWhereHas('customer', function ($customerQuery) {
                        $customerQuery->where('name', 'like', "%{$this->search}%")
                            ->orWhere('email', 'like', "%{$this->search}%");
                    });
            });
        }
    }
}
