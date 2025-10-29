<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\CampaignResource;
use App\Models\Campaign;
use App\Models\Channel;
use App\Models\Scopes\ActiveCampaignScope;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\StatusScope;
use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component as SchemaComponent;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use LogicException;
use Saade\FilamentFullCalendar\Actions;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

final class CalendarWidget extends FullCalendarWidget
{
    public Model|string|null $model = Campaign::class;

    /**
     * @param  array{start?: string, end?: string, timezone?: string} $fetchInfo
     * @return array<int, array<string, mixed>>
     */
    public function fetchEvents(array $fetchInfo): array
    {
        // Normalise the incoming date strings before passing them to the query builder.
        $startValue = $fetchInfo['start'] ?? null;
        $endValue = $fetchInfo['end'] ?? null;

        $start = is_string($startValue) && $startValue !== ''
            ? Carbon::parse($startValue)
            : now()->startOfMonth();

        $end = is_string($endValue) && $endValue !== ''
            ? Carbon::parse($endValue)
            : now()->endOfMonth();

        $campaigns = Campaign::query()
            ->withoutGlobalScopes([
                ActiveScope::class,
                StatusScope::class,
                ActiveCampaignScope::class,
            ])
            ->with('channel')
            ->whereNotNull('starts_at')
            ->where('starts_at', '<=', $end)
            ->where(function ($query) use ($start): void {
                $query
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $start);
            })
            ->orderBy('starts_at')
            ->get();

        $events = $campaigns
            ->map(function (Campaign $campaign): array {
                // Resolve the key early and guard against non-scalar identifiers.
                $key = $campaign->getKey();

                if (! is_int($key) && ! is_string($key)) {
                    throw new LogicException('Campaign identifier must be a scalar value.');
                }

                $color = $this->resolveStatusColor($campaign->status);

                $title = $campaign->name ?? $campaign->slug ?? (string) $key;

                $channel = $campaign->channel;
                $channelName = $channel instanceof Channel ? $channel->name : null;

                /** @var CarbonInterface|null $startAt */
                $startAt = $campaign->starts_at;

                if (! $startAt instanceof CarbonInterface) {
                    // The column is nullable, but the widget requires a valid value, so default to now.
                    $startAt = now();
                }

                $event = EventData::make()
                    ->id($key)
                    ->title($title)
                    ->start($startAt)
                    ->url(CampaignResource::getUrl('view', ['record' => $campaign]))
                    ->extendedProps([
                        'status'    => $campaign->status,
                        'is_active' => (bool) $campaign->is_active,
                        'channel'   => $channelName,
                        'color'     => $color,
                        'tooltip'   => $this->buildTooltip($campaign),
                    ]);

                /** @var CarbonInterface|null $endAt */
                $endAt = $campaign->ends_at;

                if ($endAt instanceof CarbonInterface) {
                    $event->end($endAt);
                }

                if ($color) {
                    $event
                        ->backgroundColor($color)
                        ->borderColor($color);
                }

                return $event->toArray();
            })
            ->values()
            ->all();

        /** @var array<int, array<string, mixed>> $events */
        return $events;
    }

    /**
     * @return array<string, mixed>
     */
    public function config(): array
    {
        return [
            'initialView'             => 'dayGridMonth',
            'firstDay'                => 1,
            'locale'                  => app()->getLocale(),
            'selectable'              => true,
            'selectMirror'            => true,
            'editable'                => true,
            'eventResizableFromStart' => true,
            'navLinks'                => true,
            'dayMaxEvents'            => true,
            'headerToolbar'           => [
                'start'  => 'prev,next today',
                'center' => 'title',
                'end'    => 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
            ],
            'buttonText' => [
                'today' => __('Today'),
                'month' => __('Month'),
                'week'  => __('Week'),
                'day'   => __('Day'),
                'list'  => __('List'),
            ],
            'eventTimeFormat' => [
                'hour'   => '2-digit',
                'minute' => '2-digit',
                'hour12' => false,
            ],
            'slotLabelFormat' => [
                'hour'   => '2-digit',
                'minute' => '2-digit',
                'hour12' => false,
            ],
        ];
    }

    /**
     * @return array<int, SchemaComponent>
     */
    public function getFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->label($this->translate('campaigns.fields.name', 'Name'))
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function (string $operation, $state, Forms\Set $set): void {
                    if ($operation !== 'create' || ! is_string($state)) {
                        return;
                    }

                    // Update the slug while the user is typing the name to improve UX.
                    $set('slug', Str::slug($state));
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
                    'draft'     => $this->translate('campaigns.status.draft', 'Draft'),
                    'active'    => $this->translate('campaigns.status.active', 'Active'),
                    'scheduled' => $this->translate('campaigns.status.scheduled', 'Scheduled'),
                    'paused'    => $this->translate('campaigns.status.paused', 'Paused'),
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
            SupportFlatpickr::makeDateTime('starts_at')
                ->label($this->translate('campaigns.fields.start_date', 'Start date'))
                ->seconds(false),
            SupportFlatpickr::makeDateTime('ends_at')
                ->label($this->translate('campaigns.fields.end_date', 'End date'))
                ->seconds(false),
        ];
    }

    /**
     * @return array<int, Action>
     */
    protected function headerActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mountUsing(function (?Schema $schema, array $arguments = []): void {
                    if (! $schema) {
                        return;
                    }

                    $schema->fill();

                    if (($arguments['type'] ?? null) !== 'select') {
                        return;
                    }

                    $state = $this->normaliseFormState($schema);
                    $state = $this->prepareStateWithDateRange(
                        $state,
                        $arguments['start'] ?? null,
                        $arguments['end'] ?? null,
                        array_key_exists('end', $arguments),
                    );

                    $schema->fill($state);
                })
                ->mutateFormDataUsing(function (array $data): array {
                    /** @var array<string, mixed> $data */
                    return $this->ensureSlug($data);
                }),
        ];
    }

    /**
     * @return array<int, Action>
     */
    protected function modalActions(): array
    {
        return [
            Actions\EditAction::make()
                ->mountUsing(function (?Schema $schema, array $arguments = []): void {
                    if (! $schema) {
                        return;
                    }

                    $schema->fill();

                    $state = $this->normaliseFormState($schema);

                    if (in_array($arguments['type'] ?? null, ['drop', 'resize'], true)) {
                        $state = $this->prepareStateWithDateRange(
                            $state,
                            $this->extractEventDate($arguments, 'start'),
                            $this->extractEventDate($arguments, 'end'),
                            $this->eventHasEndDate($arguments),
                        );
                    }

                    $schema->fill($state);
                })
                ->mutateFormDataUsing(function (array $data): array {
                    /** @var array<string, mixed> $data */
                    return $this->ensureSlug($data);
                }),
            Actions\DeleteAction::make(),
        ];
    }

    public function eventDidMount(): string
    {
        return <<<'JS'
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
     * @param  array<string, mixed> $state
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

    /**
     * @param array{event?: mixed} $arguments
     */
    private function extractEventDate(array $arguments, string $key): ?string
    {
        $event = $arguments['event'] ?? null;

        if (! is_array($event)) {
            return null;
        }

        $value = $event[$key] ?? $event[sprintf('%sStr', $key)] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * @param array{event?: mixed} $arguments
     */
    private function eventHasEndDate(array $arguments): bool
    {
        $event = $arguments['event'] ?? null;

        return is_array($event)
            && (array_key_exists('end', $event) || array_key_exists('endStr', $event));
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

        if (is_array($value) && isset($value['date']) && is_string($value['date'])) {
            return Carbon::parse($value['date'])->toDateTimeString();
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function normaliseFormState(Schema $schema): array
    {
        $state = $schema->getRawState();

        /** @var array<mixed>|Arrayable<array-key, mixed> $state */
        $state = $state;

        if ($state instanceof Arrayable) {
            $state = $state->toArray();
        }

        // @phpstan-ignore-next-line The runtime may hand back scalars when the form is pristine.
        if (! is_array($state)) {
            $state = [];
        }

        /** @var array<string, mixed> $state */
        return $state;
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function ensureSlug(array $data): array
    {
        $name = $data['name'] ?? null;

        if (blank($data['slug'] ?? null) && is_string($name) && $name !== '') {
            $data['slug'] = Str::slug($name);
        }

        return $data;
    }

    private function buildTooltip(Campaign $campaign): string
    {
        /** @var CarbonInterface|null $startsAt */
        $startsAt = $campaign->starts_at;
        /** @var CarbonInterface|null $endsAt */
        $endsAt = $campaign->ends_at;

        $start = $startsAt?->format('Y-m-d H:i');
        $end = $endsAt?->format('Y-m-d H:i');
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
            'active'    => '#16a34a',
            'scheduled' => '#0ea5e9',
            'paused'    => '#f59e0b',
            'completed' => '#6366f1',
            'cancelled' => '#ef4444',
            default     => null,
        };
    }

    private function translate(string $key, string $fallback): string
    {
        $translated = __($key);

        return $translated === $key ? $fallback : $translated;
    }
}
