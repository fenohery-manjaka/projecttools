<?php

namespace App\Modules\Tasks\Http\Requests;

use App\Modules\Tasks\Enums\TaskStatusCategory;
use App\Modules\Tasks\Models\Task;
use App\Modules\Tasks\Models\TaskStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * A card dropped on the Kanban board. Authorization is on the route.
 */
class MoveTaskRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status_id' => ['required', 'integer', Rule::exists('task_statuses', 'id')],
            'position' => ['required', 'integer', 'min:0'],
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
                $status = TaskStatus::query()->find($this->integer('status_id'));
                /** @var Task $task */
                $task = $this->route('task');

                // Moving inside the blocked column keeps the reason already given.
                $staysBlocked = $status !== null && $task->status_id === $status->id;

                if ($status?->category === TaskStatusCategory::Blocked && ! $staysBlocked && blank($this->input('blocked_reason'))) {
                    $validator->errors()->add('blocked_reason', 'Indiquez la raison du blocage.');
                }
            },
        ];
    }

    public function newStatus(): TaskStatus
    {
        return TaskStatus::query()->findOrFail($this->integer('status_id'));
    }
}
