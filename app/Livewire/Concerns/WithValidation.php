<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use Illuminate\Support\Str;

trait WithValidation
{
    protected function getValidationMessages(): array
    {
        return [
            'required' => __('shared.required_field'),
            'email' => __('validation.email'),
            'min' => __('validation.min.string'),
            'max' => __('validation.max.string'),
            'numeric' => __('validation.numeric'),
            'integer' => __('validation.integer'),
            'url' => __('validation.url'),
            'confirmed' => __('validation.confirmed'),
            'unique' => __('validation.unique'),
            'exists' => __('validation.exists'),
        ];
    }

    protected function getValidationAttributes(): array
    {
        return [
            'name' => __('Name'),
            'email' => __('Email'),
            'password' => __('Password'),
            'password_confirmation' => __('Password Confirmation'),
            'phone' => __('Phone'),
            'address' => __('Address'),
            'city' => __('City'),
            'postal_code' => __('Postal Code'),
            'country' => __('Country'),
            'description' => __('Description'),
            'title' => __('Title'),
            'content' => __('Content'),
            'rating' => __('Rating'),
            'quantity' => __('Quantity'),
            'price' => __('Price'),
        ];
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
        } catch (\Exception $e) {
            $this->notifyError(__('shared.operation_failed'));
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
