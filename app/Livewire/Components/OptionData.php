<?php

declare (strict_types=1);
namespace App\Livewire\Components;

use App\Models\Attribute;
use Illuminate\Support\Collection;
/**
 * OptionData
 * 
 * Livewire component for OptionData with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 */
class OptionData
{
    /**
     * Initialize the class instance with required dependencies.
     * @param Attribute $attribute
     * @param Collection $values
     */
    public function __construct(public Attribute $attribute, public Collection $values)
    {
    }
}