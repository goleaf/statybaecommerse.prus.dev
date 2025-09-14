<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\ValidationContext;

final class NewsCommentData extends Data
{
    public function __construct(
        #[Nullable, IntegerType, Exists('news_comments', 'id')]
        public ?int $parent_id,

        #[Required, StringType, Max(255)]
        public string $author_name,

        #[Required, Email, Max(255)]
        public string $author_email,

        #[Required, StringType, Max(2000)]
        public string $content,
    ) {
    }

    public static function messages(ValidationContext $context): array
    {
        return [
            'author_name.required' => __('validation.required', ['attribute' => __('news.comment_name')]),
            'author_name.max' => __('validation.max.string', ['attribute' => __('news.comment_name'), 'max' => 255]),
            'author_email.required' => __('validation.required', ['attribute' => __('news.comment_email')]),
            'author_email.email' => __('validation.email', ['attribute' => __('news.comment_email')]),
            'author_email.max' => __('validation.max.string', ['attribute' => __('news.comment_email'), 'max' => 255]),
            'content.required' => __('validation.required', ['attribute' => __('news.comment_content')]),
            'content.max' => __('validation.max.string', ['attribute' => __('news.comment_content'), 'max' => 2000]),
            'parent_id.exists' => __('validation.exists', ['attribute' => 'parent comment']),
        ];
    }
}
