<?php declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Attribute;
use Illuminate\Support\Collection;

class OptionData
{
    public function __construct(
        public Attribute $attribute,
        public Collection $values,
    ) {}
}
