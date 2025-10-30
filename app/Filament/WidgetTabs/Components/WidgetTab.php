<?php declare(strict_types=1);

namespace App\Filament\WidgetTabs\Components;

use App\Filament\WidgetTabs\Components\Concerns\HasIcon;
use App\Filament\WidgetTabs\Components\Concerns\HasLabel;
use App\Filament\WidgetTabs\Components\Concerns\HasPercentage;
use App\Filament\WidgetTabs\Components\Concerns\HasPrecision;
use App\Filament\WidgetTabs\Components\Concerns\HasTheme;
use App\Filament\WidgetTabs\Components\Concerns\HasValue;
use Filament\Support\Components\Component;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Support\Concerns\HasExtraAttributes;
use Illuminate\Database\Eloquent\Builder;
use Closure;

class WidgetTab extends Component
{
    // The upstream Filament component already ships with configuration helpers
    // (via the Configurable trait), so we only need to opt into the pieces that
    // power our widget tab metadata and dynamic query callbacks.
    use EvaluatesClosures;
    use HasExtraAttributes;
    use HasIcon;
    use HasLabel;
    use HasPercentage;
    use HasPrecision;
    use HasTheme;
    use HasValue;

    /**
     * @var (Closure(Builder): Builder)|null
     */
    protected ?Closure $modifyQueryUsing = null;

    /**
     * @param string|(Closure(): string)|null $label
     */
    public function __construct(string|Closure|null $label = null)
    {
        $this->label($label);
    }

    /**
     * @param string|(Closure(): string)|null $label
     */
    public static function make(string|Closure|null $label = null): static
    {
        /** @var static $static */
        $static = app(static::class, ['label' => $label]);
        // Leverage the inherited Configurable trait to allow downstream modifiers to hook into setup.
        $static->configure();

        return $static;
    }

    /**
     * @param (Closure(Builder): Builder)|null $callback
     */
    public function query(?Closure $callback): static
    {
        $this->modifyQueryUsing($callback);

        return $this;
    }

    /**
     * @param (Closure(Builder): Builder)|null $callback
     */
    public function modifyQueryUsing(?Closure $callback): static
    {
        $this->modifyQueryUsing = $callback;

        return $this;
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model> $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    public function modifyQuery(Builder $query): Builder
    {
        if ($this->modifyQueryUsing === null) {
            return $query;
        }

        /** @var Builder<\Illuminate\Database\Eloquent\Model>|null $result */
        $result = $this->evaluate($this->modifyQueryUsing, [
            'query' => $query,
            'builder' => $query,
        ]);

        return $result ?? $query;
    }
}
