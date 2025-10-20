<?php

declare(strict_types=1);

namespace App\Application\DTOs\Notifications;

use InvalidArgumentException;

/**
 * @phpstan-type TagList list<string>
 */
final class NotificationTags
{
    /**
     * @var TagList
     */
    private array $tags;

    /**
     * @param iterable<string> $tags
     */
    private function __construct(iterable $tags)
    {
        $normalised = [];
        foreach ($tags as $tag) {
            $trimmed = trim($tag);
            if ($trimmed === '') {
                throw new InvalidArgumentException('Notification tags must be non-empty strings.');
            }

            $normalised[] = $trimmed;
        }

        $this->tags = array_values(array_unique($normalised));
    }

    public static function none(): self
    {
        return new self([]);
    }

    /**
     * @param iterable<string> $tags
     */
    public static function from(iterable $tags): self
    {
        if (! is_iterable($tags)) {
            throw new InvalidArgumentException('Tags value must be iterable.');
        }

        return new self($tags);
    }

    /**
     * @return TagList
     */
    public function toArray(): array
    {
        return $this->tags;
    }
}
