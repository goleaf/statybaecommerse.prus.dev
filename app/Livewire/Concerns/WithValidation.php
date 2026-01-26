<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use Exception;
use Illuminate\Support\Str;

/**
 * WithValidation
 *
 * Trait providing reusable functionality across multiple classes.
 */
trait WithValidation
{
    protected function getValidationMessages(): array
    {
        return ['required' => __('messages.shared), 'email' => __('messages.validation), 'min' => __('messages.validation), 'max' => __('messages.validation), 'numeric' => __('validation.numeric'), 'integer' => __('validation.integer'), 'url' => __('validation.url'), 'confirmed' => __('messages.validation), 'unique' => __('messages.validation), 'exists' => __('validation.exists')];
    }

    protected function getValidationAttributes(): array
    {
        return ['name' => __('messages.name'), 'email' => __('messages.email'), 'password' => __('Password'), 'password_confirmation' => __('Password Confirmation'), 'phone' => __('messages.phone'), 'address' => __('messages.address'), 'city' => __('City'), 'postal_code' => __('Postal Code'), 'country' => __('Country'), 'description' => __('messages.description'), 'title' => __('Title'), 'content' => __('Content'), 'rating' => __('Rating'), 'quantity' => __('messages.quantity'), 'price' => __('messages.price')];
    }

    public function validateAndSave(array $rules, ?callable $saveCallback = null): bool
    {
        try {
            $this->validate($rules, $this->getValidationMessages(), $this->getValidationAttributes());
            if ($saveCallback) {
                $saveCallback();
            }

            return true;
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->notifyError(__('Please fix the validation errors'));
            throw $e;
        } catch (Exception $e) {
            $this->notifyError(__('messages.shared));
            throw $e;
        }
    }

    public function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function validatePhone(string $phone): bool
    {
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);

        return preg_match('/^(\+370|370|8)[0-9]{8}$/', $cleaned);
    }

    public function validateRequired(mixed $value): bool
    {
        if (is_string($value)) {
            return ! empty(trim($value));
        }
        if (is_array($value)) {
            return ! empty($value);
        }

        return $value !== null;
    }

    public function validateUrl(string $url, array $protocols = ['http', 'https']): bool
    {
        return Str::isUrl($url, $protocols);
    }
}
