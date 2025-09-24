<?php

declare(strict_types=1);

use App\Livewire\HomeSlider;
use App\Models\Slider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Skip problematic migrations for testing
    $this->artisan('migrate:fresh', ['--path' => 'database/migrations/2025_09_19_035640_create_sliders_table.php']);
    $this->user = User::factory()->create();
});

test('slider model can be created', function () {
    $slider = Slider::factory()->create([
        'title' => 'Test Slider',
        'description' => 'Test Description',
        'button_text' => 'Click Here',
        'button_url' => 'https://example.com',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    expect($slider->title)->toBe('Test Slider');
    expect($slider->description)->toBe('Test Description');
    expect($slider->button_text)->toBe('Click Here');
    expect($slider->button_url)->toBe('https://example.com');
    expect($slider->is_active)->toBeTrue();
    expect($slider->sort_order)->toBe(1);
});

test('slider model has correct fillable attributes', function () {
    $slider = new Slider;
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
    $slider = Slider::factory()->create([
        'is_active' => true,
        'sort_order' => 5,
        'settings' => ['key' => 'value'],
    ]);

    expect($slider->is_active)->toBeBool();
    expect($slider->sort_order)->toBeInt();
    expect($slider->settings)->toBeArray();
});

test('slider active scope works correctly', function () {
    Slider::factory()->create(['is_active' => true]);
    Slider::factory()->create(['is_active' => false]);
    Slider::factory()->create(['is_active' => true]);

    $activeSliders = Slider::active()->get();

    expect($activeSliders)->toHaveCount(2);
    expect($activeSliders->every(fn ($slider) => $slider->is_active))->toBeTrue();
});

test('slider ordered scope works correctly', function () {
    Slider::factory()->create(['sort_order' => 3, 'created_at' => now()->subDays(2)]);
    Slider::factory()->create(['sort_order' => 1, 'created_at' => now()->subDays(1)]);
    Slider::factory()->create(['sort_order' => 2, 'created_at' => now()]);

    $orderedSliders = Slider::ordered()->get();

    expect($orderedSliders->first()->sort_order)->toBe(1);
    expect($orderedSliders->last()->sort_order)->toBe(3);
});

test('home slider component displays active sliders', function () {
    $slider1 = Slider::factory()->create([
        'title' => 'First Slider',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $slider2 = Slider::factory()->create([
        'title' => 'Second Slider',
        'is_active' => true,
        'sort_order' => 2,
    ]);

    Slider::factory()->create([
        'title' => 'Inactive Slider',
        'is_active' => false,
        'sort_order' => 3,
    ]);

    Livewire::test(HomeSlider::class)
        ->assertSee('First Slider')
        ->assertSee('Second Slider')
        ->assertDontSee('Inactive Slider');
});

test('home slider component has correct initial state', function () {
    Livewire::test(HomeSlider::class)
        ->assertSet('currentSlide', 0)
        ->assertSet('autoPlay', true)
        ->assertSet('autoPlayInterval', 5000);
});

test('home slider component navigation methods work', function () {
    Slider::factory()->count(3)->create(['is_active' => true]);

    $component = Livewire::test(HomeSlider::class);

    // Test next slide
    $component->call('nextSlide')
        ->assertSet('currentSlide', 1);

    $component->call('nextSlide')
        ->assertSet('currentSlide', 2);

    // Test wrap around
    $component->call('nextSlide')
        ->assertSet('currentSlide', 0);

    // Test previous slide
    $component->call('previousSlide')
        ->assertSet('currentSlide', 2);

    // Test go to specific slide
    $component->call('goToSlide', 1)
        ->assertSet('currentSlide', 1);
});

test('home slider component toggle auto play works', function () {
    $component = Livewire::test(HomeSlider::class);

    $component->assertSet('autoPlay', true)
        ->call('toggleAutoPlay')
        ->assertSet('autoPlay', false)
        ->call('toggleAutoPlay')
        ->assertSet('autoPlay', true);
});

test('home slider component displays no slides message when no active sliders', function () {
    Slider::factory()->create(['is_active' => false]);

    Livewire::test(HomeSlider::class)
        ->assertSee('No slides available');
});

test('slider factory creates valid slider', function () {
    $slider = Slider::factory()->create();

    expect($slider->title)->not->toBeEmpty();
    expect($slider->is_active)->toBeBool();
    expect($slider->sort_order)->toBeInt();
    expect($slider->background_color)->toStartWith('#');
    expect($slider->text_color)->toStartWith('#');
});
