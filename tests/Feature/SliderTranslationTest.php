<?php

declare(strict_types=1);

use App\Models\Slider;
use App\Models\SliderTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\HomeSlider;

uses(RefreshDatabase::class);

test('slider can have translations', function () {
    $slider = Slider::factory()->create([
        'title' => 'Original Title',
        'description' => 'Original Description',
        'button_text' => 'Original Button',
    ]);

    $translation = SliderTranslation::create([
        'slider_id' => $slider->id,
        'locale' => 'en',
        'title' => 'English Title',
        'description' => 'English Description',
        'button_text' => 'English Button',
    ]);

    expect($slider->translations)->toHaveCount(1);
    expect($slider->translations->first()->locale)->toBe('en');
    expect($slider->translations->first()->title)->toBe('English Title');
});

test('slider returns translated content for current locale', function () {
    app()->setLocale('en');
    
    $slider = Slider::factory()->create([
        'title' => 'Lithuanian Title',
        'description' => 'Lithuanian Description',
        'button_text' => 'Lithuanian Button',
    ]);

    SliderTranslation::create([
        'slider_id' => $slider->id,
        'locale' => 'en',
        'title' => 'English Title',
        'description' => 'English Description',
        'button_text' => 'English Button',
    ]);

    expect($slider->getTranslatedTitle())->toBe('English Title');
    expect($slider->getTranslatedDescription())->toBe('English Description');
    expect($slider->getTranslatedButtonText())->toBe('English Button');
});

test('slider falls back to original content when translation missing', function () {
    app()->setLocale('en');
    
    $slider = Slider::factory()->create([
        'title' => 'Lithuanian Title',
        'description' => 'Lithuanian Description',
        'button_text' => 'Lithuanian Button',
    ]);

    // No translation created

    expect($slider->getTranslatedTitle())->toBe('Lithuanian Title');
    expect($slider->getTranslatedDescription())->toBe('Lithuanian Description');
    expect($slider->getTranslatedButtonText())->toBe('Lithuanian Button');
});

test('slider translation model has correct fillable attributes', function () {
    $translation = new SliderTranslation();
    $fillable = $translation->getFillable();

    expect($fillable)->toContain('slider_id');
    expect($fillable)->toContain('locale');
    expect($fillable)->toContain('title');
    expect($fillable)->toContain('description');
    expect($fillable)->toContain('button_text');
});

test('slider translation belongs to slider', function () {
    $slider = Slider::factory()->create();
    $translation = SliderTranslation::create([
        'slider_id' => $slider->id,
        'locale' => 'en',
        'title' => 'English Title',
    ]);

    expect($translation->slider)->toBeInstanceOf(Slider::class);
    expect($translation->slider->id)->toBe($slider->id);
});

test('home slider component loads with translations', function () {
    app()->setLocale('en');
    
    $slider = Slider::factory()->create([
        'title' => 'Lithuanian Title',
        'is_active' => true,
    ]);

    SliderTranslation::create([
        'slider_id' => $slider->id,
        'locale' => 'en',
        'title' => 'English Title',
    ]);

    Livewire::test(HomeSlider::class)
        ->assertSee('English Title')
        ->assertDontSee('Lithuanian Title');
});

test('slider translation factory creates valid translation', function () {
    $slider = Slider::factory()->create();
    $translation = SliderTranslation::factory()->create([
        'slider_id' => $slider->id,
        'locale' => 'en',
    ]);

    expect($translation->title)->not->toBeEmpty();
    expect($translation->locale)->toBe('en');
    expect($translation->slider_id)->toBe($slider->id);
});
