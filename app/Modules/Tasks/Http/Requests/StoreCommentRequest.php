<?php

namespace App\Modules\Tasks\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:5000'],
            'mentioned_user_ids' => ['array'],
            'mentioned_user_ids.*' => ['integer', 'distinct'],
        ];
    }
}
