<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use App\Models\AuditLog;
use App\Models\User;
use DateTimeInterface;
use Filament\Actions;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry as InfolistTextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Stringable;

/** @property-read \App\Models\Document $record */
final class ViewDocument extends ViewRecord
{
    protected static string $resource = DocumentResource::class;

    /**
     * Cache the audit log payload so multiple infolist callbacks do not trigger duplicate queries.
     *
     * @var array<int, array<string, mixed>>|null
     */
    private ?array $cachedAuditLogState = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            InfolistSection::make($this->translate('admin.documents.form.sections.basic_information', 'Document Details'))
                ->schema([
                    InfolistTextEntry::make('name')
                        // Surface a clear label even when translations are missing.
                        ->label($this->translate('admin.documents.fields.name', 'Name'))
                        ->weight('bold'),
                    InfolistTextEntry::make('title')
                        // Keep the label aligned with the form schema wording.
                        ->label($this->translate('admin.documents.form.fields.title', 'Title'))
                        ->placeholder('-'),
                    InfolistTextEntry::make('type')
                        // Use a fallback to avoid passing translation arrays to the label API.
                        ->label($this->translate('admin.documents.fields.type', 'Type'))
                        ->badge()
                        ->color('primary')
                        ->formatStateUsing(fn (?string $state): ?string => $state ? Str::upper($state) : null),
                    InfolistTextEntry::make('status')
                        // Reference the reusable status translation for consistency with filters.
                        ->label($this->translate('admin.documents.form.fields.status', 'Status'))
                        ->badge()
                        ->color(fn (?string $state): string => match ($state) {
                            'draft'     => 'gray',
                            'generated' => 'info',
                            'published' => 'success',
                            'archived'  => 'warning',
                            default     => 'primary',
                        }),
                    InfolistTextEntry::make('format')
                        // Match the schema form label so administrators see familiar copy.
                        ->label($this->translate('admin.documents.form.fields.format', 'Format'))
                        ->badge(),
                    InfolistTextEntry::make('description')
                        // Provide a human-readable fallback to avoid empty labels on missing locales.
                        ->label($this->translate('admin.documents.fields.description', 'Description'))
                        ->markdown()
                        ->placeholder('-'),
                ])
                ->columns(2),
            InfolistSection::make($this->translate('admin.documents.form.sections.organization', 'Organization'))
                ->schema([
                    InfolistTextEntry::make('documentable_type')
                        // Ensure the polymorphic type displays with a predictable caption.
                        ->label($this->translate('admin.documents.form.fields.documentable_type', 'Related Type'))
                        ->placeholder('-'),
                    InfolistTextEntry::make('documentable_id')
                        // Provide a consistent ID label for the related model reference.
                        ->label($this->translate('admin.documents.form.fields.documentable_id', 'Related ID'))
                        ->placeholder('-'),
                    InfolistTextEntry::make('creator.name')
                        // Reuse the creator label from the form schema.
                        ->label($this->translate('admin.documents.form.fields.created_by', 'Created By'))
                        ->state(fn (): string => $this->resolveActorName($this->record->creator))
                        ->placeholder($this->translate('admin.documents.audit.system', 'System')),
                    InfolistTextEntry::make('generated_at')
                        // Maintain parity with generated timestamp wording in the create/edit form.
                        ->label($this->translate('admin.documents.form.fields.generated_at', 'Generated At'))
                        ->dateTime()
                        ->placeholder('-'),
                    InfolistTextEntry::make('expires_at')
                        // Provide an explicit fallback because expiry copy is optional in translations.
                        ->label($this->translate('admin.documents.form.fields.expires_at', 'Expires At'))
                        ->dateTime()
                        ->placeholder('-'),
                    InfolistTextEntry::make('created_at')
                        ->label(__('admin.common.created_at'))
                        ->dateTime(),
                    InfolistTextEntry::make('updated_at')
                        ->label(__('admin.common.updated_at'))
                        ->dateTime(),
                ])
                ->columns(2),
            InfolistSection::make($this->translate('admin.documents.variables', 'Variables'))
                ->schema([
                    KeyValueEntry::make('variables')
                        // Keep variable metadata readable even if translator entries are absent.
                        ->label($this->translate('admin.documents.variables', 'Variables'))
                        ->keyLabel($this->translate('admin.documents.variable_name', 'Variable Name'))
                        ->valueLabel($this->translate('admin.documents.variable_value', 'Variable Value'))
                        ->visible(fn () => ! empty($this->record->variables ?? []))
                        ->placeholder($this->translate('admin.documents.audit.empty', 'No entries recorded')),
                ])
                ->visible(fn () => ! empty($this->record->variables ?? [])),
            InfolistSection::make($this->translate('admin.documents.audit.title', 'Audit Trail'))
                ->schema([
                    RepeatableEntry::make('audit_logs')
                        // Align the audit block label for each log entry.
                        ->label($this->translate('admin.documents.audit.title', 'Audit Trail'))
                        ->state(fn (): array => $this->getAuditLogState())
                        ->schema([
                            InfolistTextEntry::make('logged_at')
                                // Guarantee a friendly timestamp caption when translations are missing.
                                ->label($this->translate('admin.documents.audit.logged_at', 'Logged At'))
                                ->badge()
                                ->color('gray'),
                            InfolistTextEntry::make('performed_by')
                                // Highlight which user performed the action with a stable label.
                                ->label($this->translate('admin.documents.audit.performed_by', 'Performed By')),
                            InfolistTextEntry::make('action_label')
                                // Ensure the action label itself is translated or gracefully defaulted.
                                ->label($this->translate('admin.documents.audit.action', 'Action'))
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    __('admin.documents.audit_actions.created')  => 'success',
                                    __('admin.documents.audit_actions.updated')  => 'info',
                                    __('admin.documents.audit_actions.deleted')  => 'danger',
                                    __('admin.documents.audit_actions.restored') => 'warning',
                                    default                                      => 'secondary',
                                }),
                            KeyValueEntry::make('before')
                                // Provide context when showing the previous state snapshot.
                                ->label($this->translate('admin.documents.audit.before', 'Before'))
                                ->visible(fn (?array $state): bool => ! empty($state))
                                ->placeholder($this->translate('admin.documents.audit.empty', 'No entries recorded')),
                            KeyValueEntry::make('after')
                                // Provide context when showing the updated state snapshot.
                                ->label($this->translate('admin.documents.audit.after', 'After'))
                                ->visible(fn (?array $state): bool => ! empty($state))
                                ->placeholder($this->translate('admin.documents.audit.empty', 'No entries recorded')),
                        ])
                        ->columns(1),
                ])
                // Hide the audit section entirely when there are no log entries to render.
                ->visible(fn (): bool => $this->getAuditLogState() !== [])
                ->collapsible(),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getAuditLogState(): array
    {
        if ($this->cachedAuditLogState !== null) {
            return $this->cachedAuditLogState;
        }

        $this->cachedAuditLogState = $this->record->auditLogs()
            ->with('user')
            ->limit(20)
            ->get()
            ->map(function (AuditLog $log): array {
                $diff = is_array($log->diff) ? $log->diff : [];
                $before = isset($diff['before']) && is_array($diff['before']) ? $diff['before'] : [];
                $after = isset($diff['after']) && is_array($diff['after']) ? $diff['after'] : [];

                $user = $log->user;

                return [
                    'id'           => $log->id,
                    'action'       => $log->action,
                    'action_label' => $this->translateAuditAction($log->action),
                    'performed_by' => $this->resolveActorName($user),
                    'logged_at'    => $log->created_at?->toDayDateTimeString(),
                    'before'       => $this->stringifyDiff($before),
                    'after'        => $this->stringifyDiff($after),
                ];
            })
            ->toArray();

        return $this->cachedAuditLogState;
    }

    private function resolveActorName(?User $user): string
    {
        if ($user === null) {
            // Default anonymous events to the system label while respecting translation fallbacks.
            return $this->translate('admin.documents.audit.system', 'System');
        }

        $name = $user->getAttribute('name');

        if (is_string($name) && $name !== '') {
            return $name;
        }

        $email = $user->getAttribute('email');

        return is_string($email) && $email !== ''
            ? $email
            : $this->translate('admin.documents.audit.system', 'System');
    }

    private function translateAuditAction(string $action): string
    {
        $key = sprintf('admin.documents.audit_actions.%s', $action);
        $translation = __($key);

        return $translation === $key ? Str::ucfirst($action) : $translation;
    }

    /**
     * @param  array<int|string, mixed> $values
     * @return array<string, string>
     */
    private function stringifyDiff(array $values): array
    {
        return collect($values)
            ->mapWithKeys(fn ($value, $key): array => [
                (string) $key => $this->formatDiffValue($value),
            ])
            ->all();
    }

    private function formatDiffValue(mixed $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if (is_scalar($value) || $value === null) {
            return (string) ($value ?? 'null');
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
        }

        if ($value instanceof Stringable) {
            return $value->__toString();
        }

        $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $encoded === false ? get_debug_type($value) : $encoded;
    }

    private function translate(string $key, string $fallback): string
    {
        // Guard against array responses from nested translation groups and always provide a string for Filament components.
        $translation = __($key);

        if (is_string($translation)) {
            return $translation;
        }

        return $fallback;
    }
}
