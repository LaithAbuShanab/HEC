<?php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;

class QuoteItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $quantity = $this->input('quantity');

        if (is_string($quantity)) {
            $this->merge(['quantity' => trim(strtr($quantity, ['٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9', '٫' => '.', ',' => '.']))]);
        }
    }

    public function rules(): array
    {
        return [
            'variant' => ['nullable', 'integer'],
            'thickness' => ['nullable', 'string', 'max:60'],
            'dimensions' => ['nullable', 'string', 'max:120'],
            'quantity' => ['nullable', 'numeric', 'gt:0', 'max:999999999'],
            'unit' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.*' => __('site.quote.errors.quantity'),
            'max' => __('site.quote.errors.max'),
            'variant.*' => __('site.quote.errors.variant'),
        ];
    }
}
