<?php

declare(strict_types=1);

namespace App\Data\Notifications;

use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

final class NotificationSearchParametersData
{
    private function __construct(
        private readonly string $term,
        private readonly NotificationFilterData $filters,
    ) {
        if ($this->term === '') {
            throw new InvalidArgumentException('Search term cannot be empty.');
        }
    }

    /**
     * @param array<string, mixed> $input
     */
    public static function fromArray(array $input): self
    {
        $term = trim((string) ($input['q'] ?? ''));
        if ($term === '') {
            throw new InvalidArgumentException('Search term is required.');
        }

        return new self($term, NotificationFilterData::fromArray($input));
    }

    public function term(): string
    {
        return $this->term;
    }

    public function filters(): NotificationFilterData
    {
        return $this->filters;
    }

    public function apply(Builder $builder): Builder
    {
        $term = '%' . $this->term . '%';

        $builder->where(static function (Builder $query) use ($term): void {
            $query->where('data->title', 'like', $term)
                ->orWhere('data->message', 'like', $term)
                ->orWhere('data->type', 'like', $term);
        });

        return $this->filters->apply($builder);
    }
}
