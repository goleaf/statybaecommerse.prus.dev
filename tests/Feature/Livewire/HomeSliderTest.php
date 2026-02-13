<?php

declare(strict_types=1);

use App\Livewire\HomeSlider;
use App\Models\Slider;
use Livewire\Livewire;

it('does not have a black border on the autoplay button', function () {
    // Create 2 active sliders to trigger the autoplay button visibility
    Slider::factory()->count(2)->create([
        'is_active' => true,
    ]);

    Livewire::test(HomeSlider::class)
        ->assertOk()
        ->assertDontSee('border-dark');
});
