<?php

declare(strict_types=1);

use App\Livewire\Concerns\WithValidation;
use Livewire\Component;

class TestComponent extends Component
{
    use WithValidation;

    public function render()
    {
        return view('test');
    }
}

it('validates URLs correctly with WithValidation trait', function () {
    $component = new TestComponent;

    // Valid URLs
    expect($component->validateUrl('https://example.com'))->toBeTrue();
    expect($component->validateUrl('http://example.com'))->toBeTrue();
    expect($component->validateUrl('https://www.example.com'))->toBeTrue();
    expect($component->validateUrl('https://example.com/path'))->toBeTrue();

    // Invalid URLs
    expect($component->validateUrl('not-a-url'))->toBeFalse();
    expect($component->validateUrl('example.com'))->toBeFalse();
    expect($component->validateUrl('ftp://example.com'))->toBeFalse();
    expect($component->validateUrl(''))->toBeFalse();
});

it('validates URLs with custom protocols using WithValidation trait', function () {
    $component = new TestComponent;

    // Valid with custom protocols
    expect($component->validateUrl('https://example.com', ['https']))->toBeTrue();
    expect($component->validateUrl('http://example.com', ['http']))->toBeTrue();
    expect($component->validateUrl('https://example.com', ['http', 'https']))->toBeTrue();

    // Invalid with custom protocols
    expect($component->validateUrl('http://example.com', ['https']))->toBeFalse();
    expect($component->validateUrl('https://example.com', ['http']))->toBeFalse();
    expect($component->validateUrl('ftp://example.com', ['http', 'https']))->toBeFalse();
});

it('validates emails correctly with WithValidation trait', function () {
    $component = new TestComponent;

    // Valid emails
    expect($component->validateEmail('test@example.com'))->toBeTrue();
    expect($component->validateEmail('user.name@domain.co.uk'))->toBeTrue();

    // Invalid emails
    expect($component->validateEmail('not-an-email'))->toBeFalse();
    expect($component->validateEmail('test@'))->toBeFalse();
    expect($component->validateEmail('@example.com'))->toBeFalse();
    expect($component->validateEmail(''))->toBeFalse();
});

it('validates phone numbers correctly with WithValidation trait', function () {
    $component = new TestComponent;

    // Valid phone numbers
    expect($component->validatePhone('+37012345678'))->toBeTrue();
    expect($component->validatePhone('37012345678'))->toBeTrue();
    expect($component->validatePhone('812345678'))->toBeTrue();

    // Invalid phone numbers
    expect($component->validatePhone('12345678'))->toBeFalse();
    expect($component->validatePhone('+123456789'))->toBeFalse();
    expect($component->validatePhone(''))->toBeFalse();
});

it('validates required fields correctly with WithValidation trait', function () {
    $component = new TestComponent;

    // Valid required values
    expect($component->validateRequired('test'))->toBeTrue();
    expect($component->validateRequired(['key' => 'value']))->toBeTrue();
    expect($component->validateRequired(123))->toBeTrue();
    expect($component->validateRequired(true))->toBeTrue();

    // Invalid required values
    expect($component->validateRequired(''))->toBeFalse();
    expect($component->validateRequired('   '))->toBeFalse();
    expect($component->validateRequired([]))->toBeFalse();
    expect($component->validateRequired(null))->toBeFalse();
});
