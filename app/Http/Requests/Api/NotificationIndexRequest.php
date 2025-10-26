<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Data\Notifications\NotificationFilterData;
use App\Data\Notifications\NotificationPaginationOptions;

final class NotificationIndexRequest extends ApiRequest
{
    protected ?string $requiredAbility = 'notifications.read';

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'page'      => ['sometimes', 'integer', 'min:1'],
            'per_page'  => ['sometimes', 'integer', 'min:1', 'max:100'],
            'type'      => ['sometimes', 'string'],
            'read'      => ['sometimes', 'boolean'],
            'sort'      => ['sometimes', 'in:created_at,type'],
            'direction' => ['sometimes', 'in:asc,desc'],
        ];
    }

    public function filters(): NotificationFilterData
    {
        return NotificationFilterData::fromArray($this->validated());
    }

    public function paginationOptions(): NotificationPaginationOptions
    {
        return NotificationPaginationOptions::fromArray($this->validated());
    }
}
