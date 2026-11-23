<?php

namespace App\Modules\Projects\Http\Requests;

use App\Modules\Projects\Models\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;

class ProjectFilterRequest extends FormRequest
{
    protected $redirectRoute = 'projects.index';

    /**
     * @return array<string, list<string|callable>>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'bail',
                'nullable',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === 'archived') {
                        return;
                    }

                    if (! ctype_digit($value) || ! ProjectStatus::whereKey((int) $value)->exists()) {
                        $fail('Le filtre demandé est invalide.');
                    }
                },
            ],
        ];
    }
}
