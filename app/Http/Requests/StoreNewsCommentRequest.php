<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreNewsCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'integer', 'exists:news_comments,id'],
            'author_name' => ['required', 'string', 'max:255'],
            'author_email' => ['required', 'email', 'max:255'],
            'content' => ['required', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
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

