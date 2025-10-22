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

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema   
    {
        return $schema->schema([
            InfolistSection::make(__('admin.documents.form.sections.basic_information'))
                ->schema([
                    InfolistTextEntry::make('name')
                        ->label(__('admin.documents.name'))
                        ->weight('bold'),
                    InfolistTextEntry::make('title')
                        ->label(__('admin.documents.title_label'))
                        ->placeholder('-'),
                    InfolistTextEntry::make('type')
                        ->label(__('admin.documents.type'))
                        ->badge()
                        ->color('primary')
                        ->formatStateUsing(fn (?string $state): ?string => $state ? Str::upper($state) : null),
                    InfolistTextEntry::make('status')
                        ->label(__('admin.documents.status'))
                        ->badge()
                        ->color(fn (?string $state): string => match ($state) {
                            'draft'     => 'gray',
                            'generated' => 'info',
                            'published' => 'success',
                            'archived'  => 'warning',
                            default     => 'primary',
                        }),
                    InfolistTextEntry::make('format')
                        ->label(__('admin.documents.format'))
                        ->badge(),
                    InfolistTextEntry::make('description')
                        ->label(__('admin.documents.description'))
                        ->markdown()
                        ->placeholder('-'),
                ])
                ->columns(2),
            InfolistSection::make(__('admin.documents.form.sections.organization'))
                ->schema([
                    InfolistTextEntry::make('documentable_type')
                        ->label(__('admin.documents.documentable_type'))
                        ->placeholder('-'),
                    InfolistTextEntry::make('documentable_id')
                        ->label(__('admin.documents.documentable_id'))
                        ->placeholder('-'),
                    InfolistTextEntry::make('creator.name')
                        ->label(__('admin.documents.created_by'))
                        ->state(fn (): string => $this->resolveActorName($this->record->creator))
                        ->placeholder(__('admin.documents.audit.system')),
                    InfolistTextEntry::make('generated_at')
                        ->label(__('admin.documents.generated_at'))
                        ->dateTime()
                        ->placeholder('-'),
                    InfolistTextEntry::make('expires_at')
                        ->label(__('admin.documents.expires_at'))
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
            InfolistSection::make(__('admin.documents.variables'))
                ->schema([
                    KeyValueEntry::make('variables')
                        ->label(__('admin.documents.variables'))
                        ->keyLabel(__('admin.documents.variable_name'))
                        ->valueLabel(__('admin.documents.variable_value'))
                        ->visible(fn () => ! empty($this->record->variables ?? []))
                        ->placeholder(__('admin.documents.audit.empty')),
                ])
                ->visible(fn () => ! empty($this->record->variables ?? [])),
            InfolistSection::make(__('admin.documents.audit.title'))
                ->schema([
                    RepeatableEntry::make('audit_logs')
                        ->label(__('admin.documents.audit.title'))
                        ->state(fn (): array => $this->getAuditLogState())
                        ->schema([
                            InfolistTextEntry::make('logged_at')
                                ->label(__('admin.documents.audit.logged_at'))
                                ->badge()
                                ->color('gray'),
                            InfolistTextEntry::make('performed_by')
                                ->label(__('admin.documents.audit.performed_by')),
                            InfolistTextEntry::make('action_label')
                                ->label(__('admin.documents.audit.action'))
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    __('admin.documents.audit_actions.created')  => 'success',
                                    __('admin.documents.audit_actions.updated')  => 'info',
                                    __('admin.documents.audit_actions.deleted')  => 'danger',
                                    __('admin.documents.audit_actions.restored') => 'warning',
                                    default                                      => 'secondary',
                                }),
                            KeyValueEntry::make('before')
                                ->label(__('admin.documents.audit.before'))
                                ->visible(fn (array $state): bool => $state !== [])
                                ->placeholder(__('admin.documents.audit.empty')),
                            KeyValueEntry::make('after')
                                ->label(__('admin.documents.audit.after'))
                                ->visible(fn (array $state): bool => $state !== [])
                                ->placeholder(__('admin.documents.audit.empty')),
                        ])
                        ->columns(1)
                        ->emptyLabel(__('admin.documents.audit.empty')),
                ])
                ->collapsible(),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getAuditLogState(): array
    {
        return $this->record->auditLogs()
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
    }

    private function resolveActorName(?User $user): string
    {
        if ($user === null) {
            return __('admin.documents.audit.system');
        }

        $name = $user->getAttribute('name');

        if (is_string($name) && $name !== '') {
            return $name;
        }

        $email = $user->getAttribute('email');

        return is_string($email) && $email !== ''
            ? $email
            : __('admin.documents.audit.system');
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
}
