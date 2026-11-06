<?php

namespace App\Http\Requests\Site;

use App\Domain\Quotes\Enums\FulfillmentMethod;
use App\Domain\Shared\Support\PhoneNumber;
use App\Models\QuoteAttachment;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $extensions = 'pdf,jpg,jpeg,png';

        return [
            'idempotency_key' => ['required', 'uuid'],
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['required', 'string', 'max:32', function (string $attribute, mixed $value, Closure $fail) {
                if (! is_string($value) || PhoneNumber::international($value) === null) {
                    $fail(__('site.quote.errors.phone'));
                }
            }],
            'company' => ['nullable', 'string', 'max:160'],
            'email' => ['nullable', 'string', 'email', 'max:160'],
            'city' => ['required', 'string', 'min:2', 'max:80'],
            'fulfillment' => ['required', Rule::enum(FulfillmentMethod::class)],
            'needed_by' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today', 'before:+2 years'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'privacy' => ['accepted'],
            'attachments' => ['nullable', 'array', 'max:'.QuoteAttachment::MAX_FILES],
            'attachments.*' => [
                'file',
                'max:'.QuoteAttachment::MAX_SIZE_KB,
                'extensions:'.$extensions,
                'mimes:'.$extensions,
                'mimetypes:'.implode(',', QuoteAttachment::ALLOWED_MIME_TYPES),
            ],
        ];
    }

    public function messages(): array
    {
        $messages = [];

        foreach (['required', 'min', 'max', 'email', 'date_format', 'after_or_equal', 'before', 'accepted', 'uuid', 'enum', 'file', 'extensions', 'mimes', 'mimetypes', 'array'] as $rule) {
            $messages[$rule] = __("site.quote.errors.{$rule}");
        }

        return [
            ...$messages,
            'privacy.accepted' => __('site.quote.errors.privacy'),
            'attachments.max' => __('site.quote.errors.too_many_files', ['max' => QuoteAttachment::MAX_FILES]),
            'attachments.*.max' => __('site.quote.errors.file_too_large', ['max' => QuoteAttachment::MAX_SIZE_KB / 1024]),
            'attachments.*.extensions' => __('site.quote.errors.file_type'),
            'attachments.*.mimes' => __('site.quote.errors.file_type'),
            'attachments.*.mimetypes' => __('site.quote.errors.file_type'),
            'attachments.*.file' => __('site.quote.errors.file_type'),
            'idempotency_key.*' => __('site.quote.errors.expired'),
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('site.quote.fields.name'),
            'phone' => __('site.quote.fields.phone'),
            'company' => __('site.quote.fields.company'),
            'email' => __('site.quote.fields.email'),
            'city' => __('site.quote.fields.city'),
            'fulfillment' => __('site.quote.fields.fulfillment'),
            'needed_by' => __('site.quote.fields.needed_by'),
            'notes' => __('site.quote.fields.notes'),
            'attachments.*' => __('site.quote.fields.attachment'),
        ];
    }
}
