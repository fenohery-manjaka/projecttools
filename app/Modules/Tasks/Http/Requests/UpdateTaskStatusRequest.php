<?php

namespace App\Modules\Tasks\Http\Requests;

use App\Modules\Tasks\Enums\TaskStatusCategory;
use App\Modules\Tasks\Models\TaskStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Quick status change (buttons of the task page, ticking in My tasks). Authorization is on the route.
 */
class UpdateTaskStatusRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status_id' => ['required', 'integer', Rule::exists('task_statuses', 'id')],
            'blocked_reason' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->status()?->category === TaskStatusCategory::Blocked && blank($this->input('blocked_reason'))) {
                    $validator->errors()->add('blocked_reason', 'Indiquez la raison du blocage.');
                }
            },
        ];
    }

    /**
     * The chosen status, once validated.
     */
    public function newStatus(): TaskStatus
    {
        return TaskStatus::query()->findOrFail($this->integer('status_id'));
    }

    private function status(): ?TaskStatus
    {
        return TaskStatus::query()->find($this->integer('status_id'));
    }
}
