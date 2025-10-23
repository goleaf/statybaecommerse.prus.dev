<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\CampaignResource;
use App\Models\Campaign;
use App\Models\Scopes\ActiveCampaignScope;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\StatusScope;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Saade\FilamentFullCalendar\Actions;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

final class CalendarWidget extends FullCalendarWidget
{
    public Model | string | null $model = Campaign::class;

    public function fetchEvents(array $fetchInfo): array
    {
        $start = isset($fetchInfo['start']) ? Carbon::parse($fetchInfo['start']) : now()->startOfMonth();
        $end = isset($fetchInfo['end']) ? Carbon::parse($fetchInfo['end']) : now()->endOfMonth();

        $campaigns = Campaign::query()
            ->withoutGlobalScopes([
                ActiveScope::class,
                StatusScope::class,
                ActiveCampaignScope::class,
            ])
            ->with('channel')
            ->whereNotNull('starts_at')
            ->where('starts_at', '<=', $end)
            ->where(function ($query) use ($start) {
                $query
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $start);
            })
            ->orderBy('starts_at')
            ->get();

        return $campaigns
            ->map(function (Campaign $campaign): array {
                $color = $this->resolveStatusColor($campaign->status);

                $event = EventData::make()
                    ->id($campaign->getKey())
                    ->title($campaign->name ?? $campaign->slug)
                    ->start(optional($campaign->starts_at)->toIso8601String())
                    ->url(CampaignResource::getUrl('view', ['record' => $campaign]))
                    ->extendedProps([
                        'status' => $campaign->status,
                        'is_active' => (bool) $campaign->is_active,
                        'channel' => $campaign->channel?->name,
                        'color' => $color,
                        'tooltip' => $this->buildTooltip($campaign),
                    ]);

                if ($campaign->ends_at) {
                    $event->end($campaign->ends_at->toIso8601String());
                }

                if ($color) {
                    $event
                        ->backgroundColor($color)
                        ->borderColor($color);
                }

                return $event->toArray();
            })
            ->all();
    }

    public function config(): array
    {
        return [
            'initialView' => 'dayGridMonth',
            'firstDay' => 1,
            'locale' => app()->getLocale(),
            'selectable' => true,
            'selectMirror' => true,
            'editable' => true,
            'eventResizableFromStart' => true,
            'navLinks' => true,
            'dayMaxEvents' => true,
            'headerToolbar' => [
                'start' => 'prev,next today',
                'center' => 'title',
                'end' => 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
            ],
            'buttonText' => [
                'today' => __('Today'),
                'month' => __('Month'),
                'week' => __('Week'),
                'day' => __('Day'),
                'list' => __('List'),
            ],
            'eventTimeFormat' => [
                'hour' => '2-digit',
                'minute' => '2-digit',
                'hour12' => false,
            ],
            'slotLabelFormat' => [
                'hour' => '2-digit',
                'minute' => '2-digit',
                'hour12' => false,
            ],
        ];
    }

    public function getFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->label($this->translate('campaigns.fields.name', 'Name'))
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function (string $operation, $state, Forms\Set $set): void {
                    if ($operation === 'create') {
                        $set('slug', Str::slug((string) $state));
                    }
                }),
            TextInput::make('slug')
                ->label($this->translate('campaigns.fields.slug', 'Slug'))
                ->unique(ignoreRecord: true)
                ->maxLength(255),
            Select::make('channel_id')
                ->label($this->translate('campaigns.fields.channel', 'Channel'))
                ->relationship('channel', 'name')
                ->searchable()
                ->preload()
                ->nullable(),
            Select::make('status')
                ->label($this->translate('campaigns.fields.status', 'Status'))
                ->options([
                    'draft' => $this->translate('campaigns.status.draft', 'Draft'),
                    'active' => $this->translate('campaigns.status.active', 'Active'),
                    'scheduled' => $this->translate('campaigns.status.scheduled', 'Scheduled'),
                    'paused' => $this->translate('campaigns.status.paused', 'Paused'),
                    'completed' => $this->translate('campaigns.status.completed', 'Completed'),
                    'cancelled' => $this->translate('campaigns.status.cancelled', 'Cancelled'),
                ])
                ->required()
                ->default('draft'),
            Toggle::make('is_active')
                ->label($this->translate('campaigns.fields.is_active', 'Active'))
                ->default(true),
            Toggle::make('is_featured')
                ->label($this->translate('campaigns.fields.is_featured', 'Featured'))
                ->default(false),
            DateTimePicker::make('starts_at')
                ->label($this->translate('campaigns.fields.start_date', 'Start date'))
                ->seconds(false),
            DateTimePicker::make('ends_at')
                ->label($this->translate('campaigns.fields.end_date', 'End date'))
                ->seconds(false),
        ];
    }

    protected function headerActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mountUsing(function (?Form $form, array $arguments = []): void {
                    if (! $form) {
                        return;
                    }

                    $form->fill();

                    if (($arguments['type'] ?? null) !== 'select') {
                        return;
                    }

                    $state = $this->normaliseFormState($form);
                    $state = $this->prepareStateWithDateRange(
                        $state,
                        $arguments['start'] ?? null,
                        $arguments['end'] ?? null,
                        array_key_exists('end', $arguments),
                    );

                    $form->fill($state);
                })
                ->mutateFormDataUsing(fn (array $data): array => $this->ensureSlug($data)),
        ];
    }

    protected function modalActions(): array
    {
        return [
            Actions\EditAction::make()
                ->mountUsing(function (?Form $form, array $arguments = []): void {
                    if (! $form) {
                        return;
                    }

                    $form->fill();

                    $state = $this->normaliseFormState($form);

                    if (in_array($arguments['type'] ?? null, ['drop', 'resize'], true)) {
                        $state = $this->prepareStateWithDateRange(
                            $state,
                            $this->extractEventDate($arguments, 'start'),
                            $this->extractEventDate($arguments, 'end'),
                            $this->eventHasEndDate($arguments),
                        );
                    }

                    $form->fill($state);
                })
                ->mutateFormDataUsing(fn (array $data): array => $this->ensureSlug($data)),
            Actions\DeleteAction::make(),
        ];
    }

    public function eventDidMount(): string
    {
        return <<<JS
            function(info) {
                const tooltip = info.event.extendedProps?.tooltip;
                if (tooltip) {
                    info.el.setAttribute('title', tooltip);
                }

                const color = info.event.extendedProps?.color;
                if (color) {
                    info.el.style.setProperty('--fc-event-bg-color', color);
                    info.el.style.setProperty('--fc-event-border-color', color);
                }
            }
        JS;
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    private function prepareStateWithDateRange(array $state, mixed $start, mixed $end, bool $shouldClearEnd = false): array
    {
        $normalisedStart = $this->normaliseArgumentDate($start);
        $normalisedEnd = $this->normaliseArgumentDate($end);

        if ($normalisedStart) {
            $state['starts_at'] = $normalisedStart;
        }

        if ($normalisedEnd) {
            $state['ends_at'] = $normalisedEnd;
        } elseif ($shouldClearEnd) {
            $state['ends_at'] = null;
        }

        return $state;
    }

    private function extractEventDate(array $arguments, string $key): mixed
    {
        return $arguments['event'][$key] ?? $arguments['event'][sprintf('%sStr', $key)] ?? null;
    }

    private function eventHasEndDate(array $arguments): bool
    {
        $event = $arguments['event'] ?? [];

        return array_key_exists('end', $event) || array_key_exists('endStr', $event);
    }

    private function normaliseArgumentDate(mixed $value): ?string
    {
        if ($value instanceof CarbonInterface) {
            return $value->toDateTimeString();
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->toDateTimeString();
        }

        if (is_string($value) && $value !== '') {
            return Carbon::parse($value)->toDateTimeString();
        }

        if (is_array($value) && isset($value['date'])) {
            return Carbon::parse($value['date'])->toDateTimeString();
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function normaliseFormState(Form $form): array
    {
        $state = $form->getRawState();

        if ($state instanceof Arrayable) {
            $state = $state->toArray();
        }

        return is_array($state) ? $state : [];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function ensureSlug(array $data): array
    {
        if (blank($data['slug'] ?? null) && filled($data['name'] ?? null)) {
            $data['slug'] = Str::slug((string) $data['name']);
        }

        return $data;
    }

    private function buildTooltip(Campaign $campaign): string
    {
        $start = optional($campaign->starts_at)?->format('Y-m-d H:i');
        $end = optional($campaign->ends_at)?->format('Y-m-d H:i');
        $status = $this->translate("campaigns.status.{$campaign->status}", Str::headline($campaign->status ?? ''));

        return collect([
            $campaign->name,
            $status,
            $start ? __('Starts: :date', ['date' => $start]) : null,
            $end ? __('Ends: :date', ['date' => $end]) : null,
        ])->filter()->implode("\n");
    }

    private function resolveStatusColor(?string $status): ?string
    {
        return match ($status) {
            'active' => '#16a34a',
            'scheduled' => '#0ea5e9',
            'paused' => '#f59e0b',
            'completed' => '#6366f1',
            'cancelled' => '#ef4444',
            default => null,
        };
    }

    private function translate(string $key, string $fallback): string
    {
        $translated = __($key);

        return $translated === $key ? $fallback : $translated;
    }
}
