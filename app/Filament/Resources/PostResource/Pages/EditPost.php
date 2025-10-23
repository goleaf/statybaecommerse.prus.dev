<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostResource\Pages;

use App\Enums\ModerationState;
use App\Filament\Resources\PostResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class EditPost extends EditRecord
{
    use InteractsWithJsonTranslationTabs;

    protected static string $resource = PostResource::class;

    /**
     * @return array<int, string>
     */
    protected function getTranslatableFields(): array
    {
        return ['title', 'excerpt', 'content', 'meta_title', 'meta_description'];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\Action::make('submit_for_review')
                ->label(__('moderation.actions.submit_for_review'))
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->moderation_state === ModerationState::Draft)
                ->action(function (): void {
                    $this->record->update([
                        'moderation_state' => ModerationState::Review,
                        'submitted_for_review_at' => now(),
                        'status' => 'draft',
                    ]);

                    activity()
                        ->performedOn($this->record)
                        ->causedBy(Auth::user())
                        ->event('submitted_for_review')
                        ->log('Post submitted for review');

                    Notification::make()
                        ->title(__('moderation.messages.submitted'))
                        ->success()
                        ->send();
                }),
            Actions\Action::make('approve')
                ->label(__('moderation.actions.approve'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->form([
                    Forms\Components\Textarea::make('notes')
                        ->label(__('posts.approvals.notes'))
                        ->maxLength(500)
                        ->rows(3)
                        ->helperText(__('posts.approvals.notes_help')),
                ])
                ->visible(fn (): bool => $this->record->moderation_state === ModerationState::Review)
                ->action(function (array $data): void {
                    $userId = Auth::id();

                    if (! $userId) {
                        throw new \RuntimeException('Approvals require an authenticated user.');
                    }

                    DB::transaction(function () use ($data, $userId): void {
                        $this->record->approvals()->create([
                            'user_id' => $userId,
                            'decision' => 'approved',
                            'notes' => $data['notes'] ?? null,
                            'decided_at' => now(),
                        ]);

                        $this->record->update([
                            'moderation_state' => ModerationState::Published,
                            'approved_at' => now(),
                            'approved_by_id' => $userId,
                            'status' => 'published',
                            'published_at' => $this->record->published_at ?? now(),
                        ]);
                    });

                    activity()
                        ->performedOn($this->record)
                        ->causedBy(Auth::user())
                        ->event('approved')
                        ->log('Post approved and published');

                    Notification::make()
                        ->title(__('moderation.messages.approved'))
                        ->success()
                        ->send();
                }),
            Actions\Action::make('request_changes')
                ->label(__('moderation.actions.return_to_draft'))
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->requiresConfirmation()
                ->form([
                    Forms\Components\Textarea::make('notes')
                        ->label(__('posts.approvals.notes'))
                        ->maxLength(500)
                        ->rows(3)
                        ->required(),
                ])
                ->visible(fn (): bool => $this->record->moderation_state !== ModerationState::Draft)
                ->action(function (array $data): void {
                    $userId = Auth::id();

                    if (! $userId) {
                        throw new \RuntimeException('Return to draft requires an authenticated user.');
                    }

                    DB::transaction(function () use ($data, $userId): void {
                        $this->record->approvals()->create([
                            'user_id' => $userId,
                            'decision' => 'returned',
                            'notes' => $data['notes'] ?? null,
                            'decided_at' => now(),
                        ]);

                        $this->record->update([
                            'moderation_state' => ModerationState::Draft,
                            'submitted_for_review_at' => null,
                            'approved_at' => null,
                            'approved_by_id' => null,
                            'status' => 'draft',
                        ]);
                    });

                    activity()
                        ->performedOn($this->record)
                        ->causedBy(Auth::user())
                        ->event('returned_to_draft')
                        ->log('Post returned to draft');

                    Notification::make()
                        ->title(__('moderation.messages.returned'))
                        ->warning()
                        ->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->record->moderation_state !== ModerationState::Published) {
            $data['status'] = $data['status'] ?? 'draft';
        }

        unset($data['images'], $data['gallery']);

        return $data;
    }
}
