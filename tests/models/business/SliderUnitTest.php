<?php

declare(strict_types=1);

// Simple tests that don't require database connection
test('slider model class exists', function () {
    expect(class_exists(\App\Models\Slider::class))->toBeTrue();
});

test('slider model has correct fillable attributes', function () {
    $slider = new \App\Models\Slider;
    $fillable = $slider->getFillable();

    expect($fillable)->toContain('title');
    expect($fillable)->toContain('description');
    expect($fillable)->toContain('button_text');
    expect($fillable)->toContain('button_url');
    expect($fillable)->toContain('image');
    expect($fillable)->toContain('background_color');
    expect($fillable)->toContain('text_color');
    expect($fillable)->toContain('sort_order');
    expect($fillable)->toContain('is_active');
    expect($fillable)->toContain('settings');
});

test('slider model has correct casts', function () {
    $slider = new \App\Models\Slider;
    $casts = $slider->getCasts();

    expect($casts['is_active'])->toBe('boolean');
    expect($casts['sort_order'])->toBe('integer');
    expect($casts['settings'])->toBe('array');
});

test('slider model can be instantiated', function () {
    $slider = new \App\Models\Slider;

    expect($slider)->toBeInstanceOf(\App\Models\Slider::class);
});

test('slider model has media library traits', function () {
    $slider = new \App\Models\Slider;

    expect(method_exists($slider, 'registerMediaCollections'))->toBeTrue();
    expect(method_exists($slider, 'registerMediaConversions'))->toBeTrue();
});

test('slider model has scope methods', function () {
    $slider = new \App\Models\Slider;

    expect(method_exists($slider, 'scopeActive'))->toBeTrue();
    expect(method_exists($slider, 'scopeOrdered'))->toBeTrue();
});

test('home slider component class exists', function () {
    expect(class_exists(\App\Livewire\HomeSlider::class))->toBeTrue();
});

test('home slider component has required methods', function () {
    $component = new \App\Livewire\HomeSlider;

    expect(method_exists($component, 'nextSlide'))->toBeTrue();
    expect(method_exists($component, 'previousSlide'))->toBeTrue();
    expect(method_exists($component, 'goToSlide'))->toBeTrue();
    expect(method_exists($component, 'toggleAutoPlay'))->toBeTrue();
});

test('slider factory class exists', function () {
    expect(class_exists(\Database\Factories\SliderFactory::class))->toBeTrue();
});
