<?php declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ProductRequestFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'message' => ['nullable', 'string', 'max:1000'],
            'requested_quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'product_id.required' => __('translations.product_id_required'),
            'product_id.exists' => __('translations.product_not_found'),
            'name.required' => __('translations.name_required'),
            'name.max' => __('translations.name_max_length'),
            'email.required' => __('translations.email_required'),
            'email.email' => __('translations.email_invalid'),
            'email.max' => __('translations.email_max_length'),
            'phone.max' => __('translations.phone_max_length'),
            'message.max' => __('translations.message_max_length'),
            'requested_quantity.required' => __('translations.quantity_required'),
            'requested_quantity.integer' => __('translations.quantity_must_be_integer'),
            'requested_quantity.min' => __('translations.quantity_min_value'),
            'requested_quantity.max' => __('translations.quantity_max_value'),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'product_id' => __('translations.product'),
            'name' => __('translations.name'),
            'email' => __('translations.email'),
            'phone' => __('translations.phone'),
            'message' => __('translations.message'),
            'requested_quantity' => __('translations.quantity'),
        ];
    }
}

