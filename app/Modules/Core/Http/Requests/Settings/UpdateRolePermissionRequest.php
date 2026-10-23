<?php

namespace App\Modules\Core\Http\Requests\Settings;

use App\Modules\Core\ModuleRegistry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRolePermissionRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(ModuleRegistry $modules): array
    {
        return [
            'permission' => ['required', 'string', Rule::in(array_keys($modules->permissions())), Rule::exists('permissions', 'name')],
            'granted' => ['required', 'boolean'],
        ];
    }
}
