<?php

declare(strict_types=1);

namespace App\Filament\WidgetTabs\Components;

use App\Filament\WidgetTabs\Components\Concerns\HasIcon;
use App\Filament\WidgetTabs\Components\Concerns\HasLabel;
use App\Filament\WidgetTabs\Components\Concerns\HasPercentage;
use App\Filament\WidgetTabs\Components\Concerns\HasPrecision;
use App\Filament\WidgetTabs\Components\Concerns\HasTheme;
use App\Filament\WidgetTabs\Components\Concerns\HasValue;
use Closure;
use Filament\Support\Components\Component;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Support\Concerns\HasExtraAttributes;
use Illuminate\Database\Eloquent\Builder;

class WidgetTab extends Component
{
    use EvaluatesClosures;
    use HasExtraAttributes;
    use HasIcon;
    use HasLabel;
    use HasPercentage;
    use HasPrecision;
    use HasTheme;
    use HasValue;

    protected ?Closure $modifyQueryUsing = null;

    public function __construct(string|Closure|null $label = null)
    {
        $this->label($label);
    }

    public static function make(string|Closure|null $label = null): static
    {
        $static = app(static::class, ['label' => $label]);
        // Trigger the base component bootstrapping to stay compatible with Filament v4.
        $static->configure();

        return $static;
    }

    public function query(?Closure $callback): static
    {
        $this->modifyQueryUsing($callback);

        return $this;
    }

    public function modifyQueryUsing(?Closure $callback): static
    {
        $this->modifyQueryUsing = $callback;

        return $this;
    }

    public function modifyQuery(Builder $query): Builder
    {
        return $this->evaluate($this->modifyQueryUsing, [
            'query' => $query,
        ]) ?? $query;
    }
}
