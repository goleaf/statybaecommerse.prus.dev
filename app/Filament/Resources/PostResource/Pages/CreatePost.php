<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostResource\Pages;

use App\Enums\ModerationState;
use App\Filament\Resources\PostResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

final class CreatePost extends CreateRecord
{
    use InteractsWithJsonTranslationTabs;

    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['moderation_state'] = $data['moderation_state'] ?? ModerationState::Draft;
        $data['status'] = $data['status'] ?? 'draft';
        $data['submitted_for_review_at'] = null;
        $data['approved_at'] = null;
        $data['approved_by_id'] = null;

        unset($data['images'], $data['gallery']);

        return $data;
    }
}
