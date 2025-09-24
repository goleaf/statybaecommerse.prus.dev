<?php

declare(strict_types=1);

// Unit tests for multi-language slider functionality
test('slider model has translation methods', function () {
    $slider = new \App\Models\Slider;

    expect(method_exists($slider, 'translations'))->toBeTrue();
    expect(method_exists($slider, 'translation'))->toBeTrue();
    expect(method_exists($slider, 'getTranslatedTitle'))->toBeTrue();
    expect(method_exists($slider, 'getTranslatedDescription'))->toBeTrue();
    expect(method_exists($slider, 'getTranslatedButtonText'))->toBeTrue();
});

test('slider translation model exists', function () {
    expect(class_exists(\App\Models\SliderTranslation::class))->toBeTrue();
});

test('slider translation model has correct fillable attributes', function () {
    $translation = new \App\Models\SliderTranslation;
    $fillable = $translation->getFillable();

    expect($fillable)->toContain('slider_id');
    expect($fillable)->toContain('locale');
    expect($fillable)->toContain('title');
    expect($fillable)->toContain('description');
    expect($fillable)->toContain('button_text');
});

test('slider translation model has relationship method', function () {
    $translation = new \App\Models\SliderTranslation;

    expect(method_exists($translation, 'slider'))->toBeTrue();
});

test('slider translation factory exists', function () {
    expect(class_exists(\Database\Factories\SliderTranslationFactory::class))->toBeTrue();
});

test('slider seeder exists', function () {
    expect(class_exists(\Database\Seeders\SliderSeeder::class))->toBeTrue();
});

test('home slider component loads with translations', function () {
    $component = new \App\Livewire\HomeSlider;

    expect(method_exists($component, 'sliders'))->toBeTrue();
});

test('slider translation test file exists', function () {
    expect(file_exists(base_path('tests/Feature/SliderTranslationTest.php')))->toBeTrue();
});
