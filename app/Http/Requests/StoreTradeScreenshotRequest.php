<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTradeScreenshotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'screenshots' => ['required', 'array', 'min:1'],
            'screenshots.*' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'type' => ['required', Rule::in(['before', 'entry', 'after'])],
        ];
    }
}
