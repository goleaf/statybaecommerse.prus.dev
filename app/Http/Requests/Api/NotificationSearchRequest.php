<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Data\Notifications\NotificationFilterData;
use App\Data\Notifications\NotificationPaginationOptions;
use App\Data\Notifications\NotificationSearchParameters;

final class NotificationSearchRequest extends ApiRequest
{
    protected ?string $requiredAbility = 'notifications.read';

    public function rules(): array
    {
        return [
            'q'         => ['required', 'string', 'min:2'],
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

    public function parameters(): NotificationSearchParameters
    {
        return NotificationSearchParameters::fromArray($this->validated());
    }
}
