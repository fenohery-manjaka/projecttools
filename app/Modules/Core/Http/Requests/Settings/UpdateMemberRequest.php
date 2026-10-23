<?php

namespace App\Modules\Core\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role' => ['sometimes', 'nullable', 'string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
