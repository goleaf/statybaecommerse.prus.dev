<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsResource\Pages;

use App\Enums\ModerationState;
use App\Filament\Resources\NewsResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class EditNews extends EditRecord
{
    use InteractsWithTranslationTabs, ManagesNewsTranslationTabs {
        ManagesNewsTranslationTabs::getTranslatableFields insteadof InteractsWithTranslationTabs;
        ManagesNewsTranslationTabs::mutateMainDataWithDefaultLocale insteadof InteractsWithTranslationTabs;
        ManagesNewsTranslationTabs::syncTranslationRecords insteadof InteractsWithTranslationTabs;
    }

    protected static string $resource = NewsResource::class;

    /**
     * @var array<string, mixed>
     */
    private array $translationPayload = [];

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
                        'moderation_state'        => ModerationState::Review,
                        'submitted_for_review_at' => now(),
                        'is_visible'              => false,
                    ]);

                    activity()
                        ->performedOn($this->record)
                        ->causedBy(Auth::user())
                        ->event('submitted_for_review')
                        ->log('News submitted for review');

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
                        ->label(__('news.approvals.notes'))
                        ->maxLength(500)
                        ->rows(3)
                        ->helperText(__('news.approvals.notes_help')),
                ])
                ->visible(fn (): bool => $this->record->moderation_state === ModerationState::Review)
                ->action(function (array $data): void {
                    $userId = Auth::id();

                    if (! $userId) {
                        throw new RuntimeException('Approvals require an authenticated user.');
                    }

                    DB::transaction(function () use ($data, $userId): void {
                        $this->record->approvals()->create([
                            'user_id'    => $userId,
                            'decision'   => 'approved',
                            'notes'      => $data['notes'] ?? null,
                            'decided_at' => now(),
                        ]);

                        $this->record->update([
                            'moderation_state' => ModerationState::Published,
                            'approved_at'      => now(),
                            'approved_by_id'   => $userId,
                            'is_visible'       => true,
                            'published_at'     => $this->record->published_at ?? now(),
                        ]);
                    });

                    activity()
                        ->performedOn($this->record)
                        ->causedBy(Auth::user())
                        ->event('approved')
                        ->log('News approved and published');

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
                        ->label(__('news.approvals.notes'))
                        ->maxLength(500)
                        ->rows(3)
                        ->required(),
                ])
                ->visible(fn (): bool => $this->record->moderation_state !== ModerationState::Draft)
                ->action(function (array $data): void {
                    $userId = Auth::id();

                    if (! $userId) {
                        throw new RuntimeException('Return to draft requires an authenticated user.');
                    }

                    DB::transaction(function () use ($data, $userId): void {
                        $this->record->approvals()->create([
                            'user_id'    => $userId,
                            'decision'   => 'returned',
                            'notes'      => $data['notes'] ?? null,
                            'decided_at' => now(),
                        ]);

                        $this->record->update([
                            'moderation_state'        => ModerationState::Draft,
                            'submitted_for_review_at' => null,
                            'approved_at'             => null,
                            'approved_by_id'          => null,
                            'is_visible'              => false,
                        ]);
                    });

                    activity()
                        ->performedOn($this->record)
                        ->causedBy(Auth::user())
                        ->event('returned_to_draft')
                        ->log('News returned to draft');

                    Notification::make()
                        ->title(__('moderation.messages.returned'))
                        ->warning()
                        ->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->translationPayload = $this->extractTranslationPayload($data);

        if ($this->record->moderation_state !== ModerationState::Published) {
            $data['is_visible'] = false;
        }

        return $data;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $locale = $this->getActiveLocale();
        $translation = $this->record->translations()->firstWhere('locale', $locale);

        $data['translations'][$locale] = [
            'title'           => $translation?->title,
            'slug'            => $translation?->slug,
            'summary'         => $translation?->summary,
            'content'         => $translation?->content,
            'seo_title'       => $translation?->seo_title,
            'seo_description' => $translation?->seo_description,
        ];

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record = parent::handleRecordUpdate($record, $data);

        $this->persistTranslation($record);

        return $record;
    }

    private function extractTranslationPayload(array &$data): array
    {
        $locale = $this->getActiveLocale();
        $translationData = data_get($data, "translations.{$locale}", []);

        unset($data['translations']);

        if ($translationData === []) {
            return [];
        }

        return [
            'title'           => $translationData['title'] ?? null,
            'slug'            => $translationData['slug'] ?? null,
            'summary'         => $translationData['summary'] ?? null,
            'content'         => $translationData['content'] ?? null,
            'seo_title'       => $translationData['seo_title'] ?? null,
            'seo_description' => $translationData['seo_description'] ?? null,
        ];
    }

    private function persistTranslation(Model $record): void
    {
        if ($this->translationPayload === []) {
            return;
        }

        $locale = $this->getActiveLocale();

        $record->translations()->updateOrCreate(
            ['locale' => $locale],
            array_merge($this->translationPayload, ['locale' => $locale])
        );
    }

    private function getActiveLocale(): string
    {
        return app()->getLocale();
    }
}
